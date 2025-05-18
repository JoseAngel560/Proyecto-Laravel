<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('movimientos_inventario')->insert([
            [
                'id_producto' => 1,
                'id_origen' => 1, // Proveedor MotoParts S.A.
                'tipo' => 'entrada',
                'cantidad' => 50,
                'fecha' => '2025-05-13',
                'descripcion' => 'Ingreso inicial de pastillas de freno',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_producto' => 2,
                'id_origen' => 2, // Proveedor Repuestos Rápidos
                'tipo' => 'entrada',
                'cantidad' => 30,
                'fecha' => '2025-05-13',
                'descripcion' => 'Ingreso inicial de cadenas de transmisión',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}