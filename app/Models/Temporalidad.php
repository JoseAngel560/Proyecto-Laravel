<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temporalidad extends Model
{
    use HasFactory;
    protected $table = 'temporalidades';
    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'fecha_completa',
        'dia_semana',
        'dia_mes',
        'semana_mes',
        'dia_anio',
        'semana_anio',
        'trimestre_anio',
        'mes_anio',
        'vispera_festivo',
        'anio',
    ];

    /**
     * Castings para asegurar tipos consistentes.
     */
    protected $casts = [
        'fecha_completa' => 'datetime',
        'vispera_festivo' => 'boolean',
        'dia_mes' => 'integer',
        'semana_mes' => 'integer',
        'dia_anio' => 'integer',
        'semana_anio' => 'integer',
        'trimestre_anio' => 'integer',
        'anio' => 'integer',
    ];

    /**
     * Atributo personalizado para generar un resumen de la temporalidad.
     */
    public function getResumenTemporalidadAttribute()
    {
        return "Fecha: {$this->fecha_completa->format('d/m/Y')}, Día: {$this->dia_semana}, Mes: {$this->mes_anio}, Año: {$this->anio}";
    }
}
