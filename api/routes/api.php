<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('v1.')->prefix('v1')->group(function () {

    Route::post('signup', \App\Http\Controllers\Api\RegistrationController::class)
        ->name('signup')
        ->middleware('guest:api');
    Route::get('/verify-email/{user:email}/{code}', App\Http\Controllers\Api\VerifyEmailController::class)
        ->middleware(['throttle:6,1'])
        ->name('verification.verify');
    Route::post('/login', \App\Http\Controllers\Api\AuthenticationController::class)
        ->middleware('guest')
        ->name('login');
    Route::post('/forgot-password', \App\Http\Controllers\Api\ResetPasswordCodeController::class)
        ->middleware('guest')
        ->name('forgot.password');
    Route::post('/reset-password', \App\Http\Controllers\Api\ResetPasswordController::class)
        ->middleware('guest')
        ->name('reset.password');
    Route::post('/email/verification-notification', \App\Http\Controllers\Api\EmailVerificationNotificationCodeController::class)
        ->middleware(['guest:api', 'throttle:6,1'])
        ->name('verification.send');
    Route::post('/users/logout', \App\Http\Controllers\Api\LogoutController::class)
        ->middleware('auth:api')
        ->name('users.logout');

    Route::apiResource('payments', \App\Http\Controllers\Api\PaymentController::class)
        ->middleware('auth:api')->only(['index', 'store', 'show']);
    Route::get('balance', [\App\Http\Controllers\Api\AccountController::class, 'show'])
        ->middleware('auth:api');
    Route::get('transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index'])
        ->middleware('auth:api')->name('transactions.index');
});
