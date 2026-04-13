<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\{
    LoginRequest,
    NewPasswordRequest,
    PasswordResetRequest,
    RegisterRequest,
};
use App\Http\Resources\TokenResource;
use App\Mail\PasswordReset;
use App\Models\PasswordResetToken;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\{JsonResponse, Response};
use Illuminate\Support\Facades\Mail;
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
     * @return void
     */
    public function passwordResetRequest(PasswordResetRequest $request): void
    {
        $token = (new PasswordResetToken())->create(['email' => $request->email]);

        Mail::to($request->email)->send(new PasswordReset($token->token));
    }

    /**
     * @param NewPasswordRequest $request
     * @param AuthService $service
     * @return JsonResponse
     * @throws ValidationException
     */
    public function passwordResetConfirm(NewPasswordRequest $request, AuthService $service): JsonResponse
    {
        $service->resetPassword($request->token, $request->new_password);

        return response()->json([
            'message' => 'Password reset successfully',
        ]);
    }
}
