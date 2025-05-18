<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias', 'id_temporalidad')) {
                $table->unsignedBigInteger('id_temporalidad')->nullable();
                $table->foreign('id_temporalidad')->references('id')->on('temporalidades')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('categorias', function (Blueprint $table) {
            if (Schema::hasColumn('categorias', 'id_temporalidad')) {
                $table->dropForeign(['id_temporalidad']);
                $table->dropColumn('id_temporalidad');
            }
        });
    }
};