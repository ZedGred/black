<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleLIkeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentLikeController;
use App\Http\Controllers\FrontendController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =============================
// Public Auth Routes
// =============================
Route::post('register/users', [AuthController::class, 'registerUser']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

// Public content
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);
Route::get('articles/{article}/comments', [CommentController::class, 'index']);


// =============================
// Routes for Authenticated Users
// =============================
Route::middleware('auth:api')->group(function () {
    Route::post('register/writer', [AuthController::class, 'registerWriter']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Routes for comments
    Route::prefix('comments')->group(function () {
        Route::post('/articles/{article}', [CommentController::class, 'store'])->middleware('permission:comments.create');
        Route::put ('/{comment}', [CommentController::class, 'update'])->middleware('permission:comments.edit');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->middleware('permission:comments.delete');

        // Like/unlike comment
        Route::post('/{comment}/like', [CommentLikeController::class, 'like'])->middleware('permission:comments.like');
        Route::delete('/{comment}/like', [CommentLikeController::class, 'unlike'])->middleware('permission:comments.like');
    });

    // Routes  for articles
    Route::prefix('articles')->group(function () {
        Route::post('/', [ArticleController::class, 'store'])->middleware('permission:articles.create');
        Route::put('/{article}', [ArticleController::class, 'update'])->middleware('permission:articles.edit');
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->middleware('permission:articles.like');

        // Like/unlike Artcile
        Route::post('/{article}/like', [ArticleLikeController::class, 'like'])->middleware('permission:articles.like');
        Route::delete('/{article}/like', [ArticleLikeController::class, 'unlike'])->middleware('permission:articles.like');
    });

    Route::middleware('permission:users.view')->group(function () {
        Route::get('admin/users', [AuthController::class, 'listUsers']);
    });

    Route::get('/api/dashboard', [FrontendController::class, 'showDashboards'])->name('dashboard');
});
