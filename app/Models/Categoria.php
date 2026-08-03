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

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'categoria_id');
    }

    public function tiposSolicitud()
    {
        return $this->hasMany(TipoSolicitud::class, 'categoria_id');
    }
}
