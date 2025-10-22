<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FlightController extends Controller
{
    //
    // Obtener todos
    public function index()
    {
        $flight = Flight::all();
    
        if (!$flight) {
            return response()->json(["message" => "Error al obtener los usuarios"], 500);
        }
    
        return response()->json(['success' => true, 'flight' => $flight], 200);
    }
    
    //Obtener por Id
    public function flightById($id)
    {
        $flight = Flight::find($id);
    
        if (!$flight) {
            return response()->json(['message' => 'No se ha encontrado el vuelo'], 404);
        }
    
        return response()->json(['success' => true, 'flight' => $flight]);
    }
    
    //Crear
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                "departure_at" => 'required|date_format:Y-m-d H:i|after:now',
                "price" => "required|numeric",
                "destination_id" => "required|numeric",
                "origin_id" => "required|numeric",
                "airplane_id" => "required|numeric",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()]);
            }
    
            $flight = Flight::create([
                "departure_at" => $request->departure_at,
                "price" => $request->price,
                "destination_id" => $request->destination_id,
                "origin_id" => $request->origin_id,
                "airplane_id" => $request->airplane_id,
            ]);
    
            return response()->json(["success" => true, "message" => "Vuelo creado correctamente", "flight" => $flight]);
        } catch (\Exception $th) {
            Log::error('Error al crear', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th->getMessage()],400);
        }
    }
    
    //Actualizar
    public function update(Request $request, $id)
    {
        try {
            $flight = Flight::find($id);
    
            if (!$flight) {
                return response()->json(['message' => 'No se ha encontrado el vuelo'], 401);
            }
    
            $validated = Validator::make($request->all(), [
                "departure_at" => 'date_format:Y-m-d H:i|after:now',
                "price" => "numeric",
                "destination_id" => "numeric",
                "origin_id" => "numeric",
                "airplane_id" => "numeric",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }
    
            $actualizar = $flight->update($validated->validated());
    
            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar el vuelo"], 500);
            }
    
            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'flight' => $flight], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th]);
        }
    }
    
    // Eliminar
    public function delete($id)
    {
        $flight = Flight::find($id);

        if (!$flight) {
            return response()->json(['message' => 'No se ha encontrado el vuelo'], 401);
        }

        try {
            $flight->delete();
            return response()->json(['message' => 'Eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'No se pudo eliminar el vuelo ', $th], 500);
        }
    }

    // Obtener todos los asientos de un vuelo (disponibles y ocupados)
    public function availableSeats($flightId)
    {
        try {
            $flight = Flight::with('airplane')->find($flightId);

            if (!$flight) {
                return response()->json(['message' => 'No se ha encontrado el vuelo'], 404);
            }

            // Obtener TODOS los asientos del vuelo con su estado
            $allSeats = $flight->flightSeats()
                ->with('seat') // incluir información del asiento
                ->get()
                ->map(function ($flightSeat) {
                    return [
                        'id' => $flightSeat->id,
                        'flight_id' => $flightSeat->flight_id,
                        'seat_id' => $flightSeat->seat_id,
                        'status' => $flightSeat->status,
                        'hold_expires_at' => $flightSeat->hold_expires_at,
                        'seat' => [
                            'id' => $flightSeat->seat->id,
                            'code' => $flightSeat->seat->code,
                            'class' => $flightSeat->seat->class,
                            'airplane_id' => $flightSeat->seat->airplane_id
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'seats' => $allSeats,
                'airplane' => $flight->airplane
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error al obtener asientos', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // Buscar vuelos por origen y destino
    public function searchFlights(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'origin_id' => 'required|exists:origins,id',
                'destination_id' => 'required|exists:destinations,id',
            ]);

            if ($validated->fails()) {
                return response()->json(['errors' => $validated->errors()], 422);
            }

            $flights = Flight::where('origin_id', $request->origin_id)
                ->where('destination_id', $request->destination_id)
                ->with(['origin', 'destination', 'airplane'])
                ->get();

            if ($flights->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No se encontraron vuelos para esta ruta',
                    'flights' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'flights' => $flights
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error al buscar vuelos', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}
