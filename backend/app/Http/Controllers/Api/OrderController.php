<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderService $orderService,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->success(OrderResource::collection($this->orders->paginateWithRelations()));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        return $this->success(new OrderResource($this->orderService->create($request->validated())), 'Order created.', 201);
    }

    public function show(Order $order): JsonResponse
    {
        return $this->success(new OrderResource($this->orders->findWithRelations($order)));
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        return $this->success(new OrderResource($this->orderService->update($order, $request->validated())), 'Order updated.');
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->orderService->delete($order);

        return $this->success(null, 'Order deleted.');
    }
}
