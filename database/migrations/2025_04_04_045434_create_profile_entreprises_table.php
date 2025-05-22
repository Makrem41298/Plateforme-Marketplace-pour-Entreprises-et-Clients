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
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('postal_code');
            $table->string('phone');
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->text('description');
            $table->string('sector');
            $table->enum('company_type', ['LLC', 'SA', 'SARL', 'SNC', 'EI', 'Other']);
            $table->string('linkedin_url')->nullable();
            $table->integer('employees_count');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entreprise_profiles');
    }
};
