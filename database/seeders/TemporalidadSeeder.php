<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemporalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('temporalidades')->insert([
            [
                'fecha_completa' => '2025-05-13 00:00:00',
                'dia_semana' => 'Martes',
                'dia_mes' => 13,
                'semana_mes' => 2,
                'dia_anio' => 133,
                'semana_anio' => 20,
                'trimestre_anio' => 2,
                'mes_anio' => 5,
                'vispera_festivo' => false,
                'anio' => 2025,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}