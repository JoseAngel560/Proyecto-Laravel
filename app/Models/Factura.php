<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'id_cliente',
        'id_empleado',
        'fecha_factura',
        'total',
        'iva',
        'totalcancelado',
        'cambio',
        'metodo_pago',
        'id_temporalidad',
    ];

    protected $casts = [
        'fecha_factura' => 'datetime',
        'total' => 'decimal:2',
        'iva' => 'decimal:2',
        'totalcancelado' => 'decimal:2',
        'cambio' => 'decimal:2',
        'metodo_pago' => 'string',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
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
        return $this->hasMany(DetalleFactura::class, 'id_factura');
    }

    public function datosTarjeta(): HasMany
    {
        return $this->hasMany(DatosTarjeta::class, 'id_factura');
    }
}