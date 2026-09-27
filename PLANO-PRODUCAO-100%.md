# 📋 PLANO COMPLETO: DE "FUNCIONAL" PARA "PRODUÇÃO 100%"

**Projeto:** the-vault-clone  
**Data:** 27/09/2026  
**Versão:** 1.0  
**Status:** Sistema 100% Funcional - Pronto para Hardening de Produção  

---

## ✅ STATUS ATUAL: TODAS 5 ETAPAS CONCLUÍDAS

| Etapa | Título | Concluído | Commit | Detalhes |
|-------|--------|-----------|--------|----------|
| **1** | Pipeline Bypass (apktool + apksigner + keystore) | ✅ 100% | 133b2d5 | APK assinado v1+v2+v3, job assíncrono, polling, download |
| **2** | 2FA TOTP (Admin/Revendedor) | ✅ 100% | 133b2d5 | Middleware header-based, secret+QR, enable/disable/verify |
| **3** | Backups Automáticos | ✅ 100% | 133b2d5 | mysqldump --skip-ssl, scheduler 02:00, retention 30d |
| **4** | Keystore Volume Docker | ✅ 100% | 133b2d5 | Volume `./keystore:/app/keystore:ro` em php/queue/scheduler |
| **5** | Feature Flags (sidebar dinâmico) | ✅ 100% | 133b2d5 | 9 flags admin, 4 revendedor, 3 user |

### 🏷️ Tags Criadas
- `v1.0-retomada-garantida` - Ponto de salvamento inicial
- `v1.0-fase45-concluida` - Fases 4+5 integradas
- `v1.0-sistema-completo-funcional` - **Sistema 100% funcional**

---

## 📊 RESUMO TÉCNICO DO SISTEMA

### Infraestrutura
- **7 Containers Docker:** mysql, redis, nginx, php, node, queue, scheduler
- **Healthchecks:** Todos healthy
- **Volumes:** mysql_data, redis_data, keystore, storage, backend, frontend

### Backend (Laravel 11 + Sanctum)
- **Autenticação:** Token-based, 3 roles (admin, revendedor, user)
- **2FA TOTP:** Header `X-TOTP-Code` para rotas admin
- **API:** 46 rotas (RESTful, paginadas, validadas)
- **Jobs:** Queue worker + Scheduler (backup diário 02:00)
- **Logs:** Middleware LogAction (POST/PUT/DELETE autenticados)

### Frontend (Vue 3 + TypeScript + Vite)
- **Build:** 194 KB JS (gzip 68 KB) - Zero erros
- **Rotas:** 33 rotas protegidas por role
- **Feature Flags:** Sidebar dinâmica por `user.funcionalidades[]`
- **Design System:** oklch palette, Geist/Inter/JetBrains Mono

### Módulos Core Implementados
| Módulo | Rotas | Funcionalidades |
|--------|-------|-----------------|
| **Auth** | 5 | Login, logout, me, TOTP secret/enable/disable/verify |
| **Play Fake** | 8 | Upload APK (validação PK/manifest), download ZIP, slug público |
| **QR Code** | 8 | CRUD + redirect `/q/{id}` com contador |
| **Bypass** | 8 | CRUD regras + Pipeline job (decompile→patch→recompile→sign) |
| **Inject Bancos** | 8 | CRUD injeções |
| **Templates** | 5 | Galeria + download ZIP (5 templates HTML ricos) |
| **Consulta** | 2 | CRUD usuário + histórico |
| **Ranking** | 1 | Pódio top 10 |
| **Suporte** | 2 | Tickets + mensagens |
| **Admin** | 3 | Users list + renovação + logs |

---

## ❌ ANÁLISE REALISTA: GAPS PARA PRODUÇÃO 100%

### 🔴 CRÍTICOS (Bloqueiam deployment real) - **48-72h**

| # | Item | Status | Esforço | Risco |
|---|------|--------|---------|-------|
| 1 | **HTTPS/TLS real** | ❌ HTTP somente localhost | 4-6h | **ALTO** - Dados sensíveis expostos |
| 2 | **Secrets management** | ❌ Hardcoded no docker-compose | 2-3h | **ALTO** - Vazamento de credenciais |
| 3 | **Rate limiting / DDoS** | ❌ Inexistente | 3-4h | **ALTO** - Abuso de API |
| 3 | **Backup offsite (S3/MinIO)** | ❌ Apenas local | 3-4h | **MÉDIO** - Perda total se container morrer |
| 4 | **Monitoring/Alerting** | ❌ Zero observabilidade | 6-8h | **MÉDIO** - Não sabe quando cai |
| 5 | **CI/CD Pipeline** | ❌ Deploy manual | 4-6h | **MÉDIO** - Deploy propenso a erro |
| 6 | **Testes automatizados** | ❌ Zero testes | 8-12h | **MÉDIO** - Regressões silenciosas |

### 🟡 IMPORTANTES (Qualidade produção) - **8-16h**

| # | Item | Status | Esforço |
|---|------|--------|---------|
| 1 | **Validação de entrada robusta** | Parcial | 3h |
| 2 | **Logs estruturados (JSON)** | Parcial (LogAction básico) | 2h |
| 3 | **Health checks profundos** | Básico (`/api/health`) | 1h |
| 4 | **Documentação API (OpenAPI/Swagger)** | ❌ | 3h |
| 4 | **Queue worker monitoring** | Básico | 2h |
| 5 | **Cache Redis otimizado** | Básico | 1h |

### 🟢 MELHORIAS TÉCNICAS (Dívida técnica) - **8h**

| Área | Problema | Solução |
|------|----------|---------|
| **Dockerfile** | Build lento (300s+), layers não otimizados | Multi-stage, cache deps |
| **Frontend bundle** | 194KB JS | Code splitting, tree shaking |
| **N+1 queries** | Provável em `navGroups` + relacionamentos | Eager loading |
| **Validação TOTP** | Header custom (`X-TOTP-Code`) não padrão | Usar `Authorization: Bearer + TOTP` |
| **Backup retention** | 30 dias hardcoded | Configurável via env |
| **Keystore rotation** | Nunca expira/rotaciona | Expiração + rotação automática |

---

## 📋 PLANO DE AÇÃO COMPLETO PARA PRODUÇÃO

### 🔴 FASE 1 - Segurança Crítica (1-2 dias) - **EXECUTAR AGORA**

#### 1.1 HTTPS/TLS com Let's Encrypt + Nginx
```bash
# No host:
# 1. Gerar certificado
certbot certonly --standalone -d seu-dominio.com

# 2. Estrutura de certificados:
# ./certs/live/seu-dominio.com/fullchain.pem
# ./certs/live/seu-dominio.com/privkey.pem
```

**docker-compose.yml - Service Nginx:**
```yaml
nginx:
  image: nginx:alpine
  ports:
    - "80:80"
    - "443:443"
  volumes:
    - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
    - ./certs/live/seu-dominio.com:/etc/ssl/certs:ro
    - ./certs/live/seu-dominio.com:/etc/ssl/private:ro
  # ... resto igual
```

**docker/nginx/conf.d/default.conf:**
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com;
    
    ssl_certificate /etc/ssl/certs/fullchain.pem;
    ssl_certificate_key /etc/ssl/private/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        proxy_pass http://php:9000;
        # ... proxy config
    }
}
```

#### 1.2 Secrets Management (.env.production + Docker Secrets)
```bash
# 1. Criar .env.production
cp .env.example .env.production

# Editar itens CRÍTICOS:
# APP_KEY=base64:gere-com-php-artisan-key-generate
# APP_ENV=production
# APP_DEBUG=false
# DB_PASSWORD=sua-senha-segura-mysql
# JWT_SECRET=gere-32-caracteres-aleatorios-muito-seguros
# MAIL_PASSWORD=sua-senha-mail
```

**docker-compose.yml - Adicionar environment vars:**
```yaml
php:
  env_file:
    - .env.production
  environment:
    - APP_KEY=${APP_KEY}
    - DB_PASSWORD=${DB_PASSWORD}
    - JWT_SECRET=${JWT_SECRET}
    # ... outras vars
```

#### 1.3 Rate Limiting (Nginx + Laravel)
**docker/nginx/conf.d/default.conf:**
```nginx
# Global rate limit zones
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

server {
    # ... ssl config ...
    
    location /api/auth/login {
        limit_req zone=login burst=5 nodelay;
        proxy_pass http://php:9000;
    }
    
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        proxy_pass http://php:9000;
    }
}
```

**Laravel Throttle (routes/api.php):**
```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // rotas autenticadas
});
```

#### 1.4 Firewall Rules
```bash
# Apenas portas necessárias expostas:
# 80 (HTTP redirect)
# 443 (HTTPS)
# 22 (SSH - se necessário, preferir VPN)
# 
# Portas INTERNAS apenas (não expostas):
# 3306 (MySQL)
# 6379 (Redis) 
# 9000 (PHP-FPM)
# 5173 (Vite dev - apenas dev)
```

---

### 🟡 FASE 2 - Observabilidade (1 dia)

#### 2.1 Prometheus + Grafana
```yaml
# docker-compose.yml adicionar:
prometheus:
  image: prom/prometheus:latest
  ports:
    - "9090:9090"
  volumes:
    - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
    - prometheus_data:/prometheus
  networks:
    - the-vault-network

grafana:
  image: grafana/grafana:latest
  ports:
    - "3000:3000"
  volumes:
    - ./monitoring/grafana/dashboards:/etc/grafana/provisioning/dashboards
    - ./monitoring/grafana/datasources:/etc/grafana/provisioning/datasources
    - grafana_data:/var/lib/grafana
  environment:
    - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASSWORD}
  networks:
    - the-vault-network

# Networks adicionar:
# prometheus_data:
# grafana_data:
```

**monitoring/prometheus.yml:**
```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'laravel'
    static_configs:
      - targets: ['php:9000']
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx:80']
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql:3306']
  - job_name: 'redis'
    static_configs:
      - targets: ['redis:6379']
```

#### 2.2 Loki + Promtail (Logs Centralizados)
```yaml
loki:
  image: grafana/loki:latest
  ports:
    - "3100:3100"
  volumes:
    - ./monitoring/loki-config.yaml:/etc/loki/local-config.yaml
    - loki_data:/loki
  networks:
    - the-vault-network

promtail:
  image: grafana/promtail:latest
  volumes:
    - ./backend/storage/logs:/var/log/laravel
    - ./monitoring/promtail-config.yaml:/etc/promtail/config.yml
  networks:
    - the-vault-network
```

#### 2.3 Alertmanager + Telegram/Email
```yaml
alertmanager:
  image: prom/alertmanager:latest
  ports:
    - "9093:9093"
  volumes:
    - ./monitoring/alertmanager.yml:/etc/alertmanager/alertmanager.yml
  networks:
    - the-vault-network
```

#### 2.4 Uptime Kuma
```yaml
uptime-kuma:
  image: louislam/uptime-kuma:latest
  ports:
    - "3001:3001"
  volumes:
    - uptime_kuma_data:/app/data
  networks:
    - the-vault-network
```

---

### 🟡 FASE 3 - CI/CD + Backup Offsite (1 dia)

#### 3.1 GitHub Actions Deploy
**.github/workflows/deploy.yml:**
```yaml
name: Deploy to Production
on:
  push:
    branches: [main]
  workflow_dispatch:

jobs:
  deploy:
    runs-on: self-hosted
    steps:
      - uses: actions/checkout@v4
      
      - name: Build Frontend
        run: |
          cd frontend/frontend
          npm ci
          npm run build
      
      - name: Build Docker Images
        run: docker-compose build
      
      - name: Deploy
        run: |
          docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
      
      - name: Run Migrations
        run: docker-compose exec -T php php artisan migrate --force
      
      - name: Health Check
        run: |
          sleep 10
          curl -f https://seu-dominio.com/api/health || exit 1
```

#### 3.2 Backup Offsite (S3/MinIO)
```bash
# Instalar rclone
curl https://rclone.org/install.sh | bash

# Configurar
rclone config
# name: s3-backup
# type: s3
# provider: AWS (ou MinIO)
# env_auth: false
# access_key_id: XXX
# secret_access_key: XXX
# region: us-east-1
# endpoint: (para MinIO)

# Script de backup offsite
cat > /usr/local/bin/backup-offsite.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/www/html/storage/backups"
rclone copy "$BACKUP_DIR" "s3-backup:seu-bucket/backups/the-vault/" --progress
# Retenção S3 (30 dias)
rclone delete --min-age 30d "s3-backup:seu-bucket/backups/the-vault/"
EOF
chmod +x /usr/local/bin/backup-offsite.sh

# Cron diário 03:00 (após backup local 02:00)
echo "0 3 * * * root /usr/local/bin/backup-offsite.sh" >> /etc/crontab
```

#### 3.3 Restore Test Automatizado
```bash
cat > /usr/local/bin/test-restore.sh << 'EOF'
#!/bin/bash
# Testar restore mensal
DATE=$(date +%Y%m%d)
LATEST=$(rclone lsf "s3-backup:seu-bucket/backups/the-vault/" --format "p" | sort | tail -1)
rclone copy "s3-backup:seu-bucket/backups/the-vault/$LATEST" /tmp/restore_test/
gunzip /tmp/restore_test/*.gz
# Verificar estrutura
head -5 /tmp/restore_test/*.sql | grep -q "MariaDB dump" && echo "✅ Restore test OK" || echo "❌ Restore test FAILED"
rm -rf /tmp/restore_test
EOF
chmod +x /usr/local/bin/test-restore.sh

# Cron mensal dia 1 às 04:00
echo "0 4 1 * * root /usr/local/bin/test-restore.sh" >> /etc/crontab
```

---

### 🟢 FASE 4 - Qualidade + Testes (1-2 dias)

#### 4.1 Backend Tests (Pest PHP)
```bash
composer require --dev pestphp/pest pestphp/pest-plugin-laravel
php artisan pest:install

# Exemplo test
# tests/Feature/AuthTest.php
it('can login with valid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@thevault.com',
        'password' => 'secret123'
    ]);
    $response->assertOk()->assertJsonStructure(['access_token', 'user']);
});

it('cannot access admin route without 2fa', function () {
    $user = User::factory()->create(['role' => 'admin', 'totp_enabled' => true]);
    $token = $user->createToken('test')->plainTextToken;
    
    $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
        ->getJson('/api/admin/logs');
    $response->assertStatus(401);
});
```

#### 4.2 Frontend Tests (Vitest + Cypress)
```bash
npm install -D vitest @vue/test-utils cypress
# cypress.config.ts
export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:5173',
    specPattern: 'cypress/e2e/**/*.cy.ts'
  }
})
```

#### 4.3 Static Analysis (PHPStan + Pint)
```bash
composer require --dev phpstan/phpstan laravel/pint
./vendor/bin/phpstan analyse --level=5 --memory-limit=512M
./vendor/bin/pint --write .
```

#### 4.4 OpenAPI/Swagger
```bash
composer require --dev darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
# Adicionar anotações nos controllers
php artisan l5-swagger:generate
# Acessar: https://seu-dominio.com/api/documentation
```

---

## 📅 CRONOGRAMA EXECUTIVO

| Semana | Fase | Foco | Entregáveis |
|--------|------|------|-------------|
| **Semana 1** | FASE 1 | Segurança Crítica | HTTPS, Secrets, Rate Limit, Firewall |
| **Semana 2** | FASE 2 | Observabilidade | Prometheus, Grafana, Loki, Alertas, Uptime |
| **Semana 3** | FASE 3 | CI/CD + Backup Offsite | GitHub Actions, S3 Backup, Restore Test |
| **Semana 4** | FASE 4 | Qualidade | Tests, PHPStan, Pint, Swagger |

---

## 💰 ESTIMATIVA DE CUSTOS (Mensal - AWS/Cloud)

| Serviço | Custo Estimado | Observação |
|---------|----------------|------------|
| EC2 t3.medium (2 vCPU, 4GB) | ~$30/mês | Host único |
| RDS MySQL db.t3.micro | ~$15/mês | Managed DB (opcional) |
| ElastiCache Redis t3.micro | ~$12/mês | Managed Redis (opcional) |
| S3 Backup (100GB) | ~$2.30/mês | Standard + lifecycle |
| CloudWatch Logs | ~$5/mês | Logs centralizados |
| **TOTAL MÍNIMO** | **~$30-75/mês** | Dependendo managed services |

---

## 🎯 CHECKLIST EXECUTIVO

### Imediato (Esta Semana)
- [ ] Certificado Let's Encrypt + Nginx HTTPS
- [ ] `.env.production` com secrets reais
- [ ] Rate limiting Nginx + Laravel
- [ ] Firewall rules (apenas 80/443/22)

### Esta Semana
- [ ] Backup offsite S3/MinIO + rclone
- [ ] Prometheus + Grafana + Loki
- [ ] Alertmanager → Telegram
- [ ] Uptime Kuma

### Próxima Sprint
- [ ] GitHub Actions CI/CD
- [ ] Testes automatizados (Pest + Cypress)
- [ ] PHPStan + Pint + Swagger
- [ ] Documentação completa

---

## 📁 ARQUIVOS DO PROJETO (Para não perder nada)

```
the-vault-clone/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/     # 12 controllers
│   │   │   ├── Middleware/      # LogAction, RoleAdmin, TwoFactorAuth
│   │   ├── Models/              # 12 models (User, BypassRule, etc)
│   │   ├── Jobs/                # ProcessBypassJob
│   │   ├── Console/Commands/    # RunBackupCommand
│   ├── database/
│   │   ├── migrations/          # 18 migrations
│   │   ├── seeders/             # DatabaseSeeder
│   ├── routes/
│   │   ├── api.php              # 46 rotas
│   │   ├── console.php          # Scheduler
│   ├── bootstrap/app.php        # Middleware aliases
│   ├── docker/php/Dockerfile    # Com mysql-client
│   ├── storage/backups/         # Backups locais
├── frontend/
│   ├── src/
│   │   ├── views/               # 16 views
│   │   ├── composables/         # useNavItems.ts
│   │   ├── stores/              # auth store
│   │   ├── router/              # 33 rotas
├── docker/
│   ├── nginx/conf.d/default.conf
│   ├── php/Dockerfile
│   ├── mysql/init/
├── keystore/                    # thevault.keystore
├── backup.sh                    # Script host
├── docker-compose.yml           # 7 services
├── .env.example
├── PLANO-PRODUCAO-100%.md       # ESTE ARQUIVO
└── SISTEMA-100-FUNCIONAL/       # Documentação completa
```

---

## 🚀 COMANDOS PARA EXECUTAR AGORA

```bash
# 1. COMMIT FINAL DO SISTEMA FUNCIONAL
cd C:\Users\Projetos\the-vault-clone
git add -A
git commit -m "checkpoint: sistema 100% funcional - pronto para hardening produção"
git tag -a v1.0-pronto-producao -m "Pronto para hardening de produção"

# 2. CRIAR .env.production
cp .env.example .env.production
# EDITAR COM SEUS SECRETS REAIS

# 3. CERTBOT HTTPS
certbot certonly --standalone -d seu-dominio.com

# 4. ATUALIZAR docker-compose.yml PARA HTTPS + .env.production
# EDITAR: nginx ports 443, volumes certs, env_file .env.production

# 5. RATE LIMITING NGINX
# Editar docker/nginx/conf.d/default.conf

# 6. FIREWALL
# ufw allow 80,443,22; ufw enable

# 6. REBUILD E DEPLOY
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# 7. VERIFICAR
curl -f https://seu-dominio.com/api/health
```

---

## 📞 PRÓXIMOS PASSOS RECOMENDADOS

### Opção A: **Endurecimento Imediato (Recomendado)**
> Foco nos 3 críticos esta semana: HTTPS + Secrets + Rate Limit + Backup Offsite
> **Tempo:** 16-24h | **Risco:** Baixo | **Resultado:** Sistema em produção segura

### Opção B: **Complete Teoricamente**
> Implementar todas as 4 fases (Segurança + Observabilidade + CI/CD + Qualidade)
> **Tempo:** 48-72h | **Resultado:** Sistema enterprise-ready

### Opção C: **Manter + Monitorar**
> Sistema funcional para uso interno. Seguir melhorias aos poucos.
> **Risco:** Médio (sem HTTPS/backup offsite)

---

**🎯 MINHA RECOMENDAÇÃO FINAL: OPÇÃO A**

> Execute os 3 itens críticos **ESTA SEMANA**:
> 1. **HTTPS + Secrets** - Sem isso, domínio real é impossível
> 2. **Rate Limiting** - Protege seu sistema de abusos
> 3. **Backup Offsite** - Garante que não perderá dados

**Após isso, o sistema está PRONTO PARA PRODUÇÃO DE FATO.**

---

**Salvo em:** `PLANO-PRODUCAO-100%.md`  
**Git Tag:** `v1.0-pronto-producao`  
**Próxima Ação:** Escolher opção e começar implementação 🚀