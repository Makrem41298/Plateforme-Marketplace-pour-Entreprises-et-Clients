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
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('montant_propose');
            $table->integer('delai');
            $table->text('description');
            $table->foreignId('projet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entreprise_id')->constrained()->cascadeOnDelete();
            $table->enum('statut', [
                'en_attente',
                'acceptee',
                'rejetee',
            ])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
