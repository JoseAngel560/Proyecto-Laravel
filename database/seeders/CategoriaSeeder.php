<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->insert([
            [
                'nombre' => 'Frenos',
                'descripcion' => 'Repuestos para sistemas de frenado de motos',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Transmisión',
                'descripcion' => 'Cadenas, piñones y kits de transmisión',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Motor',
                'descripcion' => 'Componentes para el motor de motos',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}