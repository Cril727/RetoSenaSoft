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

        Schema::create('flights', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->time('hour');
            $table->integer('ability');
            $table->decimal('price');
            $table->integer('destination_id');
            $table->foreign('destination_id')->references('city')->on('origins');
            $table->integer('origin_id');
            $table->foreign('origin_id')->references('id')->on('destinations');
            $table->integer('avion_id');
            $table->foreign('avion_id')->references('id')->on('airplanes');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
