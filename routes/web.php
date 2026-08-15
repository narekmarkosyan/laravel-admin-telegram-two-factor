<?php

use Narekmarkosyan\LaravelAdminTelegramTwoFactor\Http\Controllers\AuthController;

Route::get('auth/2fa', [AuthController::class, 'getTwoFactor'])->name('auth.2fa.telegram')->withoutMiddleware('admin.auth.2fa.telegram');
Route::post('auth/2fa', [AuthController::class, 'postTwoFactor'])->name('auth.2fa.telegram')->withoutMiddleware('admin.auth.2fa.telegram');
Route::get('auth/2fa/resend', [AuthController::class, 'getTwoFactorResend'])->name('auth.2fa.telegram.resend')->withoutMiddleware('admin.auth.2fa.telegram');
