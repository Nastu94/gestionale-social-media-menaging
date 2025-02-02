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
        Schema::create('media_in_pubblicazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_media');
            $table->unsignedBigInteger('id_pubblicazione');
            $table->timestamps();

            $table->foreign('id_media')->references('id')->on('media_pubblicazioni')->onDelete('cascade');
            $table->foreign('id_pubblicazione')->references('id')->on('pubblicazioni')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_in_pubblicazioni');
    }
};
