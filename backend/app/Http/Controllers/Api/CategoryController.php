<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(CategoryResource::collection(Category::withCount('flowers')->latest()->paginate(15)));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        return $this->success(new CategoryResource(Category::create($request->validated())), 'Category created.', 201);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success(new CategoryResource($category->loadCount('flowers')));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return $this->success(new CategoryResource($category->refresh()->loadCount('flowers')), 'Category updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->success(null, 'Category deleted.');
    }
}
