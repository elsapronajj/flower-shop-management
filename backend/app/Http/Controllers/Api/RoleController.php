<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(RoleResource::collection(Role::latest()->paginate(15)));
    }

    public function store(RoleRequest $request): JsonResponse
    {
        return $this->success(new RoleResource(Role::create($request->validated())), 'Role created.', 201);
    }

    public function show(Role $role): JsonResponse
    {
        return $this->success(new RoleResource($role));
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return $this->success(new RoleResource($role->refresh()), 'Role updated.');
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return $this->success(null, 'Role deleted.');
    }
}
