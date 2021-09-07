<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'ventas';

    public function cliente()
    {        
        return $this->belongsTo(Cliente::class, 'idcliente');
    }

    public function vendedor()
    {        
        return $this->belongsTo(Vendedor::class, 'idusuario');
    }

    public function detalles_venta()
    {
        return $this->hasMany(DetalleVenta::class, 'idventa');
    }
}
