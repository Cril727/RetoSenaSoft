<?php

// namespace App\Http\Controllers;

// use App\Models\Reservation;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Validator;

// class ReservationController extends Controller
// {
//     //
//     // Obtener todos
//     public function index()
//     {
//         $reservations = Reservation::all();
    
//         if (!$reservations) {
//             return response()->json(["message" => "Error al obtener las reservaciones"], 500);
//         }
    
//         return response()->json(['success' => true, 'reservations' => $reservations], 200);
//     }
    
//     //Obtener por Id
//     public function reservationsById($id)
//     {
//         $reservation = Reservation::find($id);
    
//         if (!$reservation) {
//             return response()->json(['message' => 'No se ha encontrado la reservacion'], 404);
//         }
    
//         return response()->json(['success' => true, 'reservation' => $reservation]);
//     }
    
//     //Crear
//     public function store(Request $request)
//     {
//         try {
//             $validated = Validator::make($request->all(), [
//                 "code" => "required|string",
//                 "worth" => "required|numeric",
//                 "status" => "required|string",
//                 "number_of_positions" => "required|numeric",
//                 "passenger_id" => "required|numeric",
//                 "payer_id" => "required|numeric",
//             ]);
    
//             if ($validated->fails()) {
//                 return response()->json(["errors" => $validated->errors()]);
//             }
    
//             $reservation = Reservation::create([
//                 "code" => $request->code,
//                 "worth" => $request->worth,
//                 "status" => $request->status,
//                 "number_of_positions" => $request->number_of_positions,
//                 "passenger_id" => $request->passenger_id,
//                 "payer_id" => $request->payer_id,
//             ]);
    
//             return response()->json(["success" => true, "message" => "Reservacion creado correctamente", "reservation" => $reservation]);
//         } catch (\Throwable $th) {
//             Log::error('Error al crear', ['error' => $th->getMessage()]);
//             return response()->json(['message' => "Error interno del servidor", $th]);
//         }
//     }
    
//     //Actualizar
//     public function update(Request $request, $id)
//     {
//         try {
//             $reservation = Reservation::find($id);
    
//             if (!$reservation) {
//                 return response()->json(['message' => 'No se ha encontrado la reservacion'], 401);
//             }
    
//             $validated = Validator::make($request->all(), [
//                 "code" => "string",
//                 "worth" => "numeric",
//                 "status" => "string",
//                 "number_of_positions" => "numeric",
//                 "passenger_id" => "numeric",
//                 "payer_id" => "numeric",
//             ]);
    
//             if ($validated->fails()) {
//                 return response()->json(["errors" => $validated->errors()], 422);
//             }
    
//             $actualizar = $reservation->update($validated->validated());
    
//             if (!$actualizar) {
//                 return response()->json(["message" => "No se ha podido actualizar la reservacion"], 500);
//             }
    
//             return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'reservation' => $reservation], 200);
//         } catch (\Throwable $th) {
//             Log::error('Error al actualizar', ['error' => $th->getMessage()]);
//             return response()->json(['message' => "Error interno del servidor", $th]);
//         }
//     }
    
//     //Eliminar
//     public function delete($id)
//     {
//         $reservation = R::find($id);
    
//         if (!$user) {
//             return response()->json(['message' => 'No se ha encontrado el usuario'], 401);
//         }
    
//         try {
//             $user->delete();
//             return response()->json(['message' => 'Eliminado correctamente'], 200);
//         } catch (\Throwable $th) {
//             Log::error('Error al eliminar', ['error' => $th->getMessage()]);
//             return response()->json(['message' => 'No se pudo eliminar el usuario', $th], 500);
//         }
//     }
    
//     //traer los usuarios con roles admin
//     public function userByRolAdmin()
//     {
//         $users = User::with('rol')
//             ->whereHas('rol', function ($query) {
//                 $query->where('rol', 'user');
//             })
//             ->get();
    
//         if ($users->isEmpty()) {
//             return response()->json(['message' => 'No hay usuarios con rol admin'], 404);
//         }
    
//         return response()->json(['success' => true, 'usuarios' => $users], 200);
//     }
// }
