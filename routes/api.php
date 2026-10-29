<?php

use App\Http\Controllers\Api\CertificateVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/verify', CertificateVerificationController::class)
    ->middleware('throttle:20,1')
    ->name('api.verify');
