<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class UserHelper
{
    /**
     * Ambil user login + menu sesuai role
     */
    public static function getUserAndMenus()
    {
        $authUser = Auth::user();
        $menus = [];

        if ($authUser) {
            $roleName = $authUser->getRoleNames()->first();
            $menus = config("menu.$roleName", []);
        }

        return [$authUser, $menus];
    }
}
