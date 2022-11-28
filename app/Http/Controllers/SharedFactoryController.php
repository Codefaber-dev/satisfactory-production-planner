<?php

namespace App\Http\Controllers;

use App\Models\MultiProductionLine;
use App\Models\ProductionLine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function redirect;

class SharedFactoryController extends Controller
{
    public function show($hashId)
    {
        $factory = (Str::of($hashId)->startsWith("pl_")) ?
            ProductionLine::ofHashId($hashId) :
            MultiProductionLine::ofHashId($hashId);

        return redirect()->to($factory->getPlanUrl());
    }
}
