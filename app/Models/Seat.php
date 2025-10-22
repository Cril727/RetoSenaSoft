<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    //
    protected $fillable = ['code', 'class','airplane_id'];

    
    public function airplane(): BelongsTo
    {
        return $this->belongsTo(Airplane::class);
    }

    public function flightSeats(): HasMany
    {
        return $this->hasMany(flightSeats::class);
    }
}
