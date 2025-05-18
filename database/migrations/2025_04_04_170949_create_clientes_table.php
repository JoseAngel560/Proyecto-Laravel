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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 255); // Nombre del cliente
            $table->string('apellido', 255); // Apellido del cliente
            $table->string('telefono', 15)->nullable(); // Teléfono (opcional)
            $table->text('direccion')->nullable(); // Dirección (opcional)
            $table->timestamps(); // Columnas created_at y updated_at
            // Relación con la tabla temporalidades
            $table->unsignedBigInteger('id_temporalidad')->nullable();
            $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
