<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePubblicazioniTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pubblicazioni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stato_id')->default(1);
            $table->text('testo')->nullable();
            $table->datetime('data_pubblicazione');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('stato_id')->references('id')->on('stato_pubblicazione');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pubblicazioni');
    }
}

