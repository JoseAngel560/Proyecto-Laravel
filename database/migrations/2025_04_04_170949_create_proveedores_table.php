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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 255); // Nombre del proveedor
            $table->string('razon_social', 255)->nullable(); // Razón social, opcional
            $table->string('contacto', 255)->nullable(); // Nombre del contacto, opcional
            $table->string('telefono', 15); // Teléfono del proveedor
            $table->string('email', 255)->nullable(); // Email del proveedor, opcional
            $table->text('direccion')->nullable(); // Dirección del proveedor, opcional
            $table->string('ruc', 20)->nullable(); // Número de RUC (Registro Único), opcional
            $table->date('fecha_registro')->nullable(); // Fecha de registro, opcional
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
        Schema::dropIfExists('proveedores');
    }
};
