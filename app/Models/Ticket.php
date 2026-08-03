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

    //---------clean fechas
    protected $casts = [
        'fecha_cierre' => 'datetime',
        'fecha_vencimiento_sla' => 'datetime',
    ];

   
    //---metodo para obtener el SLA legible
    public function getSlaLegibleAttribute()
    {
        if (!$this->fecha_vencimiento_sla) {
            return 'Sin SLA';
        }

        $ahora = now();
        $vencimiento = $this->fecha_vencimiento_sla;

        //------calculo para saber si el ticket esta vencido
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

    //------------relaciones con otras tablas--------------------
    // Hallazgo B3: FK explícita en vez de dejar que Eloquent la adivine por el
    // nombre del método, para que un futuro rename no rompa la relación en silencio.
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function tipo_solicitud()
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }

    public function prioridad()
    {
        return $this->belongsTo(Prioridad::class, 'prioridad_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}
