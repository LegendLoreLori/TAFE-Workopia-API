<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::group(['prefix' => 'v1'], function () {
    Route::get('regions', [RegionController::class, 'index']);
    Route::get('regions/{id}', [RegionController::class, 'show']);
});

Route::group(['prefix' => 'v1'], function () {
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
