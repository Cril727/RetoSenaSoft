<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    //
    public function me()
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(["message" => "Usuario o token invalido"],401);
        }

        return response()->json(['user' => $user]);
    }


    // List all users 
    public function index()
    {
        $users = User::all();
    
        if (!$users) {
            return response()->json(["message" => "Error al obtener los usuarios"], 500);
        }
    
        return response()->json(['success' => true, 'users' => $users], 200);
    }
    
    //find user by id
    public function userById($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'No se ha encontrado el usuario'], 404);
        }
    
        return response()->json(['success' => true, 'usuario' => $user]);
    }
    
    //create new user
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                "full_name" => "required|string",
                "email" => "required|email",
                "password" => "required|string",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()]);
            }
    
            $user = User::create([
                "full_name" => $request->full_name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
            ]);

            if($user){
                $newPassenger  = Passenger::create([
                    "full_name" => $request->full_name,
                    "email" => $request->email,
                ]);

                if(!$newPassenger){
                    return response()->json(["message" => "error al crear el pasajero"]);
                }

            }
    
            return response()->json(["success" => true, "message" => "Ususario creado correctamente", "user" => $user, "passenger" => $newPassenger]);
        } catch (\Throwable $th) {
            Log::error('Error al crear', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th]);
        }
    }
    
    //Update users
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
    
            if (!$user) {
                return response()->json(['message' => 'No se ha encontrado el usuario'], 401);
            }
    
            $validated = Validator::make($request->all(), [
                "full_name" => "string",
                "email" => "email",
                "password" => "string",
            ]);
    
            if ($validated->fails()) {
                return response()->json(["errors" => $validated->errors()], 422);
            }
    
            $actualizar = $user->update($validated->validated());
    
            if (!$actualizar) {
                return response()->json(["message" => "No se ha podido actualizar el usuario"], 500);
            }
    
            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'usuario' => $user], 200);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar', ['error' => $th->getMessage()]);
            return response()->json(['message' => "Error interno del servidor", $th]);
        }
    }
    
    
}
