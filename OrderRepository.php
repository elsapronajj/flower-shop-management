<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function paginateWithRelations(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['customer', 'items.flower.category', 'items.flower.supplier', 'items.bouquet.bouquetFlowers.flower', 'delivery.courier'])
            ->latest('order_date')
            ->paginate($perPage);
    }

    public function findWithRelations(Order $order): Order
    {
        return $order->load(['customer', 'items.flower.category', 'items.flower.supplier', 'items.bouquet.bouquetFlowers.flower', 'delivery.courier']);
    }
}
