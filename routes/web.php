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

Route::get('/login', [FrontendController::class, 'showLogin'])->name('home');
Route::post('/login', [FrontendController::class, 'showLogin'])->name('login');
Route::post('/register', [FrontendController::class, 'showRegister'])->name('register');
Route::get('/register', [FrontendController::class, 'showRegister'])->name('register');


Route::middleware('jwt.cookie')->group(function () {
    Route::get('/dashboard', [FrontendController::class, 'showDashboard'])->name('dashboard');
    Route::get('/{username}', [FrontendController::class, 'showProfile'])->name('profile');
    Route::post('/registerwriter', [FrontendController::class, 'showRegisterWriter'])->name('registerwriter');
    Route::get('/registerwriter', [FrontendController::class, 'showRegisterWriter'])->name('registerwriter');
});
