<?php

namespace App\MultiFactories\Contracts;

use App\Models\Ingredient;
use App\Models\MultiProductionLine;
use App\Models\Recipe;
use Illuminate\Support\Collection;

interface MultiFactoriesContract
{
    public function all(): Collection;
    public function create(array $attributes): MultiProductionLine;
    public function update($id, array $attributes): MultiProductionLine;
    public function find($id): MultiProductionLine|array|null;
    public function destroy($id): void;
}
