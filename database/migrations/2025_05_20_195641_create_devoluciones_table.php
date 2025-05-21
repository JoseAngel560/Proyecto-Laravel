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
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_factura'); // Relación con facturas
            $table->unsignedBigInteger('id_empleado'); // Relación con empleados
            $table->dateTime('fecha_devolucion'); // Fecha y hora de la devolución
            $table->text('motivo_devolucion')->nullable(); // Motivo de la devolución
            $table->decimal('monto_total_devuelto', 10, 2); // Monto total devuelto en esta devolución
            $table->unsignedBigInteger('id_temporalidad'); // Relación con temporalidades, obligatorio
            $table->foreign('id_factura')->references('id')->on('facturas')->onDelete('cascade');
            $table->foreign('id_empleado')->references('id')->on('empleados')->onDelete('cascade');
            $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
            $table->timestamps(); // created_at y updated_at
            $table->index('id_factura'); // Índice para consultas rápidas
            $table->index('fecha_devolucion'); // Índice para reportes por fecha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};