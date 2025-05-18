<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('productos')->insert([
            [
                'nombre' => 'Pastillas de Freno Delanteras',
                'descripcion' => 'Pastillas de freno para motos 150cc',
                'marca' => 'Yamaha',
                'modelo' => 'YZF-R15',
                'color' => 'Negro',
                'precio_venta' => 25.50,
                'stock' => 50,
                'id_categoria' => 1,
                'id_temporalidad' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cadena de Transmisión',
                'descripcion' => 'Cadena reforzada para motos 200cc',
                'marca' => 'Honda',
                'modelo' => 'CB190R',
                'color' => 'Plata',
                'precio_venta' => 45.00,
                'stock' => 30,
                'id_categoria' => 2,
                'id_temporalidad' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pistón de Motor',
                'descripcion' => 'Pistón para motor de motos 250cc',
                'marca' => 'Suzuki',
                'modelo' => 'GSX250R',
                'color' => 'Gris',
                'precio_venta' => 80.00,
                'stock' => 20,
                'id_categoria' => 3,
                'id_temporalidad' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}