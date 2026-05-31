<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $tokens = $this->authService->issueTokens($user, $request);

        return $this->success([
            'user' => new UserResource($user),
            'access_token' => $tokens['access_token'],
            'token_type' => $tokens['token_type'],
            'expires_in' => $tokens['expires_in'],
        ], 'Registered successfully.', 201)->withCookie($this->authService->refreshCookie($tokens['refresh_token']));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->attempt($request->validated());

        if (! $user) {
            return $this->error('Invalid email or password.', 401);
        }

        $tokens = $this->authService->issueTokens($user, $request);

        return $this->success([
            'user' => new UserResource($user),
            'access_token' => $tokens['access_token'],
            'token_type' => $tokens['token_type'],
            'expires_in' => $tokens['expires_in'],
        ], 'Logged in successfully.')->withCookie($this->authService->refreshCookie($tokens['refresh_token']));
    }

    public function refresh(Request $request): JsonResponse
    {
        $payload = $this->authService->refresh($request->cookie(config('tokens.refresh_cookie')));

        return $this->success([
            'user' => new UserResource($payload['user']),
            'access_token' => $payload['access_token'],
            'token_type' => $payload['token_type'],
            'expires_in' => $payload['expires_in'],
        ], 'Access token refreshed.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->revoke($request->cookie(config('tokens.refresh_cookie')));

        return $this->success(null, 'Logged out successfully.')->withCookie($this->authService->forgetRefreshCookie());
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }
}
