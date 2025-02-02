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
        Schema::table('pubblicazioni', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cliente')->nullable()->after('id');
            $table->foreign('id_cliente')->references('id')->on('clienti')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pubblicazioni', function (Blueprint $table) {
            $table->dropColumn('id_cliente');
        });
    }
};
