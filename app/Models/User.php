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

    protected $table = 'users';
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'cargo',
        'rol_id',
        'unidad_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

 
    public function getAuthPassword()
    {
        return $this->password;
    }

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

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
