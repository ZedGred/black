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
        $username = User::where('name', $username)->firstOrFail();
        $authUser = auth()->user();
        $roleName = $authUser->getRoleNames()->first();
        $menus = config("menu.$roleName", []);

        return view('pages.profile', [
            'user' => $authUser,
            'menus' => $menus,
            'username' => $username,
        ]);
    }

    public function showRegisterWriter()
    {
        $user = auth()->user();
        $roleName = auth()->user()->getRoleNames()->first();
        $menus = config("menu.$roleName", []);

        return view('auth.registerwriter', compact('menus', 'user'));
    }
    public function showStoryWrite()
    {
        $user = auth()->user();
        $roleName = auth()->user()->getRoleNames()->first();
        $menus = config("menu.$roleName", []);

        return view('pages.write', compact('menus', 'user'));
    }
    public function showStoriesPublic()
    {
        $user = auth()->user();
        $roleName = auth()->user()->getRoleNames()->first();
        $menus = config("menu.$roleName", []);

        return view('pages.stories', compact('menus', 'user'));
    }
}
