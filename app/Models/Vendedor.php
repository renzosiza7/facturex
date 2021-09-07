<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    use HasFactory;

    protected $connection = "mysql2";
    protected $table = 'users';  

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idusuario');
    }
}
