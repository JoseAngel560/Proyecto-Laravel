<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = [
        'id_factura',
        'id_empleado',
        'fecha_devolucion',
        'motivo_devolucion',
        'monto_total_devuelto',
        'id_temporalidad',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'monto_total_devuelto' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }

    public function temporalidad(): BelongsTo
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucion::class, 'id_devolucion');
    }
}