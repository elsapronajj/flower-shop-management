<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(ReviewResource::collection(Review::with(['customer', 'order.customer'])->latest()->paginate(15)));
    }

    public function store(ReviewRequest $request): JsonResponse
    {
        return $this->success(new ReviewResource(Review::create($request->validated())->load(['customer', 'order.customer'])), 'Review created.', 201);
    }

    public function show(Review $review): JsonResponse
    {
        return $this->success(new ReviewResource($review->load(['customer', 'order.customer'])));
    }

    public function update(ReviewRequest $request, Review $review): JsonResponse
    {
        $review->update($request->validated());

        return $this->success(new ReviewResource($review->refresh()->load(['customer', 'order.customer'])), 'Review updated.');
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return $this->success(null, 'Review deleted.');
    }
}
