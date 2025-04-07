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
        Schema::create('messages', function (Blueprint $table) {
            // Polymorphic sender relationship
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type');

            // Polymorphic receiver relationship
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type');

            $table->string('subject')->nullable();
            $table->text('content');

            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
