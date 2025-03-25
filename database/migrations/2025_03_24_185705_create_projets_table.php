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
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text("description");
            $table->decimal('budget',10,2)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status',['publie','Termine','en_cours'])->default('publie');
            $table->enum('type', [
                'developpement_web',
                'developpement_mobile',
                'design_graphique',
                'marketing_digital',
                'redaction_de_contenu',
                'conseil_en_affaires',
                'intelligence_artificielle',
                'autre'
            ]);
            $table->integer('Delai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
