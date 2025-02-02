<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirmaToClientiTable extends Migration
{
    /**
     * Esegui la migrazione.
     */
    public function up()
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->text('firma')->nullable()->after('logo_cliente'); // Aggiunge la colonna firma
        });
    }

    /**
     * Annulla la migrazione.
     */
    public function down()
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->dropColumn('firma'); // Rimuove la colonna firma
        });
    }
}

