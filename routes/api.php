<?php

use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\User\UserGetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->post('user', UserGetController::class);
Route::middleware('auth:sanctum')->post('change-password', ChangePasswordController::class);
Route::middleware('auth:sanctum')->post('logout', LogoutController::class);


