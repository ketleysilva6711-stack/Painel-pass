<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'plan_id',
        'funcionalidades', 'conta_status',
        'reseller_disponivel', 'pode_servicos_externos', 'conta_expira_em',
        'totp_secret', 'totp_enabled',
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

    // 2FA TOTP Methods
    public function setupTotp(): void
    {
        $google2fa = app(Google2FA::class);
        $this->totp_secret = $google2fa->generateSecretKey();
        $this->save();
    }

    public function getTotpSecretAttribute()
    {
        return $this->attributes['totp_secret'] ?? null;
    }

    public function verifyTotp(string $code): bool
    {
        $google2fa = app(Google2FA::class);
        return $google2fa->verifyKey($this->attributes['totp_secret'] ?? '', $code);
    }

    public function getTotpQrCodeUrlAttribute(): string
    {
        $google2fa = app(Google2FA::class);
        return $google2fa->getQRCodeUrl(
            'The Vault Panel',
            $this->email,
            $this->attributes['totp_secret'] ?? ''
        );
    }

    // Feature Flags - Funcionalidades
    public function getFuncionalidadesAttribute($value)
    {
        return $value ?? [];
    }

    public function setFuncionalidadesAttribute($value)
    {
        $this->attributes['funcionalidades'] = is_array($value) ? $value : json_decode($value, true);
    }
}