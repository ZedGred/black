<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use App\Helpers\JwtHelper;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    // =============================
    // Register
    // =============================
    public function registerUser(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()
            ->json($result['response'], 201)
            ->cookie($result['cookie']);
    }

    // =============================
    // Login
    // =============================
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->only('email', 'password'));
        } catch (\Exception $e) {
            $statusCode = is_numeric($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }

        return response()
            ->json($result['response'])
            ->cookie($result['cookie']);
    }

    // =============================
    // Logout
    // =============================
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    // =============================
    // Me (get authenticated user)
    // =============================
    public function me()
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'User data retrieved successfully',
            'data'    => [
                'user'        => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles'       => $user->getRoleNames(),
            ],
        ]);
    }

    // =============================
    // Refresh Token (via DB table)
    // =============================
    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $response = $this->authService->rotateRefreshToken($request->refresh_token);
        } catch (\Exception $e) {
            $statusCode = is_numeric($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }

        return response()->json($response);
    }

    // =============================
    // Refresh Token (JWT native)
    // =============================
    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();

            return response()->json([
                'status'  => 'success',
                'message' => 'Token refreshed successfully',
                'data'    => [
                    'user'  => Auth::guard('api')->user(),
                    'token' => [
                        'access_token' => $newToken,
                        'token_type'   => 'Bearer',
                        'expires_in'   => auth('api')->factory()->getTTL() * 60,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token is invalid or has expired',
            ], 401);
        }
    }

    // =============================
    // Google OAuth
    // =============================
    public function redirectToGoogle()
    {
        $redirectUrl = url('/api/v1/auth/google/callback');
        config(['services.google.redirect' => $redirectUrl]);
        
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $redirectUrl = url('/api/v1/auth/google/callback');
            config(['services.google.redirect' => $redirectUrl]);
            
            // Pass guzzle options to disable SSL verification
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            \Illuminate\Support\Facades\Log::info('Google user:', [
                'id' => $googleUser->id,
                'email' => $googleUser->email,
                'name' => $googleUser->name,
            ]);

            $user = User::where('email', $googleUser->email)->first();

            if (! $user) {
                $username = str_replace(' ', '_', $googleUser->name) ?: 'google_user_' . time();

                $user = User::create([
                    'name'      => $username,
                    'email'     => $googleUser->email,
                    'password'  => Hash::make(uniqid()),
                    'google_id' => $googleUser->id,
                ]);
            }

            $token  = Auth::guard('api')->login($user);
            $cookie = JwtHelper::makeJwtCookie($token);

            $frontendUrl = config('app.frontend_url', 'http://localhost:3002');

            return redirect()->to($frontendUrl . '/auth/google/callback?token=' . $token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google auth failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            $frontendUrl = config('app.frontend_url', 'http://localhost:3002');
            return redirect()->to($frontendUrl . '/login?error=google_auth_failed');
        }
    }

    // =============================
    // Admin: list all users
    // =============================
    public function listUsers()
    {
        $users = User::with('roles')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data'   => $users,
        ]);
    }
}
