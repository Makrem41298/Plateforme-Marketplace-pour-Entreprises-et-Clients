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
        Schema::create('retraits', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique()
                ->comment('Référence unique du retrait');

            $table->foreignId('entreprise_id')
                ->constrained('users')
                ->comment('Entreprise effectuant le retrait');



            $table->decimal('montant', 12, 2)
                ->comment('Montant demandé a');

            $table->enum('statut', [
                'demande_initiee',
                'en_transit',
                'complete',
                'rejete',
            ])->default('demande_initiee');
            $table->string('info_compte_');
            $table->enum('methode', [
                'virement_bancaire',
                'paypal',
            ])->default('virement_bancaire');

            $table->text('notes_administratives')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retraits');
    }
};
