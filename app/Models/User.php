<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'cnh_vencimento',
        'tipo_recebimento',
        'valor_salario',
        'valor_por_viagem',
        'data_admissao',
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
            'cnh_vencimento' => 'date',
            'data_admissao' => 'date',
            'valor_salario' => 'decimal:2',
            'valor_por_viagem' => 'decimal:2',
        ];
    }

    public function documentosMotorista(): HasMany
    {
        return $this->hasMany(MotoristaDocumento::class, 'motorista_id')->latest();
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

    public function feedbacksEnviados(): HasMany
    {
        return $this->hasMany(FuncionarioFeedback::class, 'funcionario_user_id');
    }

    public function notificationsMvp(): BelongsToMany
    {
        return $this->belongsToMany(NotificationMvp::class, 'notification_users', 'user_id', 'notification_id')
            ->withPivot('read_at');
    }

    public function isAdmin(): bool
    {
        return $this->isMaster()
            || in_array(strtoupper((string) $this->role), ['ADMIN'], true)
            || in_array(strtolower((string) $this->role), ['admin'], true)
            || $this->cargo === 'admin';
    }

    public function isCliente(): bool
    {
        return in_array($this->role, ['CLIENT_ADMIN', 'CLIENT_USER', 'CLIENTE', 'cliente'], true) || $this->cargo === 'cliente';
    }

    public function isMotorista(): bool
    {
        return in_array(strtoupper((string) $this->role), ['MOTORISTA'], true) || $this->role === 'motorista' || $this->cargo === 'motorista';
    }

    public function isMaster(): bool
    {
        return (int) $this->id === 1 || strtoupper((string) $this->role) === 'MASTER';
    }

    public function isOperador(): bool
    {
        return in_array(strtoupper((string) $this->role), ['OPERADOR'], true) || strtolower((string) $this->role) === 'operador';
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
