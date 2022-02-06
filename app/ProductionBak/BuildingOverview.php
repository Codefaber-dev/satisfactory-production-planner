<?php

namespace App\ProductionBak;

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

    public BuildingDetails $details;

    public Collection $overview;

    public $selected_variant;

    public function __construct(Recipe $recipe, $qty, $belt_speed, $variant = "mk1")
    {
        $this->recipe = $recipe;
        $this->qty = $qty;
        $this->belt_speed = $belt_speed;

        $this->details = BuildingDetails::calc($recipe, $qty, $belt_speed);

        $this->overview = $this->details->map(function ($details, $building) {
            return [$building => "[x{$details['num_buildings']} {$details['clock_speed']}%] [{$details['power_usage']} MW]"];
        })->collapse();

        $this->selected_variant = $this->details->keys()->filter(fn($key) => Str::of($key)->contains($this->selected_variant))->first();
    }

    public static function make(Recipe $recipe, $qty, $belt_speed): static
    {
        return new static($recipe, $qty, $belt_speed);
    }
}
