<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            TemporalidadSeeder::class,
            CategoriaSeeder::class,
            ClienteSeeder::class,
            EmpleadoSeeder::class,
            ProveedorSeeder::class,
            ProductoSeeder::class,
            MovimientoInventarioSeeder::class,
        ]);
    }
}