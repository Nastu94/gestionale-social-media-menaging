<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatPubblicazioniTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_pubblicazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pubblicazione');
            $table->string('utente');
            $table->text('commento');
            $table->datetime('data_testo');

            $table->foreign('id_pubblicazione')->references('id')->on('pubblicazioni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_pubblicazioni');
    }
};
