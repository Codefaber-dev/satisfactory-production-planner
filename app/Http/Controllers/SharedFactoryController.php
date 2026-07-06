<?php

namespace App\Http\Controllers;

use App\Models\MultiProductionLine;
use App\Models\ProductionLine;
use Illuminate\Support\Str;

use function redirect;

class SharedFactoryController extends Controller
{
    public function show($hashId)
    {
        $factory = (Str::of($hashId)->startsWith('pl_')) ?
            ProductionLine::ofHashId($hashId) :
            MultiProductionLine::ofHashId($hashId);

        // B61: undecodable/unknown hash → 404, never a Builder fatal
        abort_unless((bool) $factory, 404);

        return redirect()->to($factory->getPlanUrl());
    }
}
