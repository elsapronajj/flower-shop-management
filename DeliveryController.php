<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Deliveries\DeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DeliveryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(DeliveryResource::collection(Delivery::with(['order.customer', 'courier.roles'])->latest()->paginate(15)));
    }

    public function store(DeliveryRequest $request): JsonResponse
    {
        return $this->success(new DeliveryResource(Delivery::create($request->validated())->load(['order.customer', 'courier.roles'])), 'Delivery created.', 201);
    }

    public function show(Delivery $delivery): JsonResponse
    {
        return $this->success(new DeliveryResource($delivery->load(['order.customer', 'courier.roles'])));
    }

    public function update(DeliveryRequest $request, Delivery $delivery): JsonResponse
    {
        $delivery->update($request->validated());

        return $this->success(new DeliveryResource($delivery->refresh()->load(['order.customer', 'courier.roles'])), 'Delivery updated.');
    }

    public function destroy(Delivery $delivery): JsonResponse
    {
        $delivery->delete();

        return $this->success(null, 'Delivery deleted.');
    }
}
