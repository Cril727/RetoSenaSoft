<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Airplane;
use App\Models\Seat;
use App\Models\Flight;
use App\Models\flightSeats;
use Illuminate\Support\Facades\DB;

class RegenerateSeats extends Command
{
    protected $signature = 'seats:regenerate';
    protected $description = 'Regenera todos los asientos con el formato correcto';

    public function handle()
    {
        $this->info('🔄 Limpiando asientos antiguos...');
        
        // Desactivar temporalmente las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Eliminar reservation_seat primero
        DB::table('reservation_seat')->truncate();
        $this->info('✓ reservation_seat eliminados');
        
        // Eliminar todos los flight_seats
        flightSeats::truncate();
        $this->info('✓ flight_seats eliminados');
        
        // Eliminar todos los seats
        Seat::truncate();
        $this->info('✓ seats eliminados');
        
        // Reactivar las restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->info('');
        $this->info('🚀 Creando nuevos asientos...');
        
        // Configuración estándar de asientos (6 filas x 6 columnas)
        $rows = 6;
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        $airplanes = Airplane::all();
        
        if ($airplanes->isEmpty()) {
            $this->error('No hay aviones en la base de datos');
            return 1;
        }
        
        $this->info("✈️  Procesando {$airplanes->count()} avión(es)...");
        
        foreach ($airplanes as $airplane) {
            $this->info("   Avión: {$airplane->model} (ID: {$airplane->id})");
            
            // Crear asientos con formato correcto: 1A, 1B, 1C, etc.
            foreach (range(1, $rows) as $row) {
                foreach ($columns as $col) {
                    Seat::create([
                        'code' => $row . $col,  // Formato: 1A, 2B, 3C
                        'class' => in_array($col, ['A', 'F']) ? 'window' : 'aisle',
                        'airplane_id' => $airplane->id
                    ]);
                }
            }
            
            $this->info("   ✓ Creados " . ($rows * count($columns)) . " asientos");
        }
        
        $this->info('');
        $this->info('🎫 Asignando asientos a vuelos...');
        
        $flights = Flight::with('airplane')->get();
        
        foreach ($flights as $flight) {
            $this->info("   Vuelo #{$flight->id}");
            
            $seats = Seat::where('airplane_id', $flight->airplane_id)->get();
            
            if ($seats->isEmpty()) {
                $this->error("   ✗ El avión no tiene asientos");
                continue;
            }
            
            foreach ($seats as $seat) {
                flightSeats::create([
                    'flight_id' => $flight->id,
                    'seat_id' => $seat->id,
                    'status' => 'available',
                    'hold_expires_at' => null
                ]);
            }
            
            $this->info("   ✓ Asignados {$seats->count()} asientos");
        }
        
        $this->info('');
        $this->info('═══════════════════════════════════════');
        $this->info('✅ REGENERACIÓN COMPLETADA');
        $this->info('═══════════════════════════════════════');
        $this->info('✈️  Aviones: ' . Airplane::count());
        $this->info('💺 Asientos totales: ' . Seat::count());
        $this->info('🎫 Vuelos: ' . Flight::count());
        $this->info('🔗 Asientos asignados: ' . flightSeats::count());
        $this->info('═══════════════════════════════════════');
        
        return 0;
    }
}