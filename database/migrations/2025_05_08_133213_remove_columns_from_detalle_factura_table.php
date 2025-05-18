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
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_producto',
                'marca_producto',
                'modelo_producto',
                'color_producto'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->string('nombre_producto')->nullable();
            $table->string('marca_producto')->nullable();
            $table->string('modelo_producto')->nullable();
            $table->string('color_producto')->nullable();
        });
    }
};