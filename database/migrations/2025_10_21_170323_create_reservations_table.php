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

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->decimal('worth',10,2);
            $table->enum('status', ['pending','paid','canceled'])->default('pending');
            $table->unsignedInteger('number_of_positions');
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->foreignId('passenger_id')->constrained('passengers')->onDelete('cascade');
            $table->foreignId('payer_id')->constrained('payers')->onDelete('cascade');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
