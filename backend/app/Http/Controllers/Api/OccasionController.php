<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Occasions\OccasionRequest;
use App\Http\Resources\OccasionResource;
use App\Models\Occasion;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OccasionController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(OccasionResource::collection(Occasion::latest()->paginate(15)));
    }

    public function store(OccasionRequest $request): JsonResponse
    {
        return $this->success(new OccasionResource(Occasion::create($request->validated())), 'Occasion created.', 201);
    }

    public function show(Occasion $occasion): JsonResponse
    {
        return $this->success(new OccasionResource($occasion));
    }

    public function update(OccasionRequest $request, Occasion $occasion): JsonResponse
    {
        $occasion->update($request->validated());

        return $this->success(new OccasionResource($occasion->refresh()), 'Occasion updated.');
    }

    public function destroy(Occasion $occasion): JsonResponse
    {
        $occasion->delete();

        return $this->success(null, 'Occasion deleted.');
    }
}
