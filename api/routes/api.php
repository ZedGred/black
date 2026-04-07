<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleLikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentLikeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;

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
// Public Auth Routes (Rate limited: 10/min)
// =============================
Route::middleware('throttle:auth')->group(function () {
    Route::post('register/users', [AuthController::class, 'registerUser']);
    Route::post('register/verify', [AuthController::class, 'verifyEmailPassword']);
    Route::post('register/resend', [AuthController::class, 'resendVerificationCode']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh/token', [AuthController::class, 'refreshToken']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Google OAuth Routes
Route::middleware('web')->group(function () {
    Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// =============================
// Public Content Routes
// =============================
// Articles
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);
Route::get('/articles/{article}/comments', [CommentController::class, 'index']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Articles milik user tertentu (by username)
Route::get('/users/{user:name}/articles', [ArticleController::class, 'userArticle']);

// Followers (public)
Route::get('/users/{user:name}/followers', [FollowController::class, 'followers']);
Route::get('/users/{user:name}/following', [FollowController::class, 'following']);


// =============================
// Routes for Authenticated Users
// =============================
Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Follow/Unfollow
    Route::post('/follow', [FollowController::class, 'follow']);
    Route::post('/unfollow', [FollowController::class, 'unfollow']);
    Route::get('/users/{user:name}/follow/check', [FollowController::class, 'checkFollow']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/{notification}', [NotificationController::class, 'show']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'delete']);
    });

    // Routes for comments (write throttle: 30/min)
    Route::prefix('comments')->middleware('throttle:write')->group(function () {
        Route::post('/articles/{article}', [CommentController::class, 'store'])->middleware('permission:comments.create');
        Route::put('/{comment}', [CommentController::class, 'update'])->middleware('permission:comments.update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->middleware('permission:comments.delete');

        // Like/unlike comment
        Route::post('/{comment}/like', [CommentLikeController::class, 'like'])->middleware('permission:comments.like');
        Route::delete('/{comment}/like', [CommentLikeController::class, 'unlike'])->middleware('permission:comments.like');
    });

    // Routes for articles (write throttle: 30/min)
    Route::prefix('articles')->group(function () {
        Route::post('/', [ArticleController::class, 'store'])->middleware(['permission:articles.create', 'throttle:write']);
        Route::put('/{article}', [ArticleController::class, 'update'])->middleware(['permission:articles.update', 'throttle:write']);
        Route::delete('/{article}', [ArticleController::class, 'destroy'])->middleware('permission:articles.delete');

        // Like/unlike Article
        Route::post('/{article}/like', [ArticleLikeController::class, 'like'])->middleware('permission:articles.like');
        Route::delete('/{article}/like', [ArticleLikeController::class, 'unlike'])->middleware('permission:articles.like');

        // Draft routes
        Route::get('/my/drafts', [ArticleController::class, 'myDrafts']);
        Route::get('/my/articles', [ArticleController::class, 'myArticles']);
        Route::post('/{article}/publish', [ArticleController::class, 'publishDraft']);
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

        // Categories (CRUD - all authenticated users)
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{category}', [CategoryController::class, 'update']);
            Route::delete('/{category}', [CategoryController::class, 'destroy']);
        });
    });
});
