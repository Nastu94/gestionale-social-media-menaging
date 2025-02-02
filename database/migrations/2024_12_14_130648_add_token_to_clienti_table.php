<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->string('token', 64)->unique()->nullable()->after('firma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
