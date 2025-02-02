<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuoliTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ruoli', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
        });

        // Inserisci i ruoli
        DB::table('ruoli')->insert([
            ['nome' => 'Amministratore'],
            ['nome' => 'Dipendente'],
            ['nome' => 'Cliente'],
            ['nome' => 'Fotografo'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ruoli');
    }
}
