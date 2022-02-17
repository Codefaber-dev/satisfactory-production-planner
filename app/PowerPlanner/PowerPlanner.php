<?php

namespace App\PowerPlanner;

use App\PowerPlanner\Generators\BiomassBurner;
use App\PowerPlanner\Generators\CoalGenerator;
use App\PowerPlanner\Generators\FuelGenerator;
use App\PowerPlanner\Generators\NuclearPowerPlant;
use Illuminate\Support\Collection;

class PowerPlanner
{
    protected $output;

    protected $options = [
        BiomassBurner::class,
        CoalGenerator::class,
        FuelGenerator::class,
        NuclearPowerPlant::class,
    ];

    /**
     * How much MW to produce
     * @param int $output
     */
    public function __construct(int $output)
    {
        $this->output = $output;
    }

    public static function make(int $output): static
    {
        return new static($output);
    }

    public function calculate(): Collection
    {
        return collect($this->options)
            ->map(fn($option) => $option::make($this->output)->calculate());
    }
}
