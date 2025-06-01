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
        Schema::create('transcations', function (Blueprint $table) {
            $table->id();
            $table->date('date_effctue');
            $table->enum('statut', [
                'en_attente',
                'effctue',
            ]);
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->enum('tranch',[1,2])->default(1);
            $table->enum('methode_paiment',[
                'carte_credit',
                'paypal'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcations');
    }
};
