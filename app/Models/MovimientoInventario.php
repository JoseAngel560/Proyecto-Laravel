<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;
    
    protected $table = 'movimientos_inventario';
    
    protected $fillable = [
        'id_producto',
        'id_origen',
        'tipo',
        'cantidad',
        'fecha',
        'descripcion',
        'id_temporalidad'
    ];
    
    
    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
    
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}