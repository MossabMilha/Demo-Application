<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('login', [AuthController::class, 'login']);


Route::post('Admin/CreateUser', [AdminController::class, 'createUser']);
Route::post('Admin/UpdateUser', [AdminController::class, 'updateUser']);
Route::GET('Admin/ShowUsers', [AdminController::class, 'ShowUsers']);
