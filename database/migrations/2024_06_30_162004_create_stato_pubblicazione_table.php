<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatoPubblicazioneTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stato_pubblicazione', function (Blueprint $table) {
            $table->id();
            $table->string('nome_stato');
        });        

        // Inserisci gli stati
        DB::table('stato_pubblicazione')->insert([
            ['nome_stato' => 'Bozza'],
            ['nome_stato' => 'In Lavorazione'],
            ['nome_stato' => 'In Valutazione'],
            ['nome_stato' => 'Approvata'],
            ['nome_stato' => 'Pianificata'],
            ['nome_stato' => 'Pubblicata'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('stato_pubblicazione');
    }
}

