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
        Schema::create('litiges', function (Blueprint $table) {
            $table->id();




            $table->foreignId('contrat_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->string('titre');
            $table->text('description');
            $table->enum('statut', [
                'ouvert',
                'en_investigation',
                'resolu',
                'ferme'
            ])->default('ouvert');

            $table->enum('type', [
                'paiement',
                'livraison',
                'qualite',
                'delai',
                'autre'
            ])->default('autre');
            //////
            $table->text('decision')->nullable()->comment('Décision finale');
            $table->enum('resolution_type', [
                'remboursement_partiel',
                'remboursement_total',
                'reparation',
                'compensation'
            ])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('litiges');
    }
};
