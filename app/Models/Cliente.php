<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'direccion',
        'estado',
        'id_temporalidad',
    ];

    /**
     * Scope global para filtrar solo clientes activos.
     */
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('estado', 'activo');
        });
    }

    /**
     * Relación con temporalidad.
     */
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}