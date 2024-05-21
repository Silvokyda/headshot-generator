<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

    

Route::post('/upload-image', [ImageController::class, 'upload'])->name('upload_image');
    

require __DIR__.'/auth.php';
