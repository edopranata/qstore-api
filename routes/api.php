<?php

use App\Http\Controllers\Api\User\UserGetController;
use Illuminate\Http\Request;
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
Route::middleware('auth:sanctum')->get('test', [\App\Http\Controllers\Api\TestController::class, 'index']);
Route::get('menu', [\App\Http\Controllers\Api\TestController::class, 'get']);

