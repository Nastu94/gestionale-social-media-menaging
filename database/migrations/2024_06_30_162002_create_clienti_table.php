<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientiTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('clienti', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('logo_cliente')->nullable();
            $table->unsignedBigInteger('pacchetto_id');
            $table->unsignedBigInteger('id_utente_cliente');
            $table->string('sito_web')->nullable();
            $table->timestamps();

            $table->foreign('id_utente_cliente')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pacchetto_id')->references('id')->on('pacchetto_pubblicazioni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('clienti');
    }
}

