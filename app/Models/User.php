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
    use Notifiable;

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'email_365',
        'password_365',
        'cargo',
        'rol_id',
        'unidad_id',
    ];

    // Laravel busca 'password' por defecto, le decimos que la tuya se llama distinto
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getEmailAttribute()
    {
        return $this->email;
    }

    /**
     * Relación: Un usuario pertenece a un rol.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
