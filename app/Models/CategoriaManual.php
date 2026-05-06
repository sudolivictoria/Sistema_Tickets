<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaManual extends Model
{
    protected $table = 'categorias_manuales';
    protected $fillable = ['nombre_categoria_manual'];
}
