<?php

namespace App\Factories\Contracts;

use App\Models\Ingredient;
use App\Models\ProductionLine;
use App\Models\Recipe;
use Illuminate\Support\Collection;

interface FactoriesContract
{
    public function all(): Collection;
    public function create(array $attributes): ProductionLine;
    public function update($id, array $attributes): ProductionLine;
    public function find($id): ProductionLine|array|null;
    public function destroy($id): void;
}
