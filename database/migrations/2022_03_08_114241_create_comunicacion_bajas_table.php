<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComunicacionBajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comunicacion_bajas', function (Blueprint $table) {
            $table->id();
            $table->string('correlativo', 5)->nullable();
            $table->string('serie_doc', 4);
            $table->string('correlativo_doc', 8);
            $table->string('ticket', 13)->nullable();
            $table->string('descripcion', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comunicacion_bajas');
    }
}
