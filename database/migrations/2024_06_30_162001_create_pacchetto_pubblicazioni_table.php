<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePacchettoPubblicazioniTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pacchetto_pubblicazioni', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('numero_pubblicazioni');
            $table->decimal('costo', 8, 2);
        });

        // Inserisci i pacchetti
        DB::table('pacchetto_pubblicazioni')->insert([
            ['nome' => 'Special X4', 'numero_pubblicazioni' => 4, 'costo' => 200.00],
            ['nome' => 'Special X8', 'numero_pubblicazioni' => 8, 'costo' => 350.00],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pacchetto_pubblicazioni');
    }
}

