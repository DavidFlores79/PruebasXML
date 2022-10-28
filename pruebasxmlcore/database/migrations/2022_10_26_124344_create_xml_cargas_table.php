<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXmlCargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xml_cargas', function (Blueprint $table) {
            $table->id();

            $table->string('documento',10);
            $table->string('referencia',20);
            $table->string('tipo_xml',4);
            $table->string('proveedor',10);
            $table->string('sociedad',4);
            $table->integer('ejercicio');
            $table->char('tipo_error')->nullable();
            $table->string('archivo',300)->nullable();
            $table->longText('mensaje')->nullable();
            $table->string('forma_pago',3)->nullable();
            $table->text('json_sap')->nullable();
            $table->boolean('resultado')->default(0);
            $table->mediumText('xml');
            
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
        Schema::dropIfExists('xml_cargas');
    }
}
