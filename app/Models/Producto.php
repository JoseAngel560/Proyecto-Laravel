<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'marca',
        'modelo',
        'color',
        'precio_venta',
        'stock',
        'id_categoria',
        'id_temporalidad',
        'estado' 
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('estado', 'activo');
        });
    }

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'stock' => 'integer'
    ];
    
    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }
    
    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }
    
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }
    
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}