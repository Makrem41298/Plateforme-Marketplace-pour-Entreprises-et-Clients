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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('statut', [
                'en_attente',
                'signe',
                'expire',
                'rompu'
            ])->default('en_attente');

            $table->foreignId('offer_id')
                ->constrained('offres')
                ->onDelete('cascade');

            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->text('termes');

            $table->decimal('acompte', 10, 2)->nullable();
            $table->decimal('solde', 10, 2)->nullable();
            $table->timestamp('signe_le')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
