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
        'telefone',
        'endereco',
        'nivel',
        'operador_id',
        'client_id',
        'cliente_id',
        'role',
        'ativo',
        'activation_token',
        'activation_expires_at',
        'activated_at',
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
            'activation_expires_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'client_id');
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
        return $this->role === 'MASTER' || $this->role === 'admin' || $this->cargo === 'admin' || (int) $this->id === 1;
    }

    public function isCliente(): bool
    {
        return in_array($this->role, ['CLIENT_ADMIN', 'CLIENT_USER', 'cliente'], true) || $this->cargo === 'cliente';
    }

    public function isMotorista(): bool
    {
        return $this->role === 'motorista' || $this->cargo === 'motorista';
    }

    public function isMaster(): bool
    {
        return $this->isAdmin() || $this->role === 'MASTER';
    }

    public function isClientAdmin(): bool
    {
        return $this->role === 'CLIENT_ADMIN';
    }

    public function isClientUser(): bool
    {
        return $this->role === 'CLIENT_USER';
    }

    public function requiresActivation(): bool
    {
        return in_array($this->role, ['CLIENT_ADMIN', 'CLIENT_USER', 'funcionario'], true);
    }
}
