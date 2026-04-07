<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use App\Helpers\JwtHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Generate a new refresh token for a user and persist it.
     */
    public function generateRefreshToken(User $user, ?string $deviceId = null, ?string $deviceName = null): string
    {
        $token = bin2hex(random_bytes(64));

        RefreshToken::create([
            'user_id'     => $user->id,
            'token'       => $token,
            'device_id'   => $deviceId,
            'device_name' => $deviceName,
            'expires_at'  => now()->addDays(30),
        ]);

        return $token;
    }

    /**
     * Build a consistent token response array.
     */
    public function buildTokenResponse(User $user, string $accessToken, ?string $refreshToken = null): array
    {
        $user->load('roles', 'permissions');

        return [
            'success' => true,
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'token' => [
                    'access_token'  => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type'    => 'Bearer',
                    'expires_in'    => auth('api')->factory()->getTTL() * 60,
                ],
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles'       => $user->getRoleNames(),
            ],
        ];
    }

    /**
     * Register a new user, send email verification link.
     */
    public function register(array $data): array
    {
        $username = str_replace(' ', '_', $data['name']);
        // Generate a 6-digit OTP code directly formatted as a string
        $verificationToken = str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::where('email', $data['email'])->whereNull('email_verified_at')->first();
        
        $ghostNameUser = User::where('name', $username)->whereNull('email_verified_at')->first();
        if ($ghostNameUser && (!$user || $ghostNameUser->id !== $user->id)) {
            $ghostNameUser->delete();
        }

        if ($user) {
            $user->update([
                'name'     => $username,
                'password' => Hash::make($data['password']),
                'verification_token' => $verificationToken,
                'verification_token_expires_at' => now()->addMinutes(5),
            ]);
        } else {
            $user = User::create([
                'name'     => $username,
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'verification_token' => $verificationToken,
                'verification_token_expires_at' => now()->addMinutes(5),
            ]);
        }

        // Send 6-digit code directly in the email instead of a link
        \Illuminate\Support\Facades\Mail::raw("Welcome to BLACK.\n\nYour 6-digit verification code is:\n\n$verificationToken\n\nPlease enter this code on the verification page to complete your registration.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Verification Code - BLACK Platform');
        });

        return [
            'success' => true,
            'message' => 'Registration successful! We have sent a 6-digit verification code to your email.',
        ];
    }

    /**
     * Verify token based on email and set account as verified.
     */
    public function verifyPassword(string $email, string $token): array
    {
        $user = User::where('email', $email)
                    ->where('verification_token', $token)
                    ->where('verification_token_expires_at', '>', now())
                    ->first();

        if (!$user) {
            throw new \Exception('Invalid or expired verification code.', 400);
        }


        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        $accessToken  = Auth::guard('api')->login($user);
        $refreshToken = $this->generateRefreshToken($user);
        $cookie       = JwtHelper::makeJwtCookie($accessToken);

        return [
            'response' => $this->buildTokenResponse($user, $accessToken, $refreshToken),
            'cookie'   => $cookie,
        ];
    }

    /**
     * Attempt login, return access token, refresh token, and cookie.
     *
     * @throws \Exception on invalid credentials
     */
    public function login(array $credentials): array
    {
        $token = Auth::guard('api')->attempt($credentials);

        if (! $token) {
            throw new \Exception('Invalid email or password', 401);
        }

        $user         = Auth::guard('api')->user();
        $refreshToken = $this->generateRefreshToken($user);
        $cookie       = JwtHelper::makeJwtCookie($token);

        return [
            'response' => $this->buildTokenResponse($user, $token, $refreshToken),
            'cookie'   => $cookie,
        ];
    }

    /**
     * Rotate a refresh token: revoke old one, issue new access + refresh tokens.
     *
     * @throws \Exception if token invalid/expired
     */
    public function rotateRefreshToken(string $rawToken): array
    {
        $refreshToken = RefreshToken::where('token', $rawToken)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $refreshToken) {
            throw new \Exception('Invalid or expired refresh token', 401);
        }

        $user = $refreshToken->user;

        // Revoke old token
        $refreshToken->update(['revoked_at' => now()]);

        $newAccessToken  = Auth::guard('api')->login($user);
        $newRefreshToken = $this->generateRefreshToken($user);

        return $this->buildTokenResponse($user, $newAccessToken, $newRefreshToken);
    }
}
