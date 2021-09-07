<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $connection = "mysql2";
    protected $table = 'articulos'; 

    public function detalles_venta()
    {
        return $this->hasMany(DetalleVenta::class, 'idarticulo');
    }
}
