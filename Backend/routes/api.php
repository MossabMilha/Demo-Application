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
Route::post('Admin/ActiveUserAccount', [AdminController::class, 'activeUserAccount']);
Route::post('Admin/DeactivateUserAccount', [AdminController::class, 'deactivateUserAccount']);
Route::GET('Admin/ShowUsers', [AdminController::class, 'ShowUsers']);


Route::POST('Admin/AddLevel', [AdminController::class, 'addLevel']);
Route::POST('Admin/DeleteLevel', [AdminController::class, 'deleteLevel']);
Route::POST('Admin/EditLevel', [AdminController::class, 'editLevel']);
Route::POST('Admin/ShowLevel', [AdminController::class, 'showLevel']);

Route::POST('Admin/AddChoices', [AdminController::class, 'addChoices']);
Route::POST('Admin/DeleteChoices', [AdminController::class, 'deleteChoices']);
Route::POST('Admin/EditChoices', [AdminController::class, 'editChoices']);
Route::POST('Admin/ShowChoices', [AdminController::class, 'showChoices']);

Route::POST('Admin/AddSubject', [AdminController::class, 'addSubject']);
Route::POST('Admin/DeleteSubject', [AdminController::class, 'deleteSubject']);
Route::POST('Admin/EditSubject', [AdminController::class, 'editSubject']);
Route::POST('Admin/ShowSubject', [AdminController::class, 'showSubject']);
