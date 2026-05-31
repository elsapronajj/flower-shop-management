<?php

namespace App\Repositories;

use App\Models\Flower;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FlowerRepository
{
    public function paginateWithRelations(int $perPage = 15): LengthAwarePaginator
    {
        return Flower::query()
            ->with(['category', 'supplier'])
            ->latest()
            ->paginate($perPage);
    }

    public function findWithRelations(Flower $flower): Flower
    {
        return $flower->load(['category', 'supplier']);
    }
}
