<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

use function Psy\debug;

class FrontendController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function showLanding()
    {
        return view('landing');
    }
    public function showDashboard()
    {
        $user = auth()->user();
        $roleName = auth()->user()->getRoleNames()->first();
        $menus = config("menu.$roleName", []);
        
        return view('pages.home', compact('menus', 'user'));
    }
    public function showProfile($username)
    {
        $user = auth()->user();
        $username = User::where('name', $username)->firstOrFail();
        $roleName = auth()->user()->getRoleNames()->first();
        $menus = config("menu.$roleName", []);// ambil menu sesuai role
        return view('pages.profile', [
            'user' => $user,
            'menus' => $menus,
            'username' => $username,
        ]);
    }

    public function showRegisterWriter()
    {
        Log::debug("registerew");
        return view('auth.registerwriter');
    }
}
