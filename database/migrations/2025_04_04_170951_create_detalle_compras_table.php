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
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_compra'); // Relación con compras
            $table->unsignedBigInteger('id_producto'); // Relación con productos
            $table->integer('cantidad'); // Cantidad de producto comprado
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del producto
            $table->decimal('subtotal', 10, 2); // Subtotal de la compra
            $table->foreign('id_compra')->references('id')->on('compras')->onDelete('cascade'); // Relación con compras
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
        Schema::dropIfExists('detalle_compras');
    }
};
