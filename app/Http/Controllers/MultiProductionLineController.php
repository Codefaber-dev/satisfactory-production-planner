<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanParams;
use App\MultiFactories\Facades\MultiFactories;

class MultiProductionLineController extends Controller
{
    public function store()
    {
        request()->validate(PlanParams::rules());

        $attributes = [
            'name' => request('name'),
            'outputs' => request('outputs'),
            'notes' => request()->has('notes') ? request('notes', '') : null,
            'imports' => request()->has('imports') ? request('imports', '') : null,
            'choices' => request()->has('choices') ? request('choices', []) : [],
            'raw_sources' => PlanParams::sanitizeRawSources(request('raw_sources', []) ?: []),
            'import_notes' => request()->has('import_notes') ? request('import_notes', []) : [],
            'auto_package_recycle' => request()->boolean('auto_package_recycle'),
        ];

        MultiFactories::create($attributes);

        return redirect()->to('/factories');
    }

    public function update($id)
    {
        request()->validate(PlanParams::rules());

        $attributes = [
            'name' => request('name'),
            'outputs' => request('outputs'),
            'notes' => request()->has('notes') ? request('notes', '') : null,
            'imports' => request()->has('imports') ? request('imports', '') : null,
            'choices' => request()->has('choices') ? request('choices', []) : [],
            'raw_sources' => PlanParams::sanitizeRawSources(request('raw_sources', []) ?: []),
            'import_notes' => request()->has('import_notes') ? request('import_notes', []) : [],
            'auto_package_recycle' => request()->boolean('auto_package_recycle'),
        ];

        MultiFactories::update($id, $attributes);

        return redirect()->to('/factories');
    }

    public function destroy($id)
    {
        MultiFactories::destroy($id);

        return redirect()->to('/factories');
    }
}
