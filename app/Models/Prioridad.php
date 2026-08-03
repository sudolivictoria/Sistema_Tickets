<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prioridad extends Model
{
    protected $table = 'prioridades';

    protected $fillable = ['nombre_prioridad'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'prioridad_id');
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'prioridad_unidad')
            ->withPivot('horas_sla')
            ->withTimestamps();
    }
}
