<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PassengerController extends Controller
{
    //
    public function index()
    {
        $passengers = Passenger::all();
    
        if (!$passengers) {
            return response()->json(["message" => "Error al obtener los pasajeros"], 500);
        }
    
        return response()->json(['success' => true, 'passengers' => $passengers], 200);
    }
    
    //Obtener por Id
    public function passengerById($id)
    {
        $passenger = Passenger::find($id);
    
        if (!$passenger) {
            return response()->json(['message' => 'No se ha encontrado el pasajero'], 404);
        }
    
        return response()->json(['success' => true, 'pass' => $passenger]);
    }
    
    
    //Actualizar
    public function update(Request $request, $id)
    {
        try {
            $passenger = Passenger::find($id);
    
            if (!$passenger) {
                return response()->json(['message' => 'No se ha encontrado el pasajero'], 401);
            }
    
            $validated = Validator::make($request->all(), [
                "full_name" => "string",
                "date_birth" => "date", 
                "gender" => "string",
                "type_document" => "string",
                "document" => "string",
                "condicien_infante" => "boolean",
                "phone" => "numeric",
                "email" => "email"
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }
    
            $actualizar = $passenger->update($validated->validated());
    
            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar el pasajero"], 500);
            }
    
            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'passenger' => $passenger], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", 'error' => $th->getMessage()], 500);
        }
    }
    
    //Eliminar
    public function delete($id)
    {
        $passenger = Passenger::find($id);
        $user = User::find($id);
    
        if (!$passenger || !$user) {
            return response()->json(['message' => 'No se ha encontrado el pasajero'], 401);
        }
    
        try {
            $passenger->delete();
            $user->delete();
            return response()->json(['message' => 'Eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'No se pudo eliminar', 'error' => $th->getMessage()], 500);
        }
    }
    
}
