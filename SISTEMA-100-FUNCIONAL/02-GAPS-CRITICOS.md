# GAPS IDENTIFICADOS - PARA 100% FUNCIONAL COMPLETO

## Prioridade ALTA (3 gaps críticos)

### 1. Fura Banco / Bypass Pipeline Real
- **Impacto:** Sistema completo de build/assinatura de APKs
- **O que falta:**
  - Dockerfile com openjdk17 + apktool + apksigner
  - Tabela bypass_jobs no banco de dados
  - ProcessBypassJob (job Laravel: apktool d → patch → apktool b → apksigner)
  - Endpoints API: /bypass/jobs/{job}, /bypass/jobs/{job}/download
  - Keystore em volume Docker (nunca no git)
- **Complexidade:** ALTA (3-4 horas)
- **Arquivos necessários:**
  - database/migrations/2026_XXX_XXX_XXX_create_bypass_jobs_table.php
  - app/Jobs/ProcessBypassJob.php
  - app/Http/Controllers/BypassJobController.php
  - routes/api.php (novas rotas)
  - keystore/ directory no volume Docker

### 2. 2FA TOTP para Admin/Revendedor
- **Impacto:** Segurança avançada para contas privilegiadas
- **O que falta:**
  - Pacote guaviyard/laravel-totp
  - Métodos totpSecret/verifyTotp no modelo User
  - Rotas /api/auth/totp-secret e /api/auth/verify-totp
  - Middleware TwoFactorAuth para proteger rotas admin
  - Geração de QR Code para enrollment
- **Complexidade:** ALTA (2-3 horas)
- **Arquivos necessários:**
  - app/Models/User.php (métodos TOTP)
  - app/Http/Controllers/AuthController.php (métodos totpSecret/verifyTOTP)
  - app/Http/Middleware/TwoFactorAuth.php
  - routes/api.php (rotas 2FA)

### 3. Backups Automáticos
- **Impacto:** Segurança de dados e recuperação
- **O que falta:**
  - Script backup.sh (mysqldump + gzip)
  - Artisan command `php artisan backup:run`
  - Scheduler configurado (diário às 02:00)
  - Retenção de backups (30 dias)
- **Complexidade:** ALTA (1-2 horas)
- **Arquivos necessários:**
  - storage/scripts/backup.sh
  - app/Console/Commands/RunBackupCommand.php
  - app/Console/Kernel.php (schedule config)

## Prioridade MÉDIA (1 gap)

### 4. Keystore Volume Docker
- **Impacto:** Assinatura de APKs em produção
- **O que falta:**
  - Volume Docker montado em ./keystore
  - Keystore gerado (keytool -genkey)
  - Acesso dentro do container em /app/keystore/
  - Integração no ProcessBypassJob
- **Complexidade:** MÉDIA (1 hora)
- **Arquivos necessários:**
  - ./keystore/thevault.keystore (no host)
  - docker-compose.yml (volume montado)
  - Atualização no ProcessBypassJob.php

## Prioridade BAixa (1 gap)

### 5. Feature Flags Tracking
- **Impacto:** Controle de sidebar e visibilidade de módulos
- **O que falta:**
  - Accessors getFuncionalidadesAttribute/no modelo User
  - Seeders populando funcionalidades por role
  - Integração no AppShell.vue do frontend
  - Filtro dinâmico da sidebar
- **Complexidade:** BAIXA (2-3 horas)
- **Arquivos necessários:**
  - app/Models/User.php (accessors)
  - database/seeders/DatabaseSeeder.php (atualizado)
  - frontend/frontend/src/AppShell.vue (filtro)
  - frontend/frontend/src/stores/user.ts