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

        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('hour');
            $table->integer('ability');
            $table->decimal('price');
            $table->foreignId('destination_id')->constrained('destinations')->onDelete('cascade');
            $table->foreignId('origin_id')->constrained('origins')->onDelete('cascade');
            $table->foreignId('airplane_id')->constrained('airplanes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
