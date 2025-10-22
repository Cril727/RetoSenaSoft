<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flight_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained("flights")->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained("seats")->cascadeOnDelete();
            $table->enum('status', ['available', 'held', 'sold'])->default('available');
            $table->timestamp('hold_expires_at')->nullable(); // para separar temporalmente
            $table->unique(['flight_id', 'seat_id']); // clave fuerte contra doble venta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_seats');
    }
};
