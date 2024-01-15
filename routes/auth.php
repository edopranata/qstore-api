<?php


use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', LoginController::class);
Route::post('logout', LogoutController::class);
