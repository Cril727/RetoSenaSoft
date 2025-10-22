<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PayerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post("login", [AuthController::class, "login"]);
Route::post('/addUser', [UserController::class, 'store']);



Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get("me", [UserController::class, "me"]);

    //endpoints users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/userById/{id}', [UserController::class, 'userById']);
    Route::put('/updateUser/{id}', [UserController::class, 'update']);


    //endpoints passenger
    Route::get('/passengers', [PassengerController::class, 'index']);
    Route::post('/addPassenger', [PassengerController::class, 'store']);
    Route::get('/passengerById/{id}', [PassengerController::class, 'userById']);
    Route::put('/updatePassenger/{id}', [PassengerController::class, 'update']);
    Route::delete('/deletePassenger/{id}', [PassengerController::class, 'delete']);


    //endpoints pay
    Route::get('/pays', [PayerController::class, 'index']);

    Route::get('/payById/{id}', [PayerController::class, 'payById']);
    Route::put('/updatePay/{id}', [PayerController::class, 'update']);
});
    Route::post('/pay', [PayerController::class, 'store']);
