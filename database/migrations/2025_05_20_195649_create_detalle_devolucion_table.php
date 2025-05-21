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
        Schema::create('detalle_devolucion', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->unsignedBigInteger('id_devolucion'); // Relación con devoluciones
            $table->unsignedBigInteger('id_detalle_factura'); // Relación con detalle_factura
            $table->unsignedBigInteger('id_producto'); // Relación con productos
            $table->integer('cantidad_devuelta'); // Cantidad de productos devueltos
            $table->decimal('precio_unitario', 10, 2); // Precio unitario del producto (copiado de detalle_factura)
            $table->decimal('iva', 10, 2)->nullable(); // IVA aplicado al producto (copiado de detalle_factura)
            $table->decimal('subtotal_devuelto', 10, 2); // Subtotal devuelto (precio_unitario * cantidad_devuelta + iva)
            $table->unsignedBigInteger('id_temporalidad'); // Relación con temporalidades, obligatorio
            $table->foreign('id_devolucion')->references('id')->on('devoluciones')->onDelete('cascade');
            $table->foreign('id_detalle_factura')->references('id')->on('detalle_factura')->onDelete('cascade');
            $table->foreign('id_producto')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
            $table->timestamps(); // created_at y updated_at
            $table->index(['id_devolucion', 'id_detalle_factura']); // Índice compuesto para consultas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_devolucion');
    }
};