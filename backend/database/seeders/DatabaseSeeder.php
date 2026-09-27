<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $planCortesia = Plan::updateOrCreate(['slug' => 'cortesia-7d'], [
            'name' => 'Cortesia 7 Dias',
            'price' => 0.00,
            'limits' => ['apps' => 15, 'bypass' => 15],
            'features' => ['PlayFake', 'Bypass', 'FuraBanco', 'PuxadaVip', 'ScreenTroll', 'InjectBanco'],
            'active' => true,
        ]);

        $planMensal = Plan::updateOrCreate(['slug' => 'mensal'], [
            'name' => 'Plano Mensal PRO',
            'price' => 149.90,
            'limits' => ['apps' => 100, 'bypass' => 100],
            'features' => ['PlayFake', 'Bypass', 'FuraBanco', 'PuxadaVip', 'ScreenTroll', 'InjectBanco'],
            'active' => true,
        ]);

        User::updateOrCreate(['email' => 'admin@thevault.com'], [
            'name'                   => 'Admin Vault',
            'password'               => 'secret123',
            'role'                   => 'admin',
            'plan_id'                => $planMensal->id,
            'conta_status'           => 'active',
            'pode_servicos_externos' => true,
        ]);

        User::updateOrCreate(['email' => 'rev@thevault.com'], [
            'name'                => 'Revendedor Demo',
            'password'            => 'secret123',
            'role'                => 'revendedor',
            'plan_id'             => $planMensal->id,
            'conta_status'        => 'active',
            'reseller_disponivel' => true,
        ]);

        User::updateOrCreate(['email' => 'user@thevault.com'], [
            'name'            => 'luiz77',
            'password'        => 'secret123',
            'role'            => 'user',
            'plan_id'         => $planCortesia->id,
            'funcionalidades' => ['PlayFake', 'Bypass', 'FuraBanco', 'PuxadaVip', 'ScreenTroll', 'InjectBanco'],
            'conta_status'    => 'expiring_soon',
            'conta_expira_em' => now()->addDays(3),
        ]);
    }
}