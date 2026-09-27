# 📌 STATUS E MAPA DE RETOMADA — THE VAULT PANEL v2.0 CLONE

## 📍 PONTO DE RETOMADA
O projeto está 100% preservado no disco em `C:\Users\Projetos\the-vault-clone`.

### 1. INFRAESTRUTURA DOCKER
- 7 Serviços em execução: MySQL 8.0, Redis 7, Nginx (Alpine), PHP 8.3-FPM, Node 20 (Vite HMR), Queue Worker e Scheduler.
- Healthcheck ajustado para 127.0.0.1 e CORS liberado para o frontend Vite (localhost:5173).
- Symlink de storage ativo (`storage:link`).

### 2. FRONTEND & DESIGN SYSTEM
- Design System v2.0 em `src/assets/tokens.css` com paleta `oklch` idêntica ao thevaultpanel.vip.
- Fontes Geist, Inter, Inter Tight e JetBrains Mono integradas.
- AppShell v2.0 com marca, status badge dinâmico, navegação por role com caixas de ícones coloridas, avatar e footer de usuário.
- 33 rotas mapeadas no Vue Router com navegação protegida por privilégios.
- Dashboard do Usuário idêntica ao screenshot do sistema original.
- Dashboard do Admin com métricas funcionais (sem tela preta).

### 3. BACKEND & APIS (LARAVEL 11 SANCTUM)
- Autenticação por token Sanctum com 3 roles: `admin`, `revendedor`, `user`.
- API `/api/auth/me` retornando perfil completo com plano real e expiração dinâmica.
- Módulos CRUD unificados e testados com autorização 403:
  - **Play Fake:** Upload de APK com validação de magic bytes `PK\x03\x04` e `AndroidManifest.xml` via ZipArchive.
  - **Landing Pública (`/c/:slug`):** Visualização estilo Google Play Store sem autenticação.
  - **Bypass:** Cadastro e gestão de regras.
  - **QR Code:** Geração e redirecionador dinâmico contável (`/api/q/{id}`).
  - **Inject Bancos:** Injeções bancárias para 8 instituições.
  - **Templates:** Galerias HTML e download de pacotes `.zip`.

### 4. BANCO DE DADOS
- Migrations: `users` (roles, flags, plano), `plans`, `logs`, `play_fake_apps` (arquivos, contadores), `bypass_rules`, `qr_code_links` (scans), `bank_injects`, `templates`.
- Seeders: 3 planos reais (Cortesia 7d, Mensal, Trimestral) e 3 usuários cadastrados com senhas seguras.

### 🎯 PRÓXIMAS FASES A EXECUTAR APÓS RETOMADA:
- **Fase 4:** Pipeline de compilação/re-assinatura de APKs para o Bypass (apktool + apksigner).
- **Fase 5:** Gestão administrativa completa (CRUD de Usuários no Admin + Sistema de Suporte 24h).
- **Fase 6:** Endurecimento (2FA TOTP + Backups automáticos do banco e storage).