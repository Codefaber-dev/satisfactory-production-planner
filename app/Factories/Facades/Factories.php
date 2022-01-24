<?php

namespace App\Factories\Facades;

use App\Models\Ingredient;
use App\Models\ProductionLine;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;


/**
 * @method static Collection all()
 * @method static ProductionLine create(array $attributes)
 * @method static ProductionLine update($id, array $attributes)
 * @method static ProductionLine|array|mixed find($id)
 * @method static void destroy($id)
*/
class Factories extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Factories';
    }
}
