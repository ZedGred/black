
<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleLIkeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentLikeController;

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
// Articles
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/articles/{article}/comments', [CommentController::class, 'index']);

// Stories milik user tertentu
// routes/web.php atau api.php
Route::get('/users/{user:name}/articles', [ArticleController::class, 'userArticle']);


// =============================
// Routes for Authenticated Users
// =============================
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Routes for comments
    Route::prefix('comments')->group(function () {
        Route::post('/articles/{article}', [CommentController::class, 'store'])->middleware('permission:comments.create');
        Route::put('/{comment}', [CommentController::class, 'update'])->middleware('permission:comments.update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->middleware('permission:comments.delete');

        // Like/unlike comment
        Route::post('/{comment}/like', [CommentLikeController::class, 'like'])->middleware('permission:comments.like');
        Route::delete('/{comment}/like', [CommentLikeController::class, 'unlike'])->middleware('permission:comments.like');
    });

    // Routes  for articles
    Route::prefix('articles')->group(function () {
        Route::post('/', [ArticleController::class, 'store'])->middleware('permission:articles.create');
        Route::put('/{article}', [ArticleController::class, 'update'])->middleware('permission:articles.update');
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->middleware('permission:articles.delete');

        // Like/unlike Artcile
        Route::post('/{article}/like', [ArticleLikeController::class, 'like'])->middleware('permission:articles.like');
        Route::delete('/{article}/like', [ArticleLikeController::class, 'unlike'])->middleware('permission:articles.like');
    });

    Route::middleware('permission:users.view')->group(function () {
        Route::get('admin/users', [AuthController::class, 'listUsers']);
    });

    Route::prefix('master-data')->group(function () {
        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('permission:users.show');
            Route::get('/{user}', [UserController::class, 'show'])->middleware('permission:users.show');
            Route::post('/', [UserController::class, 'store'])->middleware('permission:users.store');
            Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
        });

        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{role}', [RoleController::class, 'show']);
            Route::put('/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
            Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions']);
            Route::get('/{role}/permissions', [RoleController::class, 'getPermissions']);
        });

        // Permissions
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::get('/{permission}', [PermissionController::class, 'show']);
            Route::put('/{permission}', [PermissionController::class, 'update']);
            Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        });
    });
});
