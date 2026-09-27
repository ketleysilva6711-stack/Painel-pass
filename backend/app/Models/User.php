<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'plan_id',
        'funcionalidades', 'conta_status',
        'reseller_disponivel', 'pode_servicos_externos', 'conta_expira_em'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'conta_expira_em'   => 'datetime',
            'password'          => 'hashed',
            'funcionalidades'   => 'array',
            'reseller_disponivel' => 'boolean',
            'pode_servicos_externos' => 'boolean',
        ];
    }

    public function getContaStatusLabelAttribute(): string {
        if ($this->role === 'admin') return 'Acesso total';
        if ($this->role === 'revendedor') {
            if (is_null($this->reseller_disponivel)) return 'Acesso revenda';
            return $this->reseller_disponivel ? 'Disponível' : 'Offline';
        }
        return match($this->conta_status) {
            'expired'      => 'Conta expirada',
            'expiring_soon'=> 'Expira em breve',
            default        => 'Conta ativa',
        };
    }

    public function getContaStatusKindAttribute(): string {
        if ($this->role === 'admin') return 'ok';
        if ($this->role === 'revendedor') {
            if (is_null($this->reseller_disponivel)) return 'ok';
            return $this->reseller_disponivel ? 'ok' : 'bad';
        }
        return match($this->conta_status) {
            'expired'       => 'bad',
            'expiring_soon' => 'warn',
            default         => 'ok',
        };
    }

    public function getRoleLabelAttribute(): string {
        return match($this->role) {
            'admin'      => 'Admin',
            'revendedor' => 'Revendedor',
            default      => 'Usuário',
        };
    }

    public function getEyebrowAttribute(): string {
        return match($this->role) {
            'admin'      => 'Gestão',
            'revendedor' => 'Revenda',
            default      => 'Meu espaço',
        };
    }

    public function getInitialsAttribute(): string {
        return match($this->role) {
            'admin'      => 'AD',
            'revendedor' => 'RE',
            default      => strtoupper(substr($this->name, 0, 2)),
        };
    }
}