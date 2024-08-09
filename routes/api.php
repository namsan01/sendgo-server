<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomPasswordController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    // 사용자 정보 조회
    Route::get('user', [UserController::class, 'show']);
    Route::post('upload-photo', [UserController::class, 'uploadPhoto']);
    Route::post('update-profile', [UserController::class, 'updateProfile']);

    Route::delete('user/delete', [AuthController::class, 'deleteAccount']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 소셜 로그인
Route::get('auth/kakao', [AuthController::class, 'redirectToProvider']);
Route::post('auth/kakao/callback', [AuthController::class, 'handleProviderCallback']);
Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::post('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// 비밀번호 재설정 링크 전송
Route::post('password/email', [CustomPasswordController::class, 'sendResetLinkEmail']);
// 비밀번호 재설정 처리
Route::post('password/reset', [CustomPasswordController::class, 'reset']);

Route::middleware('auth:api')->post('/payments', [PaymentController::class, 'store']);
Route::get('/user/payments', [PaymentController::class, 'getUserPayments'])->middleware('auth:api');
