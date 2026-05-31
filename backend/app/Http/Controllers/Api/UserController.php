<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(UserResource::collection(User::with('roles')->latest()->paginate(15)));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->roles()->sync($roleIds);

        return $this->success(new UserResource($user->load('roles')), 'User created.', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(new UserResource($user->load('roles')));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        $user->roles()->sync($roleIds);

        return $this->success(new UserResource($user->refresh()->load('roles')), 'User updated.');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->success(null, 'User deleted.');
    }
}
