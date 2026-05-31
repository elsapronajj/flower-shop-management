<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BouquetFlowers\BouquetFlowerRequest;
use App\Http\Resources\BouquetFlowerResource;
use App\Models\BouquetFlower;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BouquetFlowerController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(BouquetFlowerResource::collection(BouquetFlower::with(['bouquet', 'flower'])->latest()->paginate(15)));
    }

    public function store(BouquetFlowerRequest $request): JsonResponse
    {
        return $this->success(new BouquetFlowerResource(BouquetFlower::updateOrCreate(
            $request->safe()->only(['bouquet_id', 'flower_id']),
            $request->safe()->only(['quantity'])
        )->load(['bouquet', 'flower'])), 'Bouquet flower saved.', 201);
    }

    public function show(BouquetFlower $bouquetFlower): JsonResponse
    {
        return $this->success(new BouquetFlowerResource($bouquetFlower->load(['bouquet', 'flower'])));
    }

    public function update(BouquetFlowerRequest $request, BouquetFlower $bouquetFlower): JsonResponse
    {
        $bouquetFlower->update($request->validated());

        return $this->success(new BouquetFlowerResource($bouquetFlower->refresh()->load(['bouquet', 'flower'])), 'Bouquet flower updated.');
    }

    public function destroy(BouquetFlower $bouquetFlower): JsonResponse
    {
        $bouquetFlower->delete();

        return $this->success(null, 'Bouquet flower deleted.');
    }
}
