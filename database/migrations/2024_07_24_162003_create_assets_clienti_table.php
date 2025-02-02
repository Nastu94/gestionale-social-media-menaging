<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsClientiTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('assets_clienti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cliente');
            $table->string('nome_assets');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();

            $table->foreign('id_cliente')->references('id')->on('clienti')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('assets_clienti');
    }
}

