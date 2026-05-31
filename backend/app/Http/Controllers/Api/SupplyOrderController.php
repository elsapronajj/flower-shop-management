<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplyOrders\SupplyOrderRequest;
use App\Http\Resources\SupplyOrderResource;
use App\Models\SupplyOrder;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SupplyOrderController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(SupplyOrderResource::collection(SupplyOrder::with('supplier')->latest()->paginate(15)));
    }

    public function store(SupplyOrderRequest $request): JsonResponse
    {
        return $this->success(new SupplyOrderResource(SupplyOrder::create($request->validated())->load('supplier')), 'Supply order created.', 201);
    }

    public function show(SupplyOrder $supplyOrder): JsonResponse
    {
        return $this->success(new SupplyOrderResource($supplyOrder->load('supplier')));
    }

    public function update(SupplyOrderRequest $request, SupplyOrder $supplyOrder): JsonResponse
    {
        $supplyOrder->update($request->validated());

        return $this->success(new SupplyOrderResource($supplyOrder->refresh()->load('supplier')), 'Supply order updated.');
    }

    public function destroy(SupplyOrder $supplyOrder): JsonResponse
    {
        $supplyOrder->delete();

        return $this->success(null, 'Supply order deleted.');
    }
}
