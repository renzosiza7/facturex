<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idcliente');
            $table->unsignedBigInteger('idusuario');
            $table->string('tipo_comprobante', 20);
            $table->string('serie_comprobante', 7)->nullable();
            $table->string('num_comprobante', 10);
            $table->date('fecha_hora');
            $table->decimal('impuesto', 4, 2);
            $table->decimal('total', 11, 2);
            $table->string('estado', 20);
            $table->timestamps();

            $table->foreign('idcliente')->references('id')->on('clientes');
            $table->foreign('idusuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
}
