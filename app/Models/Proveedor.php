<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'razon_social',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'ruc',
        'fecha_registro',
        'estado',
        'id_temporalidad',
    ];

    protected $casts = [
        'fecha_registro' => 'date',
    ];

    /**
     * Scope global para filtrar solo proveedores activos.
     */
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('estado', 'activo');
        });
    }

    /**
     * Relación con compras.
     */
    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor');
    }

    /**
     * Relación con temporalidad.
     */
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}