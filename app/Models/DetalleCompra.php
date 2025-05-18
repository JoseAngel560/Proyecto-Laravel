<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_compras';
    
    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'id_temporalidad'
    ];
    
    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];
    
    // Relaciones
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
    
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}