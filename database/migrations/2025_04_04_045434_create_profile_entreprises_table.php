<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_entreprise_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('entreprise_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade');
            $table->decimal('solde',8,2)->default(0);
            $table->string('address')->default(null)->nullable();
            $table->string('city')->default(null)->nullable();
            $table->string('country')->default(null)->nullable();
            $table->string('postal_code')->default(null)->nullable();
            $table->string('phone')->default(null)->nullable();
            $table->string('fax')->default(null)->nullable();
            $table->string('website')->default(null)->nullable();
            $table->text('description')->default(null)->nullable();
            $table->string('sector')->default(null)->nullable();
            $table->enum('company_type', ['LLC', 'SA', 'SARL', 'SNC', 'EI', 'Other'])->default(null)->nullable();
            $table->string('linkedin_url')->default(null)->nullable();
            $table->integer('employees_count')->default(null)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entreprise_profiles');
    }
};
