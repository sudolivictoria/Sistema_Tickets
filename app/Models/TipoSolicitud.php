<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSolicitud extends Model
{
    protected $table = 'tipo_solicitudes';

    protected $fillable = ['nombre_tipo_solicitud', 'descripcion_solicitud', 'categoria_id'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
