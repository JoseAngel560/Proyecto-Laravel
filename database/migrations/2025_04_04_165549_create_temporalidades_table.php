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
        Schema::create('temporalidades', function (Blueprint $table) {
            $table->id(); // Clave primaria
            $table->dateTime('fecha_completa')->default(now()); // Fecha completa con hora
            $table->string('dia_semana', 20); // Día de la semana (Ej: "Lunes")
            $table->integer('dia_mes'); // Día del mes
            $table->integer('semana_mes'); // Semana del mes
            $table->integer('dia_anio'); // Día del año
            $table->integer('semana_anio'); // Semana del año
            $table->integer('trimestre_anio'); // Trimestre del año
            $table->string('mes_anio', 20); // Mes del año (Ej: "Enero")
            $table->boolean('vispera_festivo')->default(false); // Víspera de festivo
            $table->integer('anio'); // Año
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporalidades');
    }
};