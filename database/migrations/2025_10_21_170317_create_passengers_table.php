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
            $table->string('full_name');
            $table->date('date_birth')->nullable();
            $table->enum('gender', ["Man","Woman","Other"])->nullable();
            $table->enum('type_document', ["CC","TI","CE","Pasaporte"])->nullable();
            $table->string('document')->nullable();
            $table->boolean('condicien_infante')->nullable();
            $table->string('phone')->nullable();
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
