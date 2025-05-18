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
        // Proveedores
        Schema::table('proveedores', function (Blueprint $table) {
            if (!Schema::hasColumn('proveedores', 'estado')) {
                $table->string('estado', 255)->default('activo')->nullable(false)->after('fecha_registro');
            }
        });

        // Productos
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'estado')) {
                $table->string('estado', 20)->default('activo')->nullable(false);
            }
        });

        // Clientes
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'estado')) {
                $table->string('estado', 20)->default('activo')->nullable(false);
            }
        });

        // Empleados
        Schema::table('empleados', function (Blueprint $table) {
            if (!Schema::hasColumn('empleados', 'estado')) {
                $table->string('estado', 20)->default('activo')->nullable(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (Schema::hasColumn('proveedores', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};