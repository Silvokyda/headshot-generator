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

Route::post('/generate-headshot', [ImageController::class, 'generateHeadshot'])->name('generate_headshot');

Route::post('/face-swap', [ImageController::class, 'faceSwap'])->name('face_swap');
    

require __DIR__.'/auth.php';


use App\Http\Controllers\ReplicateWebhookController;

Route::post('/replicate/webhook', [ReplicateWebhookController::class, 'replicateWebhook'])->name('replicate.webhook');
