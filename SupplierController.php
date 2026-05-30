<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Http\Requests\Suppliers\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(SupplierResource::collection(Supplier::withCount('flowers')->latest()->paginate(15)));
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        return $this->success(new SupplierResource(Supplier::create($request->validated())), 'Supplier created.', 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return $this->success(new SupplierResource($supplier->loadCount('flowers')));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return $this->success(new SupplierResource($supplier->refresh()->loadCount('flowers')), 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return $this->success(null, 'Supplier deleted.');
    }
}
