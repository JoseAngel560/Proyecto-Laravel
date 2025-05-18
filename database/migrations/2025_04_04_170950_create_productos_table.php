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
        Schema::create('productos', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 255); // Nombre del producto
            $table->text('descripcion')->nullable(); // Descripción del producto, opcional
            $table->string('marca', 100)->nullable(); // Marca del producto, opcional
            $table->string('modelo', 100)->nullable(); // Modelo del producto, opcional
            $table->string('color', 50)->nullable(); // Color del producto, opcional
            $table->decimal('precio_compra', 10, 2); // Precio de compra
            $table->decimal('precio_venta', 10, 2); // Precio de venta
            $table->integer('stock')->default(0); // Cantidad en stock, por defecto 0
            $table->unsignedBigInteger('id_categoria')->nullable(); // ID de la categoría
            $table->foreign('id_categoria')->references('id')->on('categorias')->onDelete('set null'); // Relación con categorías
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
        Schema::dropIfExists('productos');
    }
};
