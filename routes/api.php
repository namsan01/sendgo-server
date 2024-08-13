<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CommentController;

// 인증이 필요한 라우트
Route::middleware('auth:sanctum')->group(function () {
    // 사용자 정보 조회
    Route::get('user', [UserController::class, 'show']);
    Route::post('upload-photo', [UserController::class, 'uploadPhoto']);
    Route::post('update-profile', [UserController::class, 'updateProfile']);

    Route::delete('user/delete', [AuthController::class, 'deleteAccount']);

    // 문의 관련 라우트
    Route::post('/ask', [ContentController::class, 'store']); // 문의 등록
    Route::get('/ask/{id}', [ContentController::class, 'show']); // 문의 상세 (수정된 부분)
    Route::get('/ask/{id}/comments', [CommentController::class, 'index']); // 댓글 목록
    Route::post('/ask/{id}/comments', [CommentController::class, 'store']); // 댓글 등록
});

// 사용자 인증 및 등록 라우트
Route::post('register', [AuthController::class, 'register']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 소셜 로그인
Route::get('auth/kakao', [AuthController::class, 'redirectToProvider']);
Route::post('auth/kakao/callback', [AuthController::class, 'handleProviderCallback']);
Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::post('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// 비밀번호 재설정 링크 전송 및 처리
Route::post('password/email', [CustomPasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [CustomPasswordController::class, 'reset']);

// 결제 관련 라우트
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/user/payments', [PaymentController::class, 'getUserPayments']);
});

// 공개된 문의 목록
Route::get('/contents', [ContentController::class, 'index']);
