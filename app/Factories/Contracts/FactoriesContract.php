<?php

namespace App\Factories\Contracts;

use App\Models\ProductionLine;
use Illuminate\Support\Collection;

interface FactoriesContract
{
    public function all(): Collection;

    public function create(array $attributes): ProductionLine;

    public function update($id, array $attributes): ProductionLine;

    public function find($id): ProductionLine|array|null;

    public function destroy($id): void;
}
