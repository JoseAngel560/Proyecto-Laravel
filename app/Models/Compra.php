<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;
    
    protected $table = 'compras';
    
    protected $fillable = [
        'id_proveedor',
        'id_empleado',
        'fecha_compra',
        'total',
        'descripcion',
        'id_temporalidad'
    ];
    
    protected $casts = [
        'fecha_compra' => 'datetime',
        'total' => 'decimal:2'
    ];
    
    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
    
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
    
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra');
    }
    
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
    
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_origen');
    }
}