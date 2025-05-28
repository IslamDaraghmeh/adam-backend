<?php

use App\Http\Controllers\AccountStatmentController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('login', [AuthController::class, 'login']);
Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
Route::apiResource('ads', AdController::class)->only(['index', 'show']);
Route::get('admin/ads', [AdController::class, 'getAllAds']);
Route::get('storage/{path}', function ($path) {
    return Storage::disk('s3')->response($path);
})->where('path', expression: '.*');

Route::get('healthz', function () {
    return response()->json(['status' => 'ok']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user_id}/reset-password', [UserController::class, 'resetPassword']);
    Route::patch('users/{user_id}/update', [UserController::class, 'adminUpdate']);
    Route::post('users/{user_id}/containers', [ContainerController::class, 'store']);
    Route::post('users/{user_id}/account-statments', [AccountStatmentController::class, 'store']);
    Route::apiResource('places', PlaceController::class)->only(['store', 'destroy', 'update']);
    Route::apiResource('ads', AdController::class)->only(['store', 'destroy', 'update']);
    Route::apiResource('notifications', NotificationController::class);
    Route::get('users/{user_id}/containers', [UserController::class, 'getUserContainers']);
    Route::get('user/containers', [UserController::class, 'getContainersForLoggedUser']);
    Route::get('user/account-statments', [UserController::class, 'getAccountStatmentsForLoggedUser']);
    Route::get('users/{user_id}/account-statments', [UserController::class, 'getUserAccountStatments']);
    Route::get('users/{user_id}/uploaded-files', [UserController::class, 'getUploadedFile']);
    Route::patch('user/password', [AuthController::class, 'changePassword']);
    Route::patch('user/mobile', [UserController::class, 'changeMobile']);
    Route::post('user/notifications/send', [UserController::class, 'sendNotification']);
    Route::post('user/token', action: [UserController::class, 'StoreMobileToken']);
    Route::patch('ads/{ad_id}/publish', [AdController::class, 'publishAndUnpublish']);
    Route::get('statistics', [GeneralController::class, 'index']);
});