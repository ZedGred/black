<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\UserHelper;

class FrontendController extends Controller
{
    /**
     * Ambil user login + menu sesuai role
     */
    private function getUserAndMenus()
    {
        $authUser = auth()->user();
        $menus = [];

        if ($authUser) {
            $roleName = $authUser->getRoleNames()->first();
            $menus = config("menu.$roleName", []);
        }

        return [$authUser, $menus];
    }

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

    public function showHome()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.home', compact('menus', 'authUser'));
    }

    public function showProfile($username)
    {
        $profileUser = User::where('name', $username)->firstOrFail();
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.profile', compact('profileUser', 'authUser', 'menus'));
    }

    public function showRegisterWriter()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('auth.registerwriter', compact('menus', 'authUser'));
    }

    public function showStoryWrite()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.write', compact('menus', 'authUser'));
    }

    public function showStoriesPublic()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.stories', compact('menus', 'authUser'));
    }

    public function showArticle()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.articles', compact('menus', 'authUser'));
    }
}
