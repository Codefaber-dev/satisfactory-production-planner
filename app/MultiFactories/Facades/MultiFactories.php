<?php

namespace App\MultiFactories\Facades;

use App\Models\MultiProductionLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection all()
 * @method static MultiProductionLine create(array $attributes)
 * @method static MultiProductionLine update($id, array $attributes)
 * @method static MultiProductionLine|array|mixed find($id)
 * @method static void destroy($id)
 */
class MultiFactories extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'MultiFactories';
    }
}
