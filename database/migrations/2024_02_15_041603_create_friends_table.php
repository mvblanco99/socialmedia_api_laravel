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
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender');
            $table->unsignedBigInteger('recipient');
            $table->timestamps();

            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
