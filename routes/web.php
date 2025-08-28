<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('landing');
});

Route::get('/login', [FrontendController::class, 'showLogin'])->name('login');
Route::get('/register', [FrontendController::class, 'showRegister'])->name('register');

Route::get('/home', [FrontendController::class, 'showHome'])
    ->name('home');
Route::middleware(['jwt.cookie'])->group(function () {

    Route::middleware('role:user')->group(function () {
        Route::get('/registerwriter', [FrontendController::class, 'showRegisterWriter'])
            ->name('registerwriter');
    });
    Route::middleware('permission:articles.create')->group(function () {
        Route::get('/new-story', [FrontendController::class, 'showStoryWrite'])
            ->name('write');
        Route::get('/new-story', [FrontendController::class, 'showStoryWrite'])
            ->name('write');
        Route::get('/me/stories/published', [FrontendController::class, 'showStoriesPublic'])
            ->name('published');
        Route::get('/me/stories/drafts', [FrontendController::class, 'showStoriesDrafts'])
            ->name('drafts');
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/master-data/roles', [FrontendController::class, 'showRolesPage'])
            ->name('roles.index');
        Route::get('/master-data/users', [FrontendController::class, 'showUsersPage'])
            ->name('users.index');
    });
});

Route::get('/{username}', [FrontendController::class, 'showProfile'])
    ->where('username', '[A-Za-z0-9_]+') // Constraint biar ga bentrok sama route lain
    ->name('profile');

Route::get('/{username}/about', [FrontendController::class, 'showProfileAbout'])
    ->where('username', '[A-Za-z0-9_]+') // Constraint biar ga bentrok sama route lain
    ->name('about');



Route::get('/{username}/{article_id}', [FrontendController::class, 'showArticle'])
    ->whereUuid('article_id') // pastikan id bentuk UUID
    ->name('article.show');
