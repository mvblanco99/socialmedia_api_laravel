<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register',[UserController::class, 'register']);
Route::post('/login',[AuthController::class, 'login']);
Route::post('/logout',[AuthController::class, 'logout'])->middleware('auth:sanctum');

