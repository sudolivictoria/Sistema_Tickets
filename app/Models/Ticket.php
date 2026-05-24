<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'tecnico_id',
        'categoria_id',
        'prioridad_id',
        'tipo_solicitud_id',
        'estado_id',
        'asunto',
        'descripcion',
        'comentario',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function tipo_solicitud()
    {
        return $this->belongsTo(TipoSolicitud::class);
    }

    public function prioridad()
    {
        return $this->belongsTo(Prioridad::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}
