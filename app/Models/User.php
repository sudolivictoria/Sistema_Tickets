<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Rol;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    //-----campos que se llenan masivamente

    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'rol_id',
        'unidad_id',
        'activo',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

 
    //-----------password
    public function getAuthPassword()
    {
        return $this->password;
    }

    //-----------email
    public function getEmailAttribute($value)
    {
        return $value ?? $this->attributes['email'] ?? null;
    }

    /**
     * Relación: Un usuario pertenece a un rol.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    //---------unidad a la que pertenece el usuario
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    //---------tickets que este usuario ha creado
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    //---------tickets asignados a este usuario como técnico
    public function ticketsAsignados()
    {
        return $this->hasMany(Ticket::class, 'tecnico_id');
    }

    //------metodo para identificar el rol
    public function tieneRol($rolNombre)
    {
        return $this->rol && $this->rol->nombre_rol === $rolNombre;
    }

    //------metodo para identificar si el usuario es staff del ticket------
    public function esStaffDelTicket(Ticket $ticket): bool
    {
        $esGestorDeSuUnidad = $this->tieneRol('Gestor')
            && $this->unidad_id
            && $ticket->categoria?->unidad_id === $this->unidad_id;

        $esTecnicoAsignado = $ticket->tecnico_id !== null && $ticket->tecnico_id === $this->id;

        return $this->tieneRol('Admin') || $esGestorDeSuUnidad || $esTecnicoAsignado;
    }
}
