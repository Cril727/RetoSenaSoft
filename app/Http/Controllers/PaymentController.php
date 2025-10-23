<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Reservation;
use App\Models\Flight;
use App\Models\flightSeats;

class PaymentController extends Controller
{
    /**
     * Generar firma para PayU
     */
    private function generateSignature($referenceCode, $amount, $currency)
    {
        $apiKey = env('PAYU_API_KEY');
        $merchantId = env('PAYU_MERCHANT_ID');
        
        $signature = md5("{$apiKey}~{$merchantId}~{$referenceCode}~{$amount}~{$currency}");
        
        return $signature;
    }

    /**
     * Crear orden de pago y generar datos para PayU
     */
    public function createPaymentOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'flight_id' => 'required|exists:flights,id',
                'seats' => 'required|array|min:1|max:5',
                'seats.*.id' => 'required|exists:flight_seats,id',
                'payer' => 'required|array',
                'payer.full_name' => 'required|string',
                'payer.email' => 'required|email',
                'payer.phone' => 'required|string',
                'payer.document_type' => 'required|string',
                'payer.document_number' => 'required|string',
                'passengers' => 'required|array|min:1',
                'passengers.*.full_name' => 'required|string',
                'passengers.*.document_type' => 'required|string',
                'passengers.*.document_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Obtener información del vuelo
            $flight = Flight::with(['origin', 'destination'])->findOrFail($request->flight_id);
            
            // Verificar que los asientos estén disponibles
            $seatIds = collect($request->seats)->pluck('id');
            $flightSeats = flightSeats::whereIn('id', $seatIds)
                ->where('flight_id', $request->flight_id)
                ->where('status', 'available')
                ->get();

            if ($flightSeats->count() !== count($request->seats)) {
                return response()->json([
                    'message' => 'Algunos asientos ya no están disponibles'
                ], 400);
            }

            // Calcular el monto total
            $amount = $flight->price * count($request->seats);
            
            // Generar código de referencia único
            $referenceCode = 'REF-' . time() . '-' . rand(1000, 9999);
            
            // Generar firma
            $currency = 'COP';
            $signature = $this->generateSignature($referenceCode, $amount, $currency);

            // Preparar datos de la orden
            $orderData = [
                'flight_id' => $request->flight_id,
                'seats' => $seatIds->toArray(),
                'payer' => $request->payer,
                'passengers' => $request->passengers
            ];
            
            // Guardar datos de la orden en caché para recuperarlos después
            Cache::put("payment_order_{$referenceCode}", $orderData, now()->addHours(2));

            // Preparar datos para PayU (sin extra1 para evitar límite de 255 caracteres)
            $payuData = [
                'merchantId' => env('PAYU_MERCHANT_ID'),
                'accountId' => env('PAYU_ACCOUNT_ID'),
                'description' => "Vuelo {$flight->origin->city} - {$flight->destination->city}",
                'referenceCode' => $referenceCode,
                'amount' => $amount,
                'tax' => 0,
                'taxReturnBase' => 0,
                'currency' => $currency,
                'signature' => $signature,
                'test' => env('PAYU_TEST_MODE', true) ? 1 : 0,
                'buyerEmail' => $request->payer['email'],
                'buyerFullName' => $request->payer['full_name'],
                'telephone' => $request->payer['phone'],
                'responseUrl' => env('PAYU_RESPONSE_URL'),
                'confirmationUrl' => env('PAYU_CONFIRMATION_URL'),
                'extra1' => $referenceCode // Solo el código de referencia
            ];

            // Crear las reservas inmediatamente como "pending"
            $reservations = $this->createReservation($orderData, $referenceCode, $amount, 'pending');
            
            // Marcar asientos como "held" temporalmente (15 minutos)
            $holdExpiresAt = now()->addMinutes(15);
            flightSeats::whereIn('id', $seatIds)->update([
                'status' => 'held',
                'hold_expires_at' => $holdExpiresAt
            ]);

            Log::info('Orden de pago creada y reservas pendientes generadas', [
                'reference' => $referenceCode,
                'amount' => $amount,
                'seats' => $seatIds->toArray(),
                'reservations_count' => count($reservations)
            ]);

            return response()->json([
                'success' => true,
                'payuData' => $payuData,
                'payuUrl' => env('PAYU_PAYMENT_URL'),
                'referenceCode' => $referenceCode
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear orden de pago', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al procesar la orden de pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Página de respuesta de PayU (donde regresa el usuario)
     */
    public function paymentResponse(Request $request)
    {
        Log::info('Respuesta de PayU recibida', $request->all());

        $referenceCode = $request->input('referenceCode');
        $transactionState = $request->input('transactionState');
        
        // Estados de PayU:
        // 4 = Aprobada
        // 6 = Rechazada
        // 7 = Pendiente
        // 104 = Error

        return response()->json([
            'success' => true,
            'referenceCode' => $referenceCode,
            'transactionState' => $transactionState,
            'message' => $this->getTransactionMessage($transactionState)
        ]);
    }

    /**
     * Confirmación de PayU (webhook)
     */
    public function paymentConfirmation(Request $request)
    {
        try {
            Log::info('Confirmación de PayU recibida', $request->all());

            // Validar firma
            $signature = $request->input('sign');
            $referenceCode = $request->input('reference_sale');
            $amount = $request->input('value');
            $currency = $request->input('currency');
            $transactionState = $request->input('state_pol');

            $expectedSignature = $this->generateSignature($referenceCode, $amount, $currency);

            if ($signature !== $expectedSignature) {
                Log::error('Firma inválida en confirmación de PayU');
                return response('Invalid signature', 400);
            }

            // Recuperar datos de la orden desde caché
            $orderData = Cache::get("payment_order_{$referenceCode}");
            
            if (!$orderData) {
                Log::error('No se encontraron datos de la orden', ['reference' => $referenceCode]);
                return response('Order data not found', 404);
            }
            
            if ($transactionState == 4) { // Aprobada
                // Actualizar las reservas existentes a "confirmed"
                Reservation::where('code', 'LIKE', $referenceCode . '%')
                    ->update(['status' => 'confirmed']);
                
                // Marcar asientos como vendidos
                flightSeats::whereIn('id', $orderData['seats'])->update([
                    'status' => 'sold',
                    'hold_expires_at' => null
                ]);
                
                // Limpiar caché
                Cache::forget("payment_order_{$referenceCode}");
                
                Log::info('Pago confirmado y reservas actualizadas', ['reference' => $referenceCode]);
            } else {
                // Eliminar reservas pendientes y liberar asientos
                Reservation::where('code', 'LIKE', $referenceCode . '%')->delete();
                
                flightSeats::whereIn('id', $orderData['seats'])->update([
                    'status' => 'available',
                    'hold_expires_at' => null
                ]);
                
                // Limpiar caché
                Cache::forget("payment_order_{$referenceCode}");
                
                Log::info('Pago rechazado, reservas eliminadas y asientos liberados', ['reference' => $referenceCode]);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error en confirmación de PayU', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Error', 500);
        }
    }

    /**
     * Crear reservas (una por cada pasajero)
     */
    private function createReservation($data, $referenceCode, $amount, $status = 'confirmed')
    {
        // Crear o encontrar el pagador
        $payer = \App\Models\Payer::firstOrCreate(
            ['email' => $data['payer']['email']],
            [
                'full_name' => $data['payer']['full_name'],
                'phone' => $data['payer']['phone'],
                'type_document' => $data['payer']['document_type'],
                'document' => $data['payer']['document_number'],
                'payment_method' => 'credit card', // Por defecto
                'number_card' => '0000', // Placeholder
                'cvv' => '000', // Placeholder
                'expiration_date' => now()->addYear()->format('Y-m-d') // Placeholder
            ]
        );

        $reservations = [];
        $pricePerSeat = $amount / count($data['passengers']);

        // Crear una reserva por cada pasajero
        foreach ($data['passengers'] as $index => $passengerData) {
            // Crear o encontrar el pasajero
            $passenger = \App\Models\Passenger::firstOrCreate(
                ['document' => $passengerData['document_number']],
                [
                    'full_name' => $passengerData['full_name'],
                    'type_document' => $passengerData['document_type'],
                    'email' => $data['payer']['email'] // Usar el email del pagador
                ]
            );

            // Crear la reserva para este pasajero
            $reservation = Reservation::create([
                'code' => $referenceCode . '-' . ($index + 1),
                'worth' => $pricePerSeat,
                'status' => $status,
                'number_of_positions' => 1,
                'flight_id' => $data['flight_id'],
                'passenger_id' => $passenger->id,
                'payer_id' => $payer->id
            ]);

            // Asociar el asiento correspondiente con esta reserva
            if (isset($data['seats'][$index])) {
                $reservation->flightSeats()->attach([$data['seats'][$index]]);
            }

            $reservations[] = $reservation;
        }

        Log::info('Reservas creadas', [
            'reference' => $referenceCode,
            'total_reservations' => count($reservations)
        ]);

        return $reservations;
    }

    /**
     * Obtener mensaje según estado de transacción
     */
    private function getTransactionMessage($state)
    {
        $messages = [
            '4' => 'Transacción aprobada',
            '6' => 'Transacción rechazada',
            '7' => 'Transacción pendiente',
            '104' => 'Error en la transacción'
        ];

        return $messages[$state] ?? 'Estado desconocido';
    }
}