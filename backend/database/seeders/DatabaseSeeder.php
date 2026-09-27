<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@thevault.com'], [
            'name'     => 'Admin Vault',
            'password' => 'secret123',
            'role'     => 'admin',
            'funcionalidades' => null,
            'conta_status' => 'active',
            'pode_servicos_externos' => true,
        ]);

        User::updateOrCreate(['email' => 'rev@thevault.com'], [
            'name'     => 'Revendedor Demo',
            'password' => 'secret123',
            'role'     => 'revendedor',
            'funcionalidades' => null,
            'conta_status' => 'active',
            'reseller_disponivel' => true,
        ]);

        User::updateOrCreate(['email' => 'user@thevault.com'], [
            'name'     => 'luiz77',
            'password' => 'secret123',
            'role'     => 'user',
            'funcionalidades' => ['PlayFake','Bypass','FuraBanco','PuxadaVip','ScreenTroll','InjectBanco'],
            'conta_status' => 'expiring_soon',
            'conta_expira_em' => now()->addDays(3),
        ]);
    }
}