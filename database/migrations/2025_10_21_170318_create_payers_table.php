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
        Schema::create('payers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->enum('type_document', ["CC","CE","Pasaporte"]);
            $table->string('document');
            $table->string('email');
            $table->string('phone');
            $table->enum('payment_method', ["credit card","debit card","PSE"]);
            $table->string("number_card");
            $table->string("cvv");  
            //yy-mm-dd
            $table->date("expiration_date");
            $table->enum('pse_method', ["Nequi","Daviplata","Bancolombia"])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payers');
    }
};
