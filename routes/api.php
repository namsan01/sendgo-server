<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomPasswordController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [AuthController::class, 'register']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 비밀번호 재설정 링크 전송
Route::post('password/email', [CustomPasswordController::class, 'sendResetLinkEmail']);
// 비밀번호 재설정 처리
Route::post('password/reset', [CustomPasswordController::class, 'reset']);

Route::middleware('auth:api')->post('/payments', [PaymentController::class, 'store']);
Route::get('/user/payments', [PaymentController::class, 'getUserPayments'])->middleware('auth:api');
