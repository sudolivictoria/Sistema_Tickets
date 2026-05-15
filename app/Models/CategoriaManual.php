<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaManual extends Model
{
    protected $table = 'categoria_manuales';
    protected $fillable = ['nombre_categoria_manual'];
}
