<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    
    protected $table = 'categorias';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_temporalidad'
    ];
    
    // Relaciones
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria');
    }
    
    public function temporalidad()
    {
        return $this->belongsTo(Temporalidad::class, 'id_temporalidad');
    }
}