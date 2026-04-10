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
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    // =============================
    // Register
    // =============================
    public function registerUser(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    // =============================
    // Resend Verification Code
    // =============================
    public function resendVerificationCode(Request $request) {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->whereNull('email_verified_at')->first();
        if (!$user) {
            return response()->json(['message' => 'No unverified account found with this email.'], 400);
        }
        
        $verificationToken = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => now()->addMinutes(5),
        ]);
        
        \Illuminate\Support\Facades\Mail::raw("Welcome to BLACK.\n\nYour NEW 6-digit verification code is:\n\n$verificationToken\n\nPlease enter this code on the verification page.", function ($message) use ($user) {
            $message->to($user->email)->subject('Your New Verification Code - BLACK Platform');
        });
        
        return response()->json(['success' => true, 'message' => 'Code resent successfully!']);
    }

    // =============================
    // Verify & Set Password
    // =============================
    public function verifyEmailPassword(\App\Http\Requests\Auth\VerifyPasswordRequest $request)
    {
        try {
            $result = $this->authService->verifyPassword($request->email, $request->token);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
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
                    'email_verified_at' => now(),
                ]);
            } else {
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    if (!$user->google_id) {
                        $user->google_id = $googleUser->id;
                    }
                    $user->save();
                }
            }

            $token  = Auth::guard('api')->login($user);
            $cookie = JwtHelper::makeJwtCookie($token);

            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

            return redirect()->to($frontendUrl . '/auth/google/callback?token=' . $token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google auth failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
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

    // =============================
    // Me (get authenticated user) - extended
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
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username ?? $user->name,
                    'email'    => $user->email,
                    'avatar'   => $user->avatar,
                    'bio'      => $user->bio,
                ],
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles'       => $user->getRoleNames(),
            ],
        ]);
    }

    // =============================
    // Public Profile (by username)
    // =============================
    public function publicProfile(string $username)
    {
        $user = User::where('username', $username)
            ->orWhere('name', $username)
            ->firstOrFail();

        $articles = $user->articles()
            ->where('status', 'published')
            ->withCount('likedUsers')
            ->withCount('comments')
            ->latest('published_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'username'        => $user->username ?? $user->name,
                    'avatar'          => $user->avatar,
                    'bio'             => $user->bio,
                    'followers_count' => $user->followers()->count(),
                    'following_count' => $user->following()->count(),
                    'articles_count'  => $user->articles()->count(),
                ],
                'articles' => \App\Http\Resources\ArticleResource::collection($articles)->response()->getData(true),
            ],
        ]);
    }

    // =============================
    // Update own profile
    // =============================
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'bio'    => 'sometimes|nullable|string|max:500',
            'avatar' => 'sometimes|nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username ?? $user->name,
                'email'    => $user->email,
                'avatar'   => $user->avatar,
                'bio'      => $user->bio,
            ],
        ]);
    }

    // =============================
    // Forgot Password
    // =============================
    public function forgotPassword(\App\Http\Requests\Auth\ForgotPasswordRequest $request)
    {
        try {
            $result = $this->authService->sendPasswordResetEmail($request->email);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // =============================
    // Reset Password
    // =============================
    public function resetPassword(\App\Http\Requests\Auth\ResetPasswordRequest $request)
    {
        try {
            $result = $this->authService->resetPassword($request->reset_token, $request->password);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // =============================
    // Verify Reset Token
    // =============================
    public function verifyResetToken(Request $request)
    {
        $request->validate(['reset_token' => 'required|string']);

        $passwordReset = \App\Models\PasswordReset::where('reset_token', $request->reset_token)
            ->where('is_used', false)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset token is valid',
        ]);
    }
}
