<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clientes')->insert([
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'telefono' => '0981234567',
                'direccion' => 'Av. Principal 123, Quito',
                'estado' => 'activo',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'María',
                'apellido' => 'Gómez',
                'telefono' => '0998765432',
                'direccion' => 'Calle Secundaria 456, Guayaquil',
                'estado' => 'activo',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}