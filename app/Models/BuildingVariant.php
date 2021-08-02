<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function pow;

class BuildingVariant extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function setRecipe($recipe)
    {
        collect($recipe)
            ->each(function($row) {
                $ingredient = Ingredient::ofName($row['ingredient']);

                $this->recipe()->attach([$ingredient->id => ['qty' => $row['qty']]]);
            });
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function recipe()
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot([
                'qty'
            ]);
    }

    public function scopeOfName(Builder $query, $name)
    {
        return $query->firstWhere('name',$name);
    }

    public function calculatePowerUsage($clock_speed)
    {
        if ($clock_speed < 0.01) {
            Log::debug("Clock speed of $clock_speed is too low. Setting to 0.01");
            //throw new InvalidArgumentException("Clock speed must be at least 0.01");
            $clock_speed = 0.01;
        }

        if ($clock_speed > 2.5)
            throw new InvalidArgumentException("Clock speed must be at most 2.5");

        return pow($clock_speed,1.6) * $this->base_power;
    }
}
