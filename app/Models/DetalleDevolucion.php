<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDevolucion extends Model
{
    protected $table = 'detalle_devolucion';

    protected $fillable = [
        'id_devolucion',
        'id_detalle_factura',
        'id_producto',
        'cantidad_devuelta',
        'precio_unitario',
        'iva',
        'subtotal_devuelto',
        'id_temporalidad',
    ];

    protected $casts = [
        'cantidad_devuelta' => 'integer',
        'precio_unitario' => 'decimal:2',
        'iva' => 'decimal:2',
        'subtotal_devuelto' => 'decimal:2',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion');
    }

    public function detalleFactura(): BelongsTo
    {
        return $this->belongsTo(DetalleFactura::class, 'id_detalle_factura');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function temporalidad(): BelongsTo
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}