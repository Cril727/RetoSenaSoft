<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Generar PDF de tickets para una referencia de pago
     */
    public function generateTicketsPDF($referenceCode)
    {
        try {
            // Buscar todas las reservas con este código de referencia
            $reservations = Reservation::where('code', 'LIKE', $referenceCode . '%')
                ->with(['flight.origin', 'flight.destination', 'flight.airplane', 'passenger', 'payer', 'flightSeats.seat'])
                ->get();

            if ($reservations->isEmpty()) {
                return response()->json(['message' => 'No se encontraron reservas'], 404);
            }

            // Preparar datos para el PDF
            $ticketsData = $reservations->map(function ($reservation) {
                $flightSeat = $reservation->flightSeats->first();
                
                return [
                    'reservation_code' => $reservation->code,
                    'passenger_name' => $reservation->passenger->full_name,
                    'passenger_document' => $reservation->passenger->document_type . ' ' . $reservation->passenger->document_number,
                    'payer_name' => $reservation->payer->full_name,
                    'payer_email' => $reservation->payer->email,
                    'origin' => $reservation->flight->origin->city,
                    'destination' => $reservation->flight->destination->city,
                    'departure_date' => $reservation->flight->departure_at->format('d/m/Y'),
                    'departure_time' => $reservation->flight->departure_at->format('H:i'),
                    'seat_code' => $flightSeat ? $flightSeat->seat->code : 'N/A',
                    'seat_class' => $flightSeat ? $flightSeat->seat->class : 'N/A',
                    'price' => number_format($reservation->worth, 0, ',', '.'),
                    'status' => $reservation->status,
                    'airplane' => $reservation->flight->airplane->model ?? 'N/A',
                    'flight_id' => $reservation->flight->id
                ];
            });

            // Generar PDF
            $pdf = PDF::loadView('tickets.boarding-pass', [
                'tickets' => $ticketsData,
                'total_amount' => $reservations->sum('worth'),
                'reference_code' => $referenceCode,
                'issue_date' => now()->format('d/m/Y H:i')
            ]);

            // Configurar PDF
            $pdf->setPaper('a4', 'portrait');

            // Descargar PDF
            return $pdf->download("tickets-{$referenceCode}.pdf");

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de tickets', [
                'error' => $e->getMessage(),
                'reference' => $referenceCode
            ]);
            
            return response()->json([
                'message' => 'Error al generar el PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver tickets en el navegador
     */
    public function viewTickets($referenceCode)
    {
        try {
            $reservations = Reservation::where('code', 'LIKE', $referenceCode . '%')
                ->with(['flight.origin', 'flight.destination', 'flight.airplane', 'passenger', 'payer', 'flightSeats.seat'])
                ->get();

            if ($reservations->isEmpty()) {
                return response()->json(['message' => 'No se encontraron reservas'], 404);
            }

            $ticketsData = $reservations->map(function ($reservation) {
                $flightSeat = $reservation->flightSeats->first();
                
                return [
                    'reservation_code' => $reservation->code,
                    'passenger_name' => $reservation->passenger->full_name,
                    'passenger_document' => $reservation->passenger->document_type . ' ' . $reservation->passenger->document_number,
                    'payer_name' => $reservation->payer->full_name,
                    'payer_email' => $reservation->payer->email,
                    'origin' => $reservation->flight->origin->city,
                    'destination' => $reservation->flight->destination->city,
                    'departure_date' => $reservation->flight->departure_at->format('d/m/Y'),
                    'departure_time' => $reservation->flight->departure_at->format('H:i'),
                    'seat_code' => $flightSeat ? $flightSeat->seat->code : 'N/A',
                    'seat_class' => $flightSeat ? $flightSeat->seat->class : 'N/A',
                    'price' => number_format($reservation->worth, 0, ',', '.'),
                    'status' => $reservation->status,
                    'airplane' => $reservation->flight->airplane->model ?? 'N/A',
                    'flight_id' => $reservation->flight->id
                ];
            });

            $pdf = PDF::loadView('tickets.boarding-pass', [
                'tickets' => $ticketsData,
                'total_amount' => $reservations->sum('worth'),
                'reference_code' => $referenceCode,
                'issue_date' => now()->format('d/m/Y H:i')
            ]);

            return $pdf->stream("tickets-{$referenceCode}.pdf");

        } catch (\Exception $e) {
            Log::error('Error al ver tickets', [
                'error' => $e->getMessage(),
                'reference' => $referenceCode
            ]);
            
            return response()->json([
                'message' => 'Error al ver los tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}