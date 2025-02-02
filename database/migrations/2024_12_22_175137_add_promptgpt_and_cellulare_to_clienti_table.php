<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clienti', function (Blueprint $table) {
            // Aggiungi campi per GPT e cellulare
            $table->string('cellulare')->nullable()->after('sito_web');
            $table->text('promptGPT')->nullable()->after('cellulare');
        });
    }

    public function down()
    {
        Schema::table('clienti', function (Blueprint $table) {
            // Rimuovi i campi aggiunti
            $table->dropColumn(['cellulare', 'promptGPT']);
        });
    }
};
