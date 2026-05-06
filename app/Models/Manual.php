<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
    protected $table = 'manuales';

    protected $fillable = [
        'titulo',
        'archivo_path',
        'categoria_id',
    ];


    public function categoria()
    {
        return $this->belongsTo(CategoriaManual::class, 'categoria_id');
    }
}
