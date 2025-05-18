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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_cliente'); // Relación con clientes
            $table->unsignedBigInteger('id_empleado'); // Relación con empleados
            $table->dateTime('fecha_factura'); // Fecha y hora de la factura
            $table->enum('metodo_pago', ['Efectivo', 'Tarjeta']); // Método de pago
            $table->decimal('total', 10, 2); // Total de la factura
            $table->decimal('totalcancelado', 10, 2); // Monto cancelado por el cliente
            $table->decimal('cambio', 10, 2); // Cambio dado al cliente
            $table->foreign('id_cliente')->references('id')->on('clientes')->onDelete('cascade'); // Relación con clientes
            $table->foreign('id_empleado')->references('id')->on('empleados')->onDelete('cascade'); // Relación con empleados
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
        Schema::dropIfExists('facturas');
    }
};
