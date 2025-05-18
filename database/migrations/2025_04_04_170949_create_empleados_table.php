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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->string('nombre', 255); // Nombre del empleado
            $table->string('apellido', 255); // Apellido del empleado
            $table->string('cedula', 20)->unique(); // Cédula única
            $table->string('telefono', 15); // Teléfono
            $table->string('email', 255)->nullable(); // Email opcional
            $table->text('direccion')->nullable(); // Dirección opcional
            $table->string('cargo', 100)->nullable(); // Cargo del empleado
            $table->decimal('salario', 10, 2)->nullable(); // Salario con decimales
            $table->date('fecha_contratacion')->nullable(); // Fecha de contratación opcional
            $table->string('usuario', 50)->nullable(); // Nombre de usuario opcional
            $table->string('contraseña', 255)->nullable(); // Contraseña opcional
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
        Schema::dropIfExists('empleados');
    }
};

