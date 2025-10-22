<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
