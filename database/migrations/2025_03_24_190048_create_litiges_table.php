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
            $table->string('reference')->unique();
            $table->integer('litigeable_id');
            $table->string('litigeable_type');
            $table->string('reference_contrat');
            $table->foreign('reference_contrat')->references('reference')->on('contrats')->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->enum('statut', [
                'ouvert',
                'en_investigation',
                'resolu',
                'ferme'
            ])->default('ouvert');

            $table->enum('sujet', [
                'paiement',
                'livraison',
                'qualite',
                'delai',
                'autre'
            ])->default('autre');
            //////
            $table->text('decision')->nullable()->comment('Décision finale');
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
