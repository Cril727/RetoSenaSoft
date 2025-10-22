<?php

namespace App\Http\Controllers;

use App\Models\Payer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayerController extends Controller
{
    //
    // Obtener todos
    public function index()
    {
        $payers = Payer::all();
    
        if (!$payers) {
            return response()->json(["message" => "Error al obtener los pagadores"], 500);
        }
    
        return response()->json(['success' => true, 'payers' => $payers], 200);
    }
    
    //Obtener por Id
    public function payById($id)
    {
        $payer = Payer::find($id);
    
        if (!$payer) {
            return response()->json(['message' => 'No se ha encontrado el pagador'], 404);
        }
    
        return response()->json(['success' => true, 'payer' => $payer]);
    }
    
    //Crear
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                "full_name" => "required|string",
                "type_document" => "required|string",
                "document" => "required|string",
                "email" => "required|email",
                "phone" => "required|string",
                "payment_method" => "required|string",
                "number_card" => "required|numeric",
                "document" => "required|string",
                "cvv" => "required|numeric",
                "expiration_date" => "required|date",
                "pse_method" => "string",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()]);
            }
    
            $payer = Payer::create([
                "full_name" => $request->full_name,
                "type_document" => $request->type_document,
                "document" => $request->document,
                "email" => $request->email,
                "phone" => $request->phone,
                "payment_method" => $request->payment_method,
                "number_card" => $request->number_card,
                "document" => $request->document,
                "cvv" => $request->cvv,
                "expiration_date" => $request->expiration_date,
                "pse_method" => $request->pse_method,
            ]);
    
            return response()->json(["success" => true, "message" => "Pagador creado correctamente", "payer" => $payer]);
        } catch (\Throwable $th) {
            Log::error('Error al crear', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th]);
        }
    }
    
    //Actualizar
    public function update(Request $request, $id)
    {
        try {
            $payer = Payer::find($id);
    
            if (!$payer) {
                return response()->json(['message' => 'No se ha encontrado la informacion de este usuario'], 401);
            }
    
            $validated = Validator::make($request->all(), [
                "full_name" => "string",
                "type_document" => "string",
                "document" => "string",
                "email" => "email",
                "phone" => "string",    
                "payment_method" => "string",
                "number_card" => "numeric",
                "cvv" => "numeric",
                "expiration_date" => "date",
                "pse_method" => "string",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }
    
            $actualizar = $payer->update($validated->validated());
    
            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar el usuario"], 500);
            }
    
            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'payer' => $payer], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th]);
        }
    }
    
    
}
