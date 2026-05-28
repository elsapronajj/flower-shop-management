<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flowers\StoreFlowerRequest;
use App\Http\Requests\Flowers\UpdateFlowerRequest;
use App\Http\Resources\FlowerResource;
use App\Models\Flower;
use App\Repositories\FlowerRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FlowerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly FlowerRepository $flowers)
    {
    }

    public function index(): JsonResponse
    {
        return $this->success(FlowerResource::collection($this->flowers->paginateWithRelations()));
    }

    public function store(StoreFlowerRequest $request): JsonResponse
    {
        $flower = Flower::create($request->validated());

        return $this->success(new FlowerResource($flower->load(['category', 'supplier'])), 'Flower created.', 201);
    }

    public function show(Flower $flower): JsonResponse
    {
        return $this->success(new FlowerResource($this->flowers->findWithRelations($flower)));
    }

    public function update(UpdateFlowerRequest $request, Flower $flower): JsonResponse
    {
        $flower->update($request->validated());

        return $this->success(new FlowerResource($flower->refresh()->load(['category', 'supplier'])), 'Flower updated.');
    }

    public function destroy(Flower $flower): JsonResponse
    {
        $flower->delete();

        return $this->success(null, 'Flower deleted.');
    }
}
