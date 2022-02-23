<?php

namespace App\PowerPlanner\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Base implements GeneratorContract
{
    protected $name;

    protected $fuel;

    protected $inputs;

    protected $gross_output;

    protected $build_cost;

    protected $waste = [];

    protected $buildable_fuels = [
        "Biomass",
        "Solid Biofuel",
        "Packaged Liquid Biofuel",
        "Compacted Coal",
        "Petroleum Coke",
        "Fuel",
        "Liquid Biofuel",
        "Turbofuel",
        "Uranium Fuel Rod",
        "Plutonium Fuel Rod"
    ];

    protected int $output;

    public static function make(int $output): static
    {
        return new static($output);
    }

    /**
     * The desired output in MW
     *
     * @param int $output
     */
    public function __construct(int $output)
    {
        $this->output = $output;
    }

    public function getNumRequired(): int
    {
        return ceil($this->output / $this->gross_output);
    }

    public function getFuelRequirements(): Collection
    {
        $numRequired = $this->getNumRequired();

        return collect($this->fuel)->map(fn($qty) => $qty * $numRequired);
    }

    public function getInputRequirements(): Collection
    {
        $numRequired = $this->getNumRequired();

        return collect($this->inputs)->map(fn($qty) => $qty * $numRequired);
    }

    public function getWaste(): Collection
    {
        $numRequired = $this->getNumRequired();

        return collect($this->waste)->map(fn($waste) => collect($waste)->map(fn($qty) => $qty * $numRequired));
    }

    public function getBuildCost(): Collection
    {
        $numRequired = $this->getNumRequired();

        return collect($this->build_cost)->map(fn($qty) => $qty * $numRequired);
    }

    public function calculate(): array
    {
        $numRequired = $this->getNumRequired();

        return [
            "num" => $numRequired,
            "name" => $this->name,
            "output" => $this->gross_output * $numRequired,
            "fuel" => $this->getFuelRequirements()->all(),
            "other" => $this->getInputRequirements()->all(),
            "waste" => $this->getWaste()->all(),
            "build_cost" => $this->getBuildCost()->all(),
            "image" => (string) Str::of($this->name)->studly()->append(".png"),
            "buildable_fuels" => $this->buildable_fuels
        ];
    }
}
