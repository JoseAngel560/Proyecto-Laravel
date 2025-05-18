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
         Schema::create('movimientos_inventario', function (Blueprint $table) {
             $table->id(); // Clave primaria
             $table->unsignedBigInteger('id_producto'); // Relación con productos
             $table->unsignedBigInteger('id_origen')->nullable(); // ID opcional del origen
             $table->enum('tipo', ['entrada', 'salida']); // Tipo de movimiento: entrada o salida
             $table->integer('cantidad'); // Cantidad del movimiento
             $table->dateTime('fecha'); // Fecha y hora del movimiento
             $table->text('descripcion')->nullable(); // Descripción opcional del movimiento
             $table->foreign('id_producto')->references('id')->on('productos')->onDelete('cascade'); // Relación con productos
             // Relación con la tabla temporalidades
            $table->unsignedBigInteger('id_temporalidad')->nullable();
            $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
             $table->timestamps(); // Columnas created_at y updated_at
         });
     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
