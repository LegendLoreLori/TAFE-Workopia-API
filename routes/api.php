<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {
    Route::get('regions', [RegionController::class, 'index']);
    Route::get('regions/{id}', [RegionController::class, 'show']);
});


Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/users/trash/{id}', [UserController::class, 'restore']);
    Route::get('/companies/trash/{id}', [CompanyController::class, 'restore']);
    Route::get('/positions/trash/{id}', [PositionController::class, 'restore']);

    Route::apiResources([
            'companies' => CompanyController::class,
            'positions' => PositionController::class,
            'users' => UserController::class,
        ]
    );
});

Route::apiResource('v1/positions',PositionController::class)->only(['index']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
