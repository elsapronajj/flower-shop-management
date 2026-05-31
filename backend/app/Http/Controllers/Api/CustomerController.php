<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(CustomerResource::collection(Customer::withCount('orders')->latest()->paginate(15)));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        return $this->success(new CustomerResource(Customer::create($request->validated())), 'Customer created.', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return $this->success(new CustomerResource($customer->loadCount('orders')));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return $this->success(new CustomerResource($customer->refresh()->loadCount('orders')), 'Customer updated.');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return $this->success(null, 'Customer deleted.');
    }
}
