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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->string('first_surname');
            $table->string('second_surname');
            $table->string('names');
            $table->date('date_birth');
            $table->enum('gender', ["Man","Woman","Other"]);
            $table->enum('type_document', ["CC","TI","CE","Pasaporte"]);
            $table->string('document');
            $table->boolean('condicien_infante');
            $table->string('phone');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
