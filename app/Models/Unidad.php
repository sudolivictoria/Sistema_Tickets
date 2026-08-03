<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{

protected $table = 'unidades';

protected $fillable = ['nombre_unidad'];

public function usuarios()
{
    return $this->hasMany(User::class, 'unidad_id');
}

public function categorias()
{
    return $this->hasMany(Categoria::class, 'unidad_id');
}

public function prioridades()
{
    return $this->belongsToMany(Prioridad::class, 'prioridad_unidad')
        ->withPivot('horas_sla')
        ->withTimestamps();
}
}
