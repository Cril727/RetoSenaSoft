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
        Schema::disableForeignKeyConstraints();

        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->decimal('worth');
            $table->integer('number_of_positions');
            $table->integer('flight_id');
            $table->foreign('flight_id')->references('id')->on('flights');
            $table->integer('passenger_id');
            $table->foreign('passenger_id')->references('id')->on('passenger');
            $table->integer('payer_id');
            $table->foreign('payer_id')->references('id')->on('payer');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
