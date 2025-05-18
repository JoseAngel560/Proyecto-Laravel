<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatosTarjeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'datos_tarjeta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_factura',
        'nombre_titular',
        'numero_tarjeta',
        'fecha_expiracion',
        'tipo_tarjeta',
        'id_temporalidad',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_expiracion' => 'date',
        'tipo_tarjeta' => 'string', 
    ];


    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }


    public function temporalidad(): BelongsTo
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}