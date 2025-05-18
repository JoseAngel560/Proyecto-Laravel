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
        Schema::create('datos_tarjeta', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_factura'); // Relación con facturas
            $table->string('nombre_titular', 255); // Nombre del titular de la tarjeta
            $table->string('numero_tarjeta', 16); // Número de la tarjeta
            $table->date('fecha_expiracion'); // Fecha de expiración de la tarjeta
            $table->enum('tipo_tarjeta', ['Debito', 'Credito']);
            $table->foreign('id_factura')->references('id')->on('facturas')->onDelete('cascade'); // Relación con facturas
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
        Schema::dropIfExists('datos_tarjeta');
    }
};
