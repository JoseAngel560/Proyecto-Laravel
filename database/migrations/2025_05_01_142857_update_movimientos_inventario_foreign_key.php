<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            // Eliminar la clave foránea existente
            $table->dropForeign(['id_producto']);
            // Hacer id_producto nullable
            $table->unsignedBigInteger('id_producto')->nullable()->change();
            // Añadir nueva clave foránea con ON DELETE SET NULL
            $table->foreign('id_producto')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            // Revertir al comportamiento anterior (cascade)
            $table->dropForeign(['id_producto']);
            $table->unsignedBigInteger('id_producto')->nullable(false)->change();
            $table->foreign('id_producto')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('cascade');
        });
    }
};