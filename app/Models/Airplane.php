<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Airplane extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        "code",
        'number_passengers',
    ];

    /**
     * Relación con asientos
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Boot method para crear asientos automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando se crea un avión, generar asientos automáticamente
        static::created(function ($airplane) {
            $airplane->generateSeats();
        });
    }

    /**
     * Generar asientos basados en la capacidad del avión
     */
    public function generateSeats()
    {
        // Configuración estándar: 6 asientos por fila (A-F)
        $seatsPerRow = 6;
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
        
        // Calcular número de filas necesarias
        $totalRows = ceil($this->number_passengers / $seatsPerRow);
        
        $seatsCreated = 0;
        
        for ($row = 1; $row <= $totalRows && $seatsCreated < $this->number_passengers; $row++) {
            foreach ($columns as $col) {
                if ($seatsCreated >= $this->number_passengers) {
                    break;
                }
                
                Seat::create([
                    'code' => $row . $col,
                    'class' => in_array($col, ['A', 'F']) ? 'window' : 'aisle',
                    'airplane_id' => $this->id
                ]);
                
                $seatsCreated++;
            }
        }
        
        Log::info("Asientos generados automáticamente para avión {$this->model}: {$seatsCreated} asientos");
        
        return $seatsCreated;
    }
}
