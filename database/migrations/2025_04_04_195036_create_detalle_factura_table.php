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
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_factura'); // Relación con facturas
            $table->unsignedBigInteger('id_producto'); // Relación con productos
            $table->string('nombre_producto', 255); // Nombre del producto en la factura
            $table->string('marca_producto', 100)->nullable(); // Marca del producto, opcional
            $table->string('modelo_producto', 100)->nullable(); // Modelo del producto, opcional
            $table->string('color_producto', 50)->nullable(); // Color del producto, opcional
            $table->integer('cantidad'); // Cantidad del producto en la factura
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del producto
            $table->decimal('iva', 10, 2)->nullable(); // IVA aplicado al producto
            $table->decimal('subtotal', 10, 2); // Subtotal calculado del producto
            $table->foreign('id_factura')->references('id')->on('facturas')->onDelete('cascade'); // Relación con facturas
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
        Schema::dropIfExists('detalle_factura');
    }
};
