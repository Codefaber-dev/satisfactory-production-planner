<?php

namespace App\Production;

use Illuminate\Support\Collection;

class Multiplexer
{
    protected Collection $calcs;

    /**
     * V137: recycling-injected calcs (one per auto-packaged form). They take part
     * in results/materials/byproducts/overviews — so packaging + container-chain
     * production render as normal plan steps, merged with overlapping plan
     * production — but are NOT requested outputs: excluded from finals, recipes,
     * and overrides.
     */
    protected Collection $injected;

    public function __construct()
    {
        $this->calcs = collect();
        $this->injected = collect();
    }

    public function add(ProductionCalculator $calc): void
    {
        $this->calcs->push($calc);
    }

    /**
     * Replace the injected recycling calcs (V137). Called by RecyclingCalc's
     * fixpoint loop as packaging demand is re-sized.
     *
     * @param  iterable<ProductionCalculator>  $calcs
     */
    public function setInjected(iterable $calcs): void
    {
        $this->injected = collect($calcs);
    }

    /**
     * All participating calcs — requested outputs first, injected recycling
     * calcs after (they consume the leftover byproducts the plan emits).
     */
    protected function allCalcs(): Collection
    {
        return $this->calcs->concat($this->injected);
    }

    public function getResults()
    {
        $results = $this->allCalcs()->map(fn ($calc) => $calc->getRawResults())->collapse();

        return ProductionCalculator::groupAndSortResults($results);
    }

    /**
     * Plain-array Inertia payload for a multi plan — mirrors
     * ProductionCalculator::toPayload (V70: json round-trip, no objects;
     * V66/V76: recycling + extractor keys). Request params consumed here
     * are whitelisted in CACHE_REQUEST_KEYS, so caching the payload is safe.
     *
     * V137: recycling production is injected into the plan itself (packaging +
     * container chain render as normal steps inside `results`); recycling_steps
     * carries only the terminal AWESOME Sink step.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $recycling = RecyclingCalc::inject($this, request()->boolean('auto_package_recycle'));

        return json_decode(json_encode([
            'results' => $this->getResults(),
            'byproducts_used' => $this->getByproductsUsed(),
            'raw_materials' => $this->getRawMaterials(),
            'intermediate_materials' => $this->getIntermediateMaterials(),
            'all_materials' => $this->getAllMaterials(),
            'final' => $this->getFinals(),
            'recipe' => $this->getRecipes(),
            'overrides' => $this->getOverrides(),
            'byproducts' => $this->getByproducts(),
            'overviews' => $this->getOverviews(),
            // V76: extract-mode raws → extractor building rows (summary/parts/power);
            // V137: injected-chain raws are inside getRawMaterials already
            'extractors' => ExtractorSummary::build(collect($this->getRawMaterials()), request('raw_sources', [])),
            'recycling' => $recycling,
            // V137: sink-only — packaging + chain steps live in `results`
            'recycling_steps' => RecyclingSteps::build($recycling, (float) request('belt_speed', 780)),
        ]), true);
    }

    public function recalculateUsingByproducts()
    {
        $used_byproducts = [];

        $this->allCalcs()->each(function ($calc) use (&$used_byproducts) {
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
        return $this->allCalcs()->map(fn ($calc) => $calc->getByproducts())->sumByKey();
    }

    public function getByproductsUsed()
    {
        return $this->allCalcs()->map(fn ($calc) => $calc->getByproductsUsed())->sumByKey();
    }

    public function getOverviews()
    {
        // imported products render no step, so their overviews are dead weight —
        // reject them here to match ProductionCalculator::getOverviews (V137
        // unified the single + multi payload paths through this class).
        return $this->getResults()->map(function ($tier) {
            return $tier->reject(fn ($product) => $product->imported)
                ->map(function ($product) {
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
