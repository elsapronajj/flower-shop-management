<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FlowerResource;
use App\Http\Resources\OrderResource;
use App\Models\Bouquet;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Flower;
use App\Models\Order;
use App\Models\Review;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __invoke(): JsonResponse
    {
        return $this->success([
            'totals' => [
                'flowers' => Flower::count(),
                'bouquets' => Bouquet::count(),
                'categories' => Category::count(),
                'suppliers' => Supplier::count(),
                'customers' => Customer::count(),
                'orders' => Order::count(),
                'pending_deliveries' => Delivery::whereIn('status', ['pending', 'in_transit'])->count(),
                'reviews' => Review::count(),
            ],
            'low_stock_flowers' => FlowerResource::collection(
                Flower::with(['category', 'supplier'])
                    ->where('stock_quantity', '<=', 10)
                    ->orderBy('stock_quantity')
                    ->limit(8)
                    ->get()
            ),
            'recent_orders' => OrderResource::collection(
                Order::with(['customer', 'items.flower'])
                    ->latest('order_date')
                    ->limit(8)
                    ->get()
            ),
        ]);
    }
}
