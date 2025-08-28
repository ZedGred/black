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
        if (auth()->check()) {
            $authUser = auth()->user();
            $menus = collect(config('menu'))
                ->filter(fn($menu) => auth()->user()->can($menu['permissions']))
                ->toArray();


            return [$authUser, $menus];
        }
        return [[], []];
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

        return view('pages.homepage.home', compact('menus', 'authUser'));
    }

    public function showProfile($username)
    {
        $profileUser = User::whereRaw('LOWER(name) = ?', [strtolower($username)])->firstOrFail();
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.account.profile', compact('profileUser', 'authUser', 'menus'));
    }

    public function showProfileAbout($username)
    {
        $profileUser = User::whereRaw('LOWER(name) = ?', [strtolower($username)])->firstOrFail();
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.account.about', compact('profileUser', 'authUser', 'menus'));
    }

    public function showRegisterWriter()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.account.registerwriter', compact('menus', 'authUser'));
    }

    public function showStoryWrite()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.article.write', compact('menus', 'authUser'));
    }

    public function showStoriesPublic()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.stories.published', compact('menus', 'authUser'));
    }

    public function showStoriesDrafts()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.stories.drafts', compact('menus', 'authUser'));
    }

    public function showArticle()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.article.index', compact('menus', 'authUser'));
    }

    public function showUsersPage()
    {
        [$authUser, $menus] = $this->getUserAndMenus();

        return view('pages.master.users', compact('menus', 'authUser'));
    }
}
