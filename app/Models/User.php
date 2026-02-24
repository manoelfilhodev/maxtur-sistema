<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'cargo',
        'nivel',
        'operador_id',
        'cliente_id',
        'role',
        'ativo',
        'jornada_id',
        'turno_id',
        'ferias_ativo',
        'ferias_inicio',
        'ferias_fim',
        'foto',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
            'ferias_ativo' => 'boolean',
            'ferias_inicio' => 'date',
            'ferias_fim' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Operador::class, 'operador_id');
    }

    public function checklistsCriados(): HasMany
    {
        return $this->hasMany(Checklist::class, 'created_by');
    }

    public function notificationsMvp(): BelongsToMany
    {
        return $this->belongsToMany(NotificationMvp::class, 'notification_users', 'user_id', 'notification_id')
            ->withPivot('read_at');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->cargo === 'admin' || (int) $this->id === 1;
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente' || $this->cargo === 'cliente';
    }

    public function isMotorista(): bool
    {
        return $this->role === 'motorista' || $this->cargo === 'motorista';
    }
}
