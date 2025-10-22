<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Flight extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'departure_at',
        'price',
        'destination_id',
        'origin_id',
        'airplane_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public function airplane(): BelongsTo
    {
        return $this->belongsTo(Airplane::class, 'airplane_id');
    }

    public function flightSeats(): HasMany
    {
        return $this->hasMany(flightSeats::class);
    }

    /**
     * Boot method para asignar asientos automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando se crea un vuelo, asignar asientos automáticamente
        static::created(function ($flight) {
            $flight->assignSeats();
        });
    }

    /**
     * Asignar asientos del avión a este vuelo
     */
    public function assignSeats()
    {
        // Obtener todos los asientos del avión
        $seats = Seat::where('airplane_id', $this->airplane_id)->get();

        if ($seats->isEmpty()) {
            Log::warning("El avión ID {$this->airplane_id} no tiene asientos configurados");
            return 0;
        }

        $seatsAssigned = 0;

        // Crear flight_seats para cada asiento
        foreach ($seats as $seat) {
            flightSeats::create([
                'flight_id' => $this->id,
                'seat_id' => $seat->id,
                'status' => 'available',
                'hold_expires_at' => null
            ]);
            $seatsAssigned++;
        }

        Log::info("Asientos asignados automáticamente al vuelo #{$this->id}: {$seatsAssigned} asientos");

        return $seatsAssigned;
    }
}
