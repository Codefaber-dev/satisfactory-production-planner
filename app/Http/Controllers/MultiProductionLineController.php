<?php

namespace App\Http\Controllers;

use App\MultiFactories\Facades\MultiFactories;
use Illuminate\Http\Request;

class MultiProductionLineController extends Controller
{
    public function store()
    {
        $attributes = [
            'name' => request('name'),
            'outputs' => request('outputs'),
            'notes' => request()->has('notes') ? request('notes',"") : null,
            'imports' => request()->has('imports') ? request('imports',"") : null,
            'choices' => request()->has('choices') ? request('choices',[]) : []
        ];

        MultiFactories::create($attributes);

        return redirect()->to('/factories');
    }

    public function update($id)
    {
        $attributes = [
            'name' => request('name'),
            'outputs' => request('outputs'),
            'notes' => request()->has('notes') ? request('notes',"") : null,
            'imports' => request()->has('imports') ? request('imports',"") : null,
            'choices' => request()->has('choices') ? request('choices',[]) : []
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
