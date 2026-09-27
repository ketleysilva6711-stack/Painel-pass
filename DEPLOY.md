# The Vault Panel v2.0 - Production Deployment Guide

## 🚀 Quick Start (Production Ready)

### Prerequisites
- Docker & Docker Compose v2+
- Domain name (or use local hosts file)
- Git

---

## 📋 1. Clone & Configure

```bash
# Clone repository
git clone https://github.com/SEU_USUARIO/the-vault-clone.git
cd the-vault-clone

# Create production environment
cp .env.production.template .env.production
# EDITE .env.production COM SEUS SECRETS REAIS!
```

### ⚠️ Secrets Obrigatórios no `.env.production`:
```bash
APP_KEY=base64:GERE_COM_php_artisan_key:generate
DB_PASSWORD=sua_senha_mysql_forte
DB_ROOT_PASSWORD=sua_senha_root_mysql_forte
JWT_SECRET=gere_32_caracteres_aleatorios_base64
REDIS_PASSWORD=sua_senha_redis
DB_PASSWORD=sua_senha_mysql
MAIL_PASSWORD=sua_senha_app_gmail
```

---

## 🔐 2. SSL/HTTPS (Let's Encrypt)

```bash
# Instalar certbot
sudo apt install certbot

# Gerar certificados
sudo certbot certonly --standalone -d seu-dominio.com -d www.seu-dominio.com

# Certificados salvos em:
# /etc/letsencrypt/live/seu-dominio.com/fullchain.pem
# /etc/letsencrypt/live/seu-dominio.com/privkey.pem

# Copiar para o projeto
mkdir -p certs/live/seu-dominio.com
sudo cp /etc/letsencrypt/live/seu-dominio.com/fullchain.pem certs/live/seu-dominio.com/
sudo cp /etc/letsencrypt/live/seu-dominio.com/privkey.pem certs/live/seu-dominio.com/
sudo chown -R $USER:$USER certs/
```

---

## 🐳 3. Deploy com Docker Compose

```bash
# Build e deploy
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# Verificar status
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps

# Ver logs
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# Health check
curl -f https://seu-dominio.com/api/health
```

---

## 🔧 4. Comandos Úteis

```bash
# Logs em tempo real
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f php

# Executar migrations
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec php php artisan migrate --force

# Limpar cache
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec php php artisan config:clear
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec php php artisan route:clear
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec php php artisan view:clear

# Backup manual
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec php php artisan backup:run

# Backup offsite (S3)
./backup-offsite.sh

# Testar restore
./restore-test.sh

# Renovar SSL (cron mensal)
certbot renew --quiet --deploy-hook "docker compose -f docker-compose.yml -f docker-compose.prod.yml restart nginx"
```

---

## 📊 5. Observabilidade (Opcional)

```bash
# Iniciar stack de monitoramento
docker compose -f docker-compose.yml -f docker-compose.observability.yml up -d

# Acessos:
# Grafana: http://seu-dominio.com:3000 (admin/senha_do_env)
# Prometheus: http://seu-dominio.com:9090
# Alertmanager: http://seu-dominio.com:9093
# Uptime Kuma: http://seu-dominio.com:3001
# cAdvisor: http://seu-dominio.com:8080
# Node Exporter: http://seu-dominio.com:9100/metrics
```

---

## 🔄 6. CI/CD (GitHub Actions)

O pipeline roda automaticamente no push para `main`:

1. **Tests** - Backend (Pest) + Frontend (Vitest)
2. **Static Analysis** - PHPStan Level 5 + Pint
3. **Security Scan** - Trivy
4. **Build** - Docker images multi-arch
4. **Deploy** - Self-hosted runner no VPS
5. **Health Check** - Verifica `/api/health`
6. **Notify** - Telegram

### Secrets Necessários no GitHub:
```
DOCKER_REGISTRY=ghcr.io
GITHUB_TOKEN=auto
DOMAIN=seu-dominio.com
TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=xxx
SMTP_PASSWORD=xxx
GRAFANA_PASSWORD=xxx
```

---

## 🗂️ Estrutura de Arquivos Importantes

```
the-vault-clone/
├── docker-compose.yml              # Base
├── docker-compose.prod.yml         # Produção
├── docker-compose.observability.yml # Monitoring stack
├── .env.production                 # SECRETS (não commitar!)
├── .env.production.template        # Template
├── docker/
│   ├── nginx/
│   │   ├── conf.d/default.conf     # Nginx base
│   │   └── nginx.prod.conf         # Nginx produção (SSL + rate limit)
│   ├── php/Dockerfile              # PHP + mysql-client + java + apktool
│   └── mysql/init/                 # Init scripts
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/       # 12 controllers
│   │   ├── Http/Middleware/        # LogAction, RoleAdmin, TwoFactorAuth
│   │   ├── Models/                 # 12 models
│   │   ├── Jobs/ProcessBypassJob.php
│   │   ├── Console/Commands/RunBackupCommand.php
│   │   ├── Http/Requests/          # Form requests
│   ├── database/
│   │   ├── migrations/             # 18 migrations
│   │   ├── seeders/                # DatabaseSeeder (feature flags)
│   ├── routes/
│   │   ├── api.php                 # 46 rotas API
│   │   ├── console.php             # Scheduler backup 02:00
│   ├── bootstrap/app.php           # Middleware aliases
│   ├── config/l5-swagger.php       # OpenAPI config
│   ├── phpstan.neon               # PHPStan level 5
│   ├── pint.json                  # Laravel Pint
│   ├── phpunit.xml                # Pest config
│   └── tests/                     # Pest tests
├── frontend/
│   ├── frontend/
│   │   ├── src/
│   │   │   ├── views/             # 16 views
│   │   │   ├── composables/useNavItems.ts # Feature flags
│   │   │   ├── stores/auth.ts     # Auth store + feature flags
│   │   │   ├── router/index.ts    # 33 rotas
│   │   │   └── components/
│   │   ├── Dockerfile.prod        # Frontend prod build
│   │   ├── nginx.conf             # Nginx frontend
│   │   ├── vitest.config.ts       # Vitest config
│   │   ├── cypress.config.ts      # Cypress config
│   │   └── cypress/               # E2E tests
├── keystore/thevault.keystore      # RSA 2048 (assinatura APK)
├── backup.sh                       # Backup local
├── backup-offsite.sh               # Backup S3/MinIO
├── restore-test.sh                 # Teste de restore
├── PLANO-PRODUCAO-100%.md          # Este guia
├── SISTEMA-100-FUNCIONAL/          # Documentação completa
├── .github/workflows/deploy.yml    # CI/CD
└── .github/workflows/test.yml      # Tests CI
```

---

## 🔍 Verificação Pós-Deploy

```bash
# 1. Health check
curl -f https://seu-dominio.com/api/health

# 2. Login admin
curl -X POST https://seu-dominio.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@seu-dominio.com","password":"sua_senha"}'

# 3. TOTP secret
curl -H "Authorization: Bearer SEU_TOKEN" \
  https://seu-dominio.com/api/auth/totp-secret

# 4. Habilitar 2FA
curl -X POST -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"code":"SEU_CODIGO_TOTP"}' \
  https://seu-dominio.com/api/auth/totp-enable

# 5. Acessar admin com 2FA
curl -H "Authorization: Bearer SEU_TOKEN" \
  -H "X-TOTP-Code: SEU_CODIGO_TOTP" \
  https://seu-dominio.com/api/admin/logs

# 6. Frontend
open https://seu-dominio.com
```

---

## 🆘 Troubleshooting

| Problema | Solução |
|----------|---------|
| `502 Bad Gateway` | `docker compose restart php nginx` |
| `SSL Certificate Error` | Verificar `certs/live/` e permissões |
| `Database Connection Failed` | Verificar `.env.production` e `docker compose logs mysql` |
| `Rate Limited` | Ajustar `limit_req_zone` no nginx |
| `Backup Failed` | Verificar espaço em disco e permissões `/backup` |
| `Queue Stuck` | `docker compose restart queue` |

---

## 📞 Suporte

- **Documentação**: `SISTEMA-100-FUNCIONAL/`
- **API Docs**: `https://seu-dominio.com/api/documentation` (Swagger)
- **Grafana**: `https://seu-dominio.com:3000`
- **Uptime Kuma**: `https://seu-dominio.com:3001`

---

**✅ Sistema Pronto para Produção!** 🚀

*Última atualização: $(date)*
*Versão: 1.0.0*
*Tag: `v1.0-pronto-producao`*