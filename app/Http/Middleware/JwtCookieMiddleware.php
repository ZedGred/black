<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtCookieMiddleware
{


    public function handle($request, Closure $next)
    {
        $token = $request->cookie('token');

        Log::info('Middleware jwt.cookie dijalankan');
        Log::info('Token dari cookie:', ['token' => $token]);

        if (!$token) {
            Log::warning('Token tidak ditemukan di cookie');
            return redirect('/login');
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            Log::info('User berhasil di-authenticate', ['user_id' => $user?->id]);

            if (!$user) {
                Log::warning('User tidak ditemukan dari token');
                return redirect('/login');
            }

            Auth::login($user);
            Log::info('User berhasil login via Auth facade');
        } catch (\Exception $e) {
            Log::error('Exception saat verifikasi token JWT: ' . $e->getMessage());
            return redirect('/login');
        }

        return $next($request);
    }
}
