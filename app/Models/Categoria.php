<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model

{
    protected $table = 'categorias';
    protected $fillable = [
        'nombre_categoria',
        'unidad_id',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
