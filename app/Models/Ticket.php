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
        'drive_link',
        'fecha_vencimiento_sla',
        'estado_sla',
        'fecha_cierre',
        'tiempo_respuesta',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'fecha_vencimiento_sla' => 'datetime',
    ];

   
    //---que sea legible
    public function getSlaLegibleAttribute()
    {
        if (!$this->fecha_vencimiento_sla) {
            return 'Sin SLA';
        }

        $ahora = now();
        $vencimiento = $this->fecha_vencimiento_sla;

        if ($ahora->greaterThan($vencimiento)) {
            return 'Vencido';
        }

        $horasRestantes = $ahora->diffInHours($vencimiento);

        if ($horasRestantes < 24) {
            return "{$horasRestantes} horas";
        }

        $dias = floor($horasRestantes / 24);
        $horasSobrantes = $horasRestantes % 24;

        if ($horasSobrantes == 0) {
            return "{$dias} " . ($dias == 1 ? 'día' : 'días');
        }

        return "{$dias} " . ($dias == 1 ? 'día' : 'días') . " y {$horasSobrantes} horas";
    }

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
