🎯 OBJETIVO: Configurar Keystore Volume Docker para Assinatura de APKs

📋 CONTEXTUALIZAÇÃO:
- Etapa 1 (Pipeline Bypass) em andamento
- Necessário: Volume persistente para keystore (nunca no git)
- Assinatura de APKs no pipeline Build

🔧 TASKS EXECUTÁVEIS:

1. Criar diretório e gerar keystore no host
   ```
   mkdir ./keystore
   keytool -genkey -keystore ./keystore/thevault.keystore -alias thevault -validity 3650 -keyalg RSA -keysize 2048
   ```
   Seguir prompts:
   - Enter keystore password: vault2024
   - Re-enter: vault2024
   - First/Last name: The Vault Panel
   - Organizational Unit: Security
   - Organization: The Vault
   - City: São Paulo
   - State: São Paulo
   - Country Code: BR
   
   Verificar: `keytool -list -keystore ./keystore/thevault.keystore`

2. Montar volume no docker-compose.yml
   No arquivo docker-compose.yml, no service the-vault-php, adicionar:
   ```
   volumes:
     - ./keystore:/app/keystore:ro
   ```
   (ou `rw` se precisar escrever dentro, mas `ro` é mais seguro)
   
   Reiniciar container: `docker compose restart the-vault-php`

3. Verificar acesso dentro do container
   ```
   docker compose exec the-vault-php ls -la /app/keystore/
   docker compose exec the-vault-php keytool -list -keystore /app/keystore/thevault.keystore
   ```
   
   Esperado: Dever listar o alias "thevault" com certificado válido

4. Integrar no ProcessBypassJob
   No app/Jobs/ProcessBypassJob.php, no método handle():
   ```
   // Caminho do keystore (volume Docker)
   $keystorePath = '/app/keystore/thevault.keystore';
   
   // Ou usando caminho relativo (se volume mapeado como writable)
   // $keystorePath = storage_path('app/keystore/thevault.keystore');
   ```
   
   No comando apksigner:
   ```
   $signResult = shell_exec("apksigner sign --out {$outputApk}.signed --ks {$keystorePath} --ks-key-alias thevault --ks-key-size 2048 2>&1");
   ```

5. Testar assinatura com volume
   No container PHP:
   ```
   apksigner sign --out /tmp/teste.signed --ks /app/keystore/thevault.keystore --ks-key-alias thevault --ks-key-size 2048 /tmp/minha_app.apk
   ```
   
   Verificar assinatura:
   ```
   apksigner verify /tmp/teste.signed
   ```
   
   Esperado: `Verified signing info: ...`

✅ CRITÉRIO DE ACEITE ETAPA 4:
- [ ] Diretório ./keystore/ criado no host
- [ ] Keystore gerado com keytool (alias: thevault)
- [ ] Volume Docker montado no docker-compose.yml (./keystore:/app/keystore)
- [ ] Container reiniciado e keystore acessível em /app/keystore/
- [ ] apksigner verify retornando OK no arquivo assinado
- [ ] ProcessBypassJob usando keystore do volume para assinatura
- [ ] APKs assinados são instaláveis (validar assinatura)