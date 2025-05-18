<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('empleados')->insert([
            [
                'nombre' => 'Carlos',
                'apellido' => 'López',
                'cedula' => '1234567890',
                'telefono' => '0987654321',
                'email' => 'carlos@motorepuestos.com',
                'direccion' => 'Calle Central 789, Cuenca',
                'cargo' => 'Administrador',
                'salario' => 800.00,
                'fecha_contratacion' => '2025-01-01',
                'usuario' => 'admin',
                'contraseña' => Hash::make('admin123'),
                'id_temporalidad' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'cedula' => '0987654321',
                'telefono' => '0991234567',
                'email' => 'ana@motorepuestos.com',
                'direccion' => 'Av. Libertad 321, Loja',
                'cargo' => 'Vendedor',
                'salario' => 500.00,
                'fecha_contratacion' => '2025-02-01',
                'usuario' => 'vendedor1',
                'contraseña' => Hash::make('vendedor123'),
                'id_temporalidad' => 1,
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}