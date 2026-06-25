<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\{
    LoginRequest,
    NewPasswordRequest,
    PasswordResetRequest,
    RegisterRequest,
};
use App\Http\Resources\TokenResource;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\{JsonResponse, Response};
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login member by email and password
     *
     * @param LoginRequest $request
     * @param AuthService $service
     * @return TokenResource
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request, AuthService $service): TokenResource
    {
        $credentials = $request->only(['email', 'password']);

        return new TokenResource(
            $service->login($credentials, $request->remember_me ?? false)
        );
    }

    /**
     * Register new member
     *
     * @param RegisterRequest $request
     * @param AuthService $service
     * @return TokenResource
     */
    public function register(RegisterRequest $request, AuthService $service): TokenResource
    {
        $token = $service->register($request->validated());

        return new TokenResource($token);
    }

    /**
     * Logout member
     *
     * @return Response
     */
    public function logout(): Response
    {
        auth()->logout();

        return response()->noContent();
    }

    /**
     * @param PasswordResetRequest $request
     * @param PasswordResetService $service
     * @return JsonResponse
     */
    public function passwordResetRequest(PasswordResetRequest $request, PasswordResetService $service): JsonResponse
    {
        $service->sendResetCode($request->email);

        return response()->json([
            'message' => 'If an account with that email exists, a verification code has been sent.'
        ]);
    }

    /**
     * @param NewPasswordRequest $request
     * @param PasswordResetService $service
     * @return JsonResponse
     * @throws ValidationException
     */
    public function passwordResetConfirm(NewPasswordRequest $request, PasswordResetService $service): JsonResponse
    {
        $service->resetPassword($request->email, $request->code, $request->password);

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    }
}
