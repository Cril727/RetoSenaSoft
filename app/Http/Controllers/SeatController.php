<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Airplane;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    // Obtener todos los asientos
    public function index()
    {
        $seats = Seat::all();

        if (!$seats) {
            return response()->json(["message" => "Error al obtener los asientos"], 500);
        }

        return response()->json(['success' => true, 'seats' => $seats], 200);
    }

    // Obtener asiento por ID
    public function seatById($id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json(['message' => 'No se ha encontrado el asiento'], 404);
        }

        return response()->json(['success' => true, 'seat' => $seat]);
    }

    // Crear asiento
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                "code" => "required|string",
                "class" => "string",
                "airplane_id" => "required|numeric|exists:airplanes,id",
            ]);

            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()]);
            }

            // Verificar capacidad del avión
            $airplane = Airplane::find($request->airplane_id);
            $currentSeatsCount = Seat::where('airplane_id', $request->airplane_id)->count();

            if ($currentSeatsCount >= $airplane->number_passengers) {
                return response()->json(["message" => "No se pueden crear más asientos. El avión ha alcanzado su capacidad máxima."], 400);
            }

            $seat = Seat::create([
                "code" => $request->code,
                "class" => $request->class ?? 'economy',
                "airplane_id" => $request->airplane_id,
            ]);

            return response()->json(["success" => true, "message" => "Asiento creado correctamente", "seat" => $seat]);
        } catch (\Throwable $th) {
            Log::error('Error al crear asiento', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor"], 500);
        }
    }

    // Actualizar asiento
    public function update(Request $request, $id)
    {
        try {
            $seat = Seat::find($id);

            if (!$seat) {
                return response()->json(['message' => 'No se ha encontrado el asiento'], 404);
            }

            $validated = Validator::make($request->all(), [
                "code" => "string",
                "class" => "string",
                "airplane_id" => "numeric|exists:airplanes,id",
            ]);

            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }

            // Si se cambia el airplane_id, verificar capacidad
            if ($request->has('airplane_id') && $request->airplane_id != $seat->airplane_id) {
                $airplane = Airplane::find($request->airplane_id);
                $currentSeatsCount = Seat::where('airplane_id', $request->airplane_id)->count();

                if ($currentSeatsCount >= $airplane->number_passengers) {
                    return response()->json(["message" => "No se puede mover el asiento. El avión destino ha alcanzado su capacidad máxima."], 400);
                }
            }

            $actualizar = $seat->update($validated->validated());

            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar el asiento"], 500);
            }

            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'seat' => $seat], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar asiento', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor"], 500);
        }
    }

    // Eliminar asiento
    public function delete($id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json(['message' => 'No se ha encontrado el asiento'], 404);
        }

        try {
            $seat->delete();
            return response()->json(['message' => 'Eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar asiento', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'No se pudo eliminar el asiento'], 500);
        }
    }
}