<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //method login for users
    public function login(Request $request)
    {
        //validation of email and password
        $validated = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|string"
        ]);

        //return error response if validation is fails
        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        //credentials
        $credenciales = $request->only('email', 'password');

        //Verification of credentials
        if (!$token = JWTAuth::attempt($credenciales)) {
            return response()->json(['success' => false, 'errors' => 'credenciales invalidas'], 401);
        }

        //user verificated
        $user = JWTAuth::user();

        //return user and token
        return response()->json([
            'success' => true,
            'message' => 'Bienvenido',
            'token' => $token,
            'user' => $user
        ]);
    }

    //method of logout
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error al cerar la sesion, intentalo nuevamente'], 500);
        }
        return response()->json(['message' => 'Sesion cerrada correctamente']);
    }

}
