<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('proveedores')->insert([
            [
                'nombre' => 'MotoParts S.A.',
                'razon_social' => 'MotoParts Sociedad Anónima',
                'contacto' => 'Luis Vargas',
                'telefono' => '0981112223',
                'email' => 'ventas@motoparts.com',
                'direccion' => 'Zona Industrial 456, Quito',
                'ruc' => '1791234567001',
                'fecha_registro' => '2025-01-15',
                'estado' => 'activo',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Repuestos Rápidos',
                'razon_social' => 'Repuestos Rápidos Cía. Ltda.',
                'contacto' => 'Sofía Torres',
                'telefono' => '0992223334',
                'email' => 'contacto@repuestosrapidos.com',
                'direccion' => 'Av. Amazonas 789, Guayaquil',
                'ruc' => '0991234567001',
                'fecha_registro' => '2025-02-01',
                'estado' => 'activo',
                'id_temporalidad' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}