<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class flightSeats extends Model
{
    //
    protected $table = 'flight_seats';

    protected $fillable = [
        'flight_id',
        'seat_id',
        'status',
        'hold_expires_at',
    ];

    protected $casts = [
        'hold_expires_at' => 'datetime',
    ];

     public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    //  ver qué reservas tienen este flight_seat
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_seat')
            ->withTimestamps();
    }

    public function scopeNotExpired(Builder $q): Builder
    {
        return $q->where(function ($w) {
            $w->whereNull('hold_expires_at')->orWhere('hold_expires_at', '>', now());
        });
    }
}
