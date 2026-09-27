# PLANO DE IMPLEMENTAÇÃO ETAPA A ETAPA

## 📅 ORDEM DE EXECUÇÃO RECOMENDADA

### ETAPA 1: Fura Banco / Bypass Pipeline (3-4 horas)
**Objetivo:** Sistema completo de build/assinatura de APKs

**Passos:**
1.1. Atualizar Dockerfile do PHP container com Java + apktool + apksigner
1.2. Criar migration tabela bypass_jobs
1.3. Criar ProcessBypassJob (job Laravel)
1.4. Criar endpoints API (/bypass/jobs/{job}, /download)
1.5. Gerar keystore em volume Docker
1.6. Testar fluxo completo: Upload → Job → Status → Download APK assinado

**Critério de aceite:**
- ✅ Dockerfile atualizado com Java 17+apktool+apksigner
- ✅ Tabela bypass_jobs criada e migrada
- ✅ ProcessBypassJob funcional
- ✅ Endpoints API respondendo corretamente
- ✅ Keystore acessível no container
- ✅ Pelo menos 1 job completo testado (do pending ao completed)

---

### ETAPA 2: 2FA TOTP para Admin/Revendedor (2-3 horas)
**Objetivo:** Autenticação dois fatores para contas privilegiadas

**Passos:**
2.1. Instalar pacote guaviyard/laravel-totp
2.2. Configurar métodos no modelo User (totpSecret, verifyTotp)
2.3. Criar rotas /api/auth/totp-secret e /api/auth/verify-totp
2.4. Criar middleware TwoFactorAuth
2.5. Testar fluxo: Login → Gerar QR Code → Inserir código → Acesso liberado

**Critério de aceite:**
- ✅ Pacote instalado e configurado
- ✅ User model tem métodos TOTP funcionando
- ✅ Rota /api/auth/totp-secret retorna secret + QR Code
- ✅ Rota /api/auth/verify-totp valida códigos corretamente
- ✅ Middleware bloqueia admin/revendedor sem código
- ✅ Acesso restabelecido após código válido enviado

---

### ETAPA 3: Backups Automáticos (1-2 horas)
**Objetivo:** Segurança de dados com backups agendados

**Passos:**
3.1. Criar script backup.sh (mysqldump + gzip)
3.2. Testar backup manual no host
3.3. Criar Artisan command `php artisan backup:run`
3.4. Configurar scheduler no Kernel.php (diário 02:00)
3.4. Verificar retenção de backups (30 dias)

**Critério de aceite:**
- ✅ Script backup.sh criado e executável
- ✅ Backup manual gera .sql.gz em pasta dedicada
- ✅ Artisan command `php artisan backup:run` funciona
- ✅ Scheduler configurado para executar diariamente
- ✅ Pelo menos 1 backup testado e arquivo verificado

---

### ETAPA 3: Keystore Volume Docker (1 hora)
**Objetivo:** Assinatura de APKs usando volume persistente

**Passos:**
4.1. Criar diretório ./keystore no host
4.2. Gerar keystore com keytool (alias: thevault, validade 3650)
4.3. Montar volume no docker-compose.yml (./keystore:/app/keystore)
4.4. Reiniciar container the-vault-php
4.5. Verificar acesso dentro do container
4.6. Integrar no ProcessBypassJob

**Critério de aceite:**
- ✅ Volume Docker criado e montado
- ✅ Keystore acessível em /app/keystore/ dentro do container
- ✅ apksigner consegue assinar usando o keystore do volume
- ✅ Assinaturas retornam OK no apksigner verify
- ✅ ProcessBypassJob usa keystore do volume para assinatura

---

### ETAPA 4: Feature Flags Tracking (2-3 horas)
**Objetivo:** Controle de sidebar e visibilidade por role

**Passos:**
5.1. Adicionar accessors no modelo User (getFuncionalidadesAttribute)
5.2. Atualizar DatabaseSeeder populando funcionalidades por role
5.3. Integrar no frontend AppShell.vue (filtro dinâmico)
5.4. Testar sidebar por cada role (admin, revendedor, user)

**Critério de aceite:**
- ✅ Modelo User tem accessors getFuncionalidadesAttribute
- ✅ Seeders populam funcionalidades diferentes por role
- ✅ AppShell.vue filtra itens baseado em user.funcionalidades[]
- ✅ Sidebar diferente para admin vs revendedor vs user

---

## 📅 CRONOGRAMA SUGERIDO

| Semana | Foco | Deliverables |
|--------|------|--------------|
| **1** | Etapa 1 (Pipeline Bypass) | Pipeline funcionando do upload ao download assinado |
| **2** | Etapa 2 (2FA TOTP) | 2FA implementado e testado para admin/revendedor |
| **3** | Etapa 3 (Backups) | Backup automático configurado e testado |
| **4** | Etapa 4 (Keystore + Feature Flags) | Volume + sidebar role-bound completos |
| **5** | Validação Final | Todos testes passando, sistema 100% funcional |

---

## 🎯 MARCO FINAL

**Sistema 100% Funcional quando:**
- [ ] Todos os 5 gaps resolvidos
- [ ] Login 3 roles funcionando perfeitamente
- [ ] Roteamento role-based operacional
- [ ] Todos os módulos CRUD completos
- [ ] Frontend build zero erros
- [ ] Segurança implementada (RoleAdmin + 2FA)
- [ ] Dados persistidos e backups configurados
- [ ] Documentação atualizada

**Próximo passo após marco final:** 
Fase 6 - Documentação completa + deploy preparation + monitoring setup