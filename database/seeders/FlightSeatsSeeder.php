<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airplane;
use App\Models\Seat;
use App\Models\Origin;
use App\Models\Destination;
use App\Models\Flight;
use App\Models\flightSeats;

class FlightSeatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando configuración de asientos...');

        // Configuración estándar de asientos (6 filas x 6 columnas)
        $rows = 6;
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];

        // Obtener TODOS los aviones existentes
        $airplanes = Airplane::all();

        if ($airplanes->isEmpty()) {
            $this->command->warn('⚠️  No hay aviones en la base de datos. Creando uno...');
            $airplanes = collect([
                Airplane::create([
                    'model' => 'Boeing 737',
                    'code' => 'B737',
                    'capacity' => 180
                ])
            ]);
        }

        $this->command->info("✈️  Configurando asientos para {$airplanes->count()} avión(es)...");

        // Crear asientos para CADA avión
        foreach ($airplanes as $airplane) {
            $this->command->info("   Procesando: {$airplane->model} (ID: {$airplane->id})");
            
            // Verificar si ya tiene asientos
            $existingSeats = Seat::where('airplane_id', $airplane->id)->count();
            
            if ($existingSeats > 0) {
                $this->command->info("   ✓ Ya tiene {$existingSeats} asientos configurados");
                continue;
            }

            // Crear asientos para este avión
            foreach (range(1, $rows) as $row) {
                foreach ($columns as $col) {
                    Seat::create([
                        'code' => $row . $col,
                        'class' => in_array($col, ['A', 'F']) ? 'window' : 'aisle',
                        'airplane_id' => $airplane->id
                    ]);
                }
            }
            
            $this->command->info("   ✓ Creados " . ($rows * count($columns)) . " asientos");
        }

        // Crear orígenes y destinos si no existen
        $cities = ['BOGOTA', 'MEDELLIN', 'CARTAGENA', 'CALI', 'BARRANQUILLA'];
        
        foreach ($cities as $city) {
            Origin::firstOrCreate(['city' => $city]);
            Destination::firstOrCreate(['city' => $city]);
        }

        $this->command->info('🌍 Ciudades configuradas');

        // Obtener TODOS los vuelos existentes
        $flights = Flight::with('airplane')->get();

        if ($flights->isEmpty()) {
            $this->command->warn('⚠️  No hay vuelos. Creando vuelos de ejemplo...');
            
            $bogota = Origin::where('city', 'BOGOTA')->first();
            $medellin = Destination::where('city', 'MEDELLIN')->first();
            $cartagena = Destination::where('city', 'CARTAGENA')->first();
            $firstAirplane = $airplanes->first();

            $flightsData = [
                [
                    'departure_at' => now()->addDays(5)->setTime(10, 0),
                    'price' => 250000,
                    'origin_id' => $bogota->id,
                    'destination_id' => $medellin->id,
                    'airplane_id' => $firstAirplane->id
                ],
                [
                    'departure_at' => now()->addDays(5)->setTime(15, 0),
                    'price' => 280000,
                    'origin_id' => $bogota->id,
                    'destination_id' => $cartagena->id,
                    'airplane_id' => $firstAirplane->id
                ],
            ];

            foreach ($flightsData as $flightData) {
                $flights->push(Flight::create($flightData));
            }
        }

        $this->command->info("🎫 Asignando asientos a {$flights->count()} vuelo(s)...");

        // Asignar asientos a CADA vuelo
        foreach ($flights as $flight) {
            $this->command->info("   Vuelo #{$flight->id}: {$flight->airplane->model}");
            
            // Verificar si ya tiene asientos asignados
            $existingFlightSeats = flightSeats::where('flight_id', $flight->id)->count();
            
            if ($existingFlightSeats > 0) {
                $this->command->info("   ✓ Ya tiene {$existingFlightSeats} asientos asignados");
                continue;
            }

            // Obtener todos los asientos del avión de este vuelo
            $seats = Seat::where('airplane_id', $flight->airplane_id)->get();

            if ($seats->isEmpty()) {
                $this->command->error("   ✗ El avión no tiene asientos configurados!");
                continue;
            }

            // Crear flight_seats para cada asiento
            foreach ($seats as $seat) {
                flightSeats::create([
                    'flight_id' => $flight->id,
                    'seat_id' => $seat->id,
                    'status' => 'available',
                    'hold_expires_at' => null
                ]);
            }

            // Marcar algunos asientos como vendidos (simulación)
            $seatsToSell = min(5, $seats->count());
            $soldSeats = $seats->random($seatsToSell);
            
            foreach ($soldSeats as $seat) {
                flightSeats::where('flight_id', $flight->id)
                    ->where('seat_id', $seat->id)
                    ->update(['status' => 'sold']);
            }

            $this->command->info("   ✓ Asignados {$seats->count()} asientos ({$seatsToSell} vendidos)");
        }

        // Resumen final
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('✅ CONFIGURACIÓN COMPLETADA');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('✈️  Aviones: ' . Airplane::count());
        $this->command->info('💺 Asientos totales: ' . Seat::count());
        $this->command->info('🎫 Vuelos: ' . Flight::count());
        $this->command->info('🔗 Asientos asignados: ' . flightSeats::count());
        $this->command->info('🟢 Disponibles: ' . flightSeats::where('status', 'available')->count());
        $this->command->info('🔴 Vendidos: ' . flightSeats::where('status', 'sold')->count());
        $this->command->info('═══════════════════════════════════════');
    }
}