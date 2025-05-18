<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';
    
    protected $fillable = [
        'nombre', 
        'apellido', 
        'cedula', 
        'telefono', 
        'email', 
        'direccion', 
        'cargo', 
        'salario', 
        'fecha_contratacion',
        'usuario', 
        'contraseña',
        'id_temporalidad',
        'estado', 
    ];

    protected $hidden = ['contraseña'];
    
    protected $casts = [
        'fecha_contratacion' => 'date',
        'salario' => 'decimal:2',
        'estado' => 'string', 
    ];

    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }

    /**
     * Verifica si el empleado tiene acceso a una sección específica según su cargo.
     *
     * @param string $section
     * @return bool
     */
    public function hasAccessTo($section)
    {
        $permissions = [
            'Administrador' => [
                'inicio',
                'facturacion',
                'inventario',
                'compras',
                'clientes',
                'proveedores',
                'empleados',
                'reportes',
                'database-backup',
            ],
            'Vendedor' => [
                'inicio',
                'facturacion',
                'clientes',
                'reportes',
            ],
            'Inventario' => [
                'inicio',
                'compras',
                'inventario',
                'clientes',
                'proveedores',
                'reportes',
            ],
        ];

        return in_array($section, $permissions[$this->cargo] ?? []);
    }

    /**
     * Scope para filtrar empleados activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }
}