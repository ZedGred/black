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
     * Register a new user, issue JWT and refresh token.
     */
    public function register(array $data): array
    {
        $username = str_replace(' ', '_', $data['name']);

        $user = User::create([
            'name'     => $username,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

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
