<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_factura', 'id_temporalidad')) {
                $table->unsignedBigInteger('id_temporalidad')->nullable()->after('subtotal');
                $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_factura', 'id_temporalidad')) {
                $table->dropForeign(['id_temporalidad']);
                $table->dropColumn('id_temporalidad');
            }
        });
    }
};