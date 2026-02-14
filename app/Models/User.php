<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'usuario',
        'nombres',
        'email',
        'password',
        'rol_id',
        'acceso_web',
        'acceso_app',
        'expiracion_sesion',
        'archivo_imagen',
        'activo',
        'eliminado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'acceso_web' => 'boolean',
            'acceso_app' => 'boolean',
            'expiracion_sesion' => 'date',
            'activo' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user');
    }

    public function esAdmin(): bool
    {
        return $this->rol->slug === 'super_admin';
    }

    public function esSupervisor(): bool
    {
        return in_array($this->rol->slug, ['supervisor', 'supervisor_invitado', 'super_admin']);
    }

    public function tieneAccesoWeb(): bool
    {
        return $this->acceso_web && !$this->eliminado;
    }

    public function tieneAccesoApp(): bool
    {
        return $this->acceso_app && !$this->eliminado;
    }

    public function sesionExpirada(): bool
    {
        return $this->expiracion_sesion && $this->expiracion_sesion->isPast();
    }
}
