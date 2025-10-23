<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\OriginsAndDestinations;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PayerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SeatController;
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
    Route::get("/me", [UserController::class, "me"]);

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
    Route::post('/pay', [PayerController::class, 'store']);
    Route::get('/payById/{id}', [PayerController::class, 'payById']);
    Route::put('/updatePay/{id}', [PayerController::class, 'update']);

    //reservations endpoints
    Route::post('/addReservation', [ReservationController::class, 'store']);
    Route::post('/addReservationWithPayment', [ReservationController::class, 'storeWithPayment']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservationById/{id}', [ReservationController::class, 'reservationsById']);
    Route::put('/updateReservation/{id}', [ReservationController::class, 'update']);
    Route::delete('/deleteReservation/{id}', [ReservationController::class, 'delete']);
    Route::get('/myReservations', [ReservationController::class, 'myReservations']);
    Route::get('/passengersWithSeats/{flight_id}', [ReservationController::class, 'passengersWithSeats']);

    //flight endpoints
    Route::post('/addFlight', [FlightController::class, 'store']);
    Route::delete("/deleteFlight/{id}", [FlightController::class, "delete"]);
    Route::get('/flights', [FlightController::class, 'index']);
    Route::get('/flightById/{id}', [FlightController::class, 'flightById']);
    Route::put('/updateFlight/{id}', [FlightController::class, 'update']);
    Route::post('/searchFlights', [FlightController::class, 'searchFlights']);
    Route::get('/flights/{flight_id}/available-seats', [FlightController::class, 'availableSeats']);
    
    //se paga y luego crear la reserva
    //datos del avion y asientos
    //necestio que me tariags los datos de pasajero puesto y nombre
    //pdf del tickete
    //para que me traiga los pasajero puesto y nombre


    //seat endpoints
    Route::get('/seats', [SeatController::class, 'index']);
    Route::post('/addSeat', [SeatController::class, 'store']);
    Route::get('/seatById/{id}', [SeatController::class, 'seatById']);
    Route::put('/updateSeat/{id}', [SeatController::class, 'update']);
    Route::delete('/deleteSeat/{id}', [SeatController::class, 'delete']);


    //Origins and Destinations
    Route::get('/origins', [OriginsAndDestinations::class, 'origins']);
    Route::get('/destinations', [OriginsAndDestinations::class, 'destinations']);
    Route::get('/flightsByOrigin/{origin_id}', [OriginsAndDestinations::class, 'flightsByOrigin']);
    Route::get('/flightsByDestination/{destination_id}', [OriginsAndDestinations::class, 'flightsByDestination']);

    //Payment endpoints (PayU Latam)
    Route::post('/payment/create-order', [PaymentController::class, 'createPaymentOrder']);
    Route::get('/payment/response', [PaymentController::class, 'paymentResponse']);

});

// Payment confirmation endpoint (webhook - no auth required)
Route::post('/payment/confirmation', [PaymentController::class, 'paymentConfirmation']);
