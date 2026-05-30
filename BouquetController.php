<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bouquets\BouquetRequest;
use App\Http\Resources\BouquetResource;
use App\Models\Bouquet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BouquetController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(BouquetResource::collection(Bouquet::with('bouquetFlowers.flower')->latest()->paginate(15)));
    }

    public function store(BouquetRequest $request): JsonResponse
    {
        return $this->success(new BouquetResource(Bouquet::create($request->validated())->load('bouquetFlowers.flower')), 'Bouquet created.', 201);
    }

    public function show(Bouquet $bouquet): JsonResponse
    {
        return $this->success(new BouquetResource($bouquet->load('bouquetFlowers.flower')));
    }

    public function update(BouquetRequest $request, Bouquet $bouquet): JsonResponse
    {
        $bouquet->update($request->validated());

        return $this->success(new BouquetResource($bouquet->refresh()->load('bouquetFlowers.flower')), 'Bouquet updated.');
    }

    public function destroy(Bouquet $bouquet): JsonResponse
    {
        $bouquet->delete();

        return $this->success(null, 'Bouquet deleted.');
    }
}
