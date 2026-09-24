<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');

Route::get('/posts/create', [PostController::class, 'create'])
    ->middleware('auth')
    ->name('posts.create');

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth')
    ->name('posts.store');

Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::get('/posts/{post:slug}/edit', [PostController::class, 'edit'])
    ->middleware('auth')
    ->name('posts.edit');

Route::put('/posts/{post:slug}', [PostController::class, 'update'])
    ->middleware('auth')
    ->name('posts.update');

Route::delete('/posts/{post:slug}', [PostController::class, 'destroy'])
    ->middleware('auth')
    ->name('posts.destroy');

Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

Route::post('/posts/{post:slug}/comments/{comment}/reply', [CommentController::class, 'reply'])
    ->middleware('auth')
    ->name('comments.reply');

Route::delete('/posts/{post:slug}/comments/{comment}', [CommentController::class, 'destroy'])
    ->middleware('auth')
    ->name('comments.destroy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
