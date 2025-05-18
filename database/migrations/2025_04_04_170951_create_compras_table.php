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
        Schema::create('compras', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_proveedor'); // Relación con proveedores
            $table->unsignedBigInteger('id_empleado'); // Relación con empleados
            $table->dateTime('fecha_compra'); // Fecha y hora de la compra
            $table->decimal('total', 10, 2); // Total de la compra
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->foreign('id_proveedor')->references('id')->on('proveedores')->onDelete('cascade'); // Relación con proveedores
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
        Schema::dropIfExists('compras');
    }
};
