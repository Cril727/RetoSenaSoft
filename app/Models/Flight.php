<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flight extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date',
        'hour',
        'ability',
        'price',
        'destination_id',
        'origin_id',
        'avion_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'price' => 'decimal',
        ];
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class, 'destination_id', 'city');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function avion(): BelongsTo
    {
        return $this->belongsTo(Airplane::class);
    }
}
