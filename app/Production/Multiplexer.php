<?php

namespace App\Production;

use Illuminate\Support\Collection;

class Multiplexer
{
    protected Collection $calcs;

    public function __construct()
    {
        $this->calcs = collect();
    }

    public function add(ProductionCalculator $calc): void
    {
        $this->calcs->push($calc);
    }

    public function getResults()
    {
        $results = $this->calcs->map(fn ($calc) => $calc->getRawResults())->collapse();

        return ProductionCalculator::groupAndSortResults($results);
    }

    /**
     * Plain-array Inertia payload for a multi plan — mirrors
     * ProductionCalculator::toPayload (V70: json round-trip, no objects;
     * V66/V76/V88: recycling + extractor keys). Request params consumed here
     * are whitelisted in CACHE_REQUEST_KEYS, so caching the payload is safe.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $recycling = RecyclingCalc::calc($this->getByproducts(), $this->getByproductsUsed(), request()->boolean('auto_package_recycle'));
        $recyclingBelt = (float) request('belt_speed', 780);

        // V136: the container chain's materials fold into the plan totals
        $rawMaterials = collect([$this->getRawMaterials(), $recycling['raw_materials'] ?? []])->sumByKey();

        return json_decode(json_encode([
            'results' => $this->getResults(),
            'byproducts_used' => $this->getByproductsUsed(),
            'raw_materials' => $rawMaterials,
            'intermediate_materials' => collect([$this->getIntermediateMaterials(), $recycling['intermediate_materials'] ?? []])->sumByKey(),
            'all_materials' => collect([$this->getAllMaterials(), $recycling['all_materials'] ?? []])->sumByKey(),
            'final' => $this->getFinals(),
            'recipe' => $this->getRecipes(),
            'overrides' => $this->getOverrides(),
            'byproducts' => $this->getByproducts(),
            // V93: merge the package steps' 4-clock overviews so they render with controls
            'overviews' => $this->getOverviews()->merge(RecyclingSteps::overviews($recycling, $recyclingBelt)),
            // V76: extract-mode raws → extractor building rows (summary/parts/power);
            // V136: chain raws included so extractor counts cover them
            'extractors' => ExtractorSummary::build(collect($rawMaterials), request('raw_sources', [])),
            'recycling' => $recycling,
            // V88: recycling rendered as full build steps (packaging + sink, with diagrams)
            'recycling_steps' => RecyclingSteps::build($recycling, $recyclingBelt),
        ]), true);
    }

    public function recalculateUsingByproducts()
    {
        $used_byproducts = [];

        $this->calcs->each(function ($calc) use (&$used_byproducts) {
            $calc->recalculateUsingByproducts($this->getByproducts(), $used_byproducts);
            $used_byproducts = $calc->getUsedByproducts();
        });
    }

    /**
     * Recalculate byproduct usage until the shared byproduct state stops
     * changing (V74) — replaces the fixed 3× pass. An identical state means
     * the next pass is a no-op, so early exit is byte-identical to the old
     * fixed-pass behavior. Returns the number of passes run.
     */
    public function recalculateUsingByproductsUntilStable(int $maxPasses = 3): int
    {
        $previous = null;
        $passes = 0;

        while ($passes < $maxPasses) {
            $this->recalculateUsingByproducts();
            $passes++;

            $state = $this->byproductState();

            if ($state === $previous) {
                break;
            }

            $previous = $state;
        }

        return $passes;
    }

    /**
     * Serialized byproduct production/consumption state across all calcs,
     * used for convergence detection (V74).
     */
    public function byproductState(): string
    {
        return json_encode([
            'byproducts' => $this->getByproducts(),
            'used' => $this->getByproductsUsed(),
        ]);
    }

    public function getRawMaterials()
    {
        $results = $this->getResults();

        // confirm we have raw materials
        if (! isset($results[1])) {
            return collect();
        }

        return $results[1]->map(function ($val, $name) {
            return round($val->total, 4);
        })->filter(fn ($val, $name) => $val > 0);
    }

    public function getIntermediateMaterials()
    {
        $results = $this->getResults();

        // skip raw if needed
        $intermediate = isset($results[1]) ?
            $results->skip(1) :
            $results;

        return $intermediate->map(function ($tier) {
            return $tier->map(function ($val, $name) {
                if (! isset($val->outputs['final'])) {
                    return round($val->total, 4);
                }

                return null;
            })->filter();
        })->collapse();
    }

    public function getAllMaterials()
    {
        return $this->getResults()->map(function ($tier) {
            return $tier->map(function ($val, $name) {
                // if(! isset($val->outputs['final'])) {
                return round($val->total, 4);
                // }
                // return null;
            })->filter();
        })->collapse();
    }

    public function getFinals()
    {
        return $this->calcs->map(fn ($calc) => $calc->getSteps()->toArray());
    }

    public function getRecipes()
    {
        return $this->calcs->map(fn ($calc) => $calc->getSteps()->getRecipe());
    }

    public function getOverrides()
    {
        return $this->calcs->map(fn ($calc) => $calc->getSteps()->getOverrides()->filter())->collapse();
    }

    public function getByproducts()
    {
        return $this->calcs->map(fn ($calc) => $calc->getByproducts())->sumByKey();
    }

    public function getByproductsUsed()
    {
        return $this->calcs->map(fn ($calc) => $calc->getByproductsUsed())->sumByKey();
    }

    public function getOverviews()
    {
        return $this->getResults()->map(function ($tier) {
            return $tier->map(function ($product) {
                return $product->production->filter(fn ($row) => isset($row['overview']))->pluck('overviews');
            })->collapse()->filter();
        })->collapse()
            ->map(fn ($overview) => [$overview['c100']['product'].'|'.$overview['c100']['recipe'] => [
                'clock' => 'c100',
                'selected_variant_name' => $overview['c100']['selected_variant_name'],
                'overviews' => $overview,
                'overview' => $overview['c100'],
            ]])
            ->collapse();
    }

    public function ratioOfAvailableRawMaterials(): float
    {
        $raw_available = ($raw = request('raw')) ? static::parseRaw($raw) : [];

        return $this->getRawMaterials()
            ->map(function ($required, $key) use ($raw_available) {
                if (isset($raw_available[$key]) && $available = $raw_available[$key]) {
                    return $available / $required;
                }

                return null;
            })
            ->filter()
            ->min() ?? 1;
    }

    protected static function parseRaw($raw): array
    {
        return collect(explode(',', $raw))
            ->map(function ($pair) {
                [$key,$value] = explode(':', $pair);

                return [$key => (int) $value];
            })
            ->collapse()
            ->all();
    }
}
