<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class JwtHelper
{
    public static function makeJwtCookie($token)
    {
        $ttlMinutes = Auth::guard('api')->factory()->getTTL();

        return cookie(
            'token',    // nama cookie
            $token,     // value cookie
            $ttlMinutes,// durasi cookie dalam menit
            '/',        // path
            null,       // domain
            true,       // secure (HTTPS only)
            true        // httpOnly
        );
    }
}
