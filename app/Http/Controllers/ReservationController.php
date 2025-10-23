<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReservationController extends Controller
{
    //
    // Obtener todos
    public function index()
    {
        $reservations = Reservation::all();

        if (!$reservations) {
            return response()->json(["message" => "Error al obtener las reservaciones"], 500);
        }

        return response()->json(['success' => true, 'reservations' => $reservations], 200);
    }

    //Obtener por Id
    public function reservationsById($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'No se ha encontrado la reservacion'], 404);
        }

        return response()->json(['success' => true, 'reservation' => $reservation]);
    }

    //Crear
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                "code" => "required|string",
                "worth" => "required|numeric",
                "status" => "required|string",
                "number_of_positions" => "required|numeric",
                "flight_id" => "required|numeric",
                "passenger_id" => "required|numeric",
                "payer_id" => "required|numeric",
            ]);

            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()]);
            }

            $reservation = Reservation::create([
                "code" => $request->code,
                "worth" => $request->worth,
                "status" => $request->status,
                "number_of_positions" => $request->number_of_positions,
                "flight_id" => $request->flight_id,
                "passenger_id" => $request->passenger_id,
                "payer_id" => $request->payer_id,
            ]);

            return response()->json(["success" => true, "message" => "Reservacion creado correctamente", "reservation" => $reservation]);
        } catch (\Throwable $th) {
            Log::error('Error al crear', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", 'error' => $th->getMessage()], 500);
        }
    }

    //Actualizar
    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::find($id);

            if (!$reservation) {
                return response()->json(['message' => 'No se ha encontrado la reservacion'], 401);
            }

            $validated = Validator::make($request->all(), [
                "code" => "string",
                "worth" => "numeric",
                "status" => "string",
                "number_of_positions" => "required|numeric",
                "flight_id" => "numeric",
                "passenger_id" => "numeric",
                "payer_id" => "numeric",
            ]);

            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }

            $actualizar = $reservation->update($validated->validated());

            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar la reservacion"], 500);
            }

            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'reservation' => $reservation], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", 'error' => $th->getMessage()], 500);
        }
    }

    //Eliminar
    public function delete($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'No se ha encontrado la reservacion'], 401);
        }

        try {
            $reservation->delete();
            return response()->json(['message' => 'Eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'No se pudo eliminar la reservacion', 'error' => $th->getMessage()], 500);
        }
    }


    //Mis reservaciones
    public function myReservations()
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $reservations = Reservation::with(['flight', 'passenger', 'payer'])
            ->whereHas('payer', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->get();

        return response()->json(['success' => true, 'reservations' => $reservations], 200);
    }

    //Pasajeros con puesto y nombre para un vuelo
    public function passengersWithSeats($flight_id)
    {
        $flightSeats = \App\Models\flightSeats::where('flight_id', $flight_id)
            ->with(['reservations.passenger', 'seat'])
            ->whereHas('reservations')
            ->get();

        $passengersWithSeats = $flightSeats->map(function ($flightSeat) {
            return $flightSeat->reservations->map(function ($reservation) use ($flightSeat) {
                return [
                    'passenger_name' => $reservation->passenger->full_name,
                    'seat_code' => $flightSeat->seat->code,
                ];
            });
        })->flatten(1);

        return response()->json(['success' => true, 'passengers_with_seats' => $passengersWithSeats], 200);
    }

}
