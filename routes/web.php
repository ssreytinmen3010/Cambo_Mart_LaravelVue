<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\User\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role->name == 'admin') {
            return Inertia::render('Admin/Dashboard');
        } else {
            return Inertia::render('user/Dashboard');
        }
    } else {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    if (auth()->user()->role->name == 'admin') {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('user.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin/dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');

Route::get('/user/dashboard', function () {
    return Inertia::render('user/Dashboard');
})->middleware(['auth', 'verified', 'role:user'])->name('user.dashboard');

Route::get('/admin/users/list', [UserController::class, 'list'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::post('/admin/users/list/add', [UserController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:admin'])->name('admin.users.store');

Route::patch('/admin/users/list/update/{user}', [UserController::class, 'update'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::patch('/admin/users/{user}/status', [UserController::class, 'updateStatus'])
    ->middleware(['auth', 'verified', 'role:admin']);

Route::get('/admin/users', function () {
    return Inertia::render('Admin/User');
})->middleware(['auth', 'verified', 'role:admin']);





Route::prefix('admin')->middleware(['auth','role:admin'])->group(function () {
    Route::get('/users/list', [UserController::class, 'list']);
    Route::post('/users/list/add', [UserController::class, 'add']);
    Route::get('/users/list/update/{userId}', [UserController::class, 'edit']);
    Route::patch('/users/list/update/{userId}', [UserController::class, 'update']);
    Route::delete('/users/list/{userId}', [UserController::class, 'destroy']);
    Route::patch('/users/list/{userId}/status', [UserController::class, 'updateStatus']);
});



require __DIR__.'/auth.php';
