<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/auth/token', [TokenController::class, 'store'])
    ->name('auth.token.store');

Route::middleware(['auth:sanctum'])->apiResource('tasks', TaskController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
