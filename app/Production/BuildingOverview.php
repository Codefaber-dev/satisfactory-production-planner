<?php

namespace App\Production;

use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildingOverview
{
    /**
     * @var \App\Models\Recipe
     */
    protected $recipe;

    protected $qty;

    protected $belt_speed;

    protected $clock_speed;

    public BuildingDetails $details;

    public Collection $overview;

    public $selected_variant;

    public function __construct(Recipe $recipe, $qty, $belt_speed, $variant = "mk1", $clock_speed = 100)
    {
        $this->recipe = $recipe;
        $this->qty = $qty;
        $this->belt_speed = $belt_speed;
        $this->clock_speed = $clock_speed;

        $this->details = BuildingDetails::calc($recipe, $qty, $belt_speed, $clock_speed);

        $this->overview = $this->details->map(function ($details, $building) {
            return [$building => "[x{$details['num_buildings']} {$details['clock_speed']}%] [{$details['power_usage']} MW]"];
        })->collapse();

        $this->selected_variant = $this->details->keys()->filter(fn($key) => Str::of($key)->contains($variant))->first();
    }

    public static function make(Recipe $recipe, $qty, $belt_speed, $variant = "mk1", $clock_speed = 100): static
    {
        return new static($recipe, $qty, $belt_speed, $variant, $clock_speed);
    }

    public function toArray(): array
    {
        return [
            "qty" => $this->qty,
            "details" => $this->details->all(),
            "overview" => $this->overview->all(),
            "selected_variant" => $this->details[$this->selected_variant],
            "selected_variant_name" => $this->selected_variant,
            "product" => $this->recipe->product->name,
            "recipe" => $this->recipe->description ?? $this->recipe->product->name,
        ];
    }


}
