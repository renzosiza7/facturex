<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'detalle_ventas';

    public function venta()
    {        
        return $this->belongsTo(Venta::class, 'idventa');
    }

    public function producto()
    {        
        return $this->belongsTo(Producto::class, 'idarticulo');
    }    
}
