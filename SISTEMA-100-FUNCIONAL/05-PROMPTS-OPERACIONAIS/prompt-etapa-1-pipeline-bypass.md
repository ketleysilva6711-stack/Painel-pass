🎯 OBJETIVO: Implementar Fura Banco / Bypass Pipeline Completo

📋 CONTEXTUALIZAÇÃO:
- Projeto: the-vault-clone (Laravel + Vue 3)
- 7 containers Docker rodando
- Core modules já funcionando (Consulta, Ranking, Play Fake basic)
- Gap: Pipeline build/assinatura de APKs não existe

🔧 TASKS EXECUTÁVEIS:

1. Atualizar Dockerfile do PHP container
   ```
   RUN apt-get update && apt-get install -y \
       openjdk-17-jdk \
       apktool \
       apksigner \
       && rm -rf /var/lib/apt/lists/*
   ```
   Testar: java -version, apktool --version, apksigner --version

2. Criar migration tabela bypass_jobs
   ```
   docker compose exec php php artisan make:migration create_bypass_jobs_table
   ```
   Fields necessários: id, user_id, apk_path, output_path, status, progress, phase, log, target_package, apk_filename, timestamps

   Executar: docker compose exec php php artisan migrate --force

3. Criar ProcessBypassJob
   ```
   docker compose exec php php artisan make:job ProcessBypassJob
   ```
   No handle() do job:
   - Etapa 1: apktool d -f $apk_path -o $decompiledDir
   - Etapa 2: Aplicar patches (baseado em target_package)
   - Etapa 3: apktool b $outputDir
   - Etapa 4: apksigner sign --out $output.apks --ks $keystorePath --ks-key-alias thevault
   - Etapa 5: Atualizar BypassRule com status 'completed', output_path, progress 100

   Salvar em: app/Jobs/ProcessBypassJob.php

4. Criar BypassJobController
   ```
   docker compose exec php php artisan make:controller BypassJobController --api
   ```
   Métodos:
   - show($job): Retornar status, progress, phase, log, output_path
   - download($job): Retornar response()->download($outputPath, filename)

   Salvar em: app/Http/Controllers/BypassJobController.php

5. Adicionar rotas API
   No routes/api.php:
   ```
   Route::middleware('auth:sanctum')->get('/bypass/jobs/{job}', [BypassJobController::class, 'show']);
   Route::middleware('auth:sanctum')->get('/bypass/jobs/{job}/download', [BypassJobController::class, 'download']);
   ```

6. Gerar keystore em volume Docker
   No host:
   ```
   mkdir ./keystore
   keytool -genkey -keystore ./keystore/thevault.keystore -alias thevault -validity 3650 -keyalg RSA -keysize 2048
   ```
   No docker-compose.yml adicionar volume: ./keystore:/app/keystore
   Reiniciar: docker compose restart the-vault-php

6. Testar fluxo completo
   No PowerShell:
   ```powershell
   # 1. Login admin
   $LOGIN = Invoke-RestMethod -Method Post -Uri 'http://localhost:8080/api/auth/login' -Body '{"email":"admin@teste","password":"password"}' -ContentType 'application/json'
   $TOKEN = $LOGIN.token

   # 2. Criar regra de bypass (precisa ter APK cadastrado na tabela play_fake_apps)
   $rule = Invoke-RestMethod -Method Post -Uri 'http://localhost:8080/api/bypass-rules' -Body '{"target_package":"com.example.app","apk_id":1}' -Headers @{Authorization="Bearer $TOKEN"}

   # 3. Polling status
   $status = Invoke-RestMethod -Uri "http://localhost:8080/api/bypass/jobs/$($rule.id)" -Headers @{Authorization="Bearer $TOKEN"}
   Write-Host "Status: $($status.status) Progress: $($status.progress)%"

   # 4. Quando completed, fazer download
   if ($status.status -eq "completed") {
       Invoke-RestMethod -Method Get -Uri "http://localhost:8080/api/bypass/jobs/$($rule.id)/download" -Headers @{Authorization="Bearer $TOKEN"} -OutFile "C:\bypass_assinado.apk"
       Write-Host "Download concluído!"
   }
   ```

✅ CRITÉRIO DE ACEITE ETAPA 1:
- [ ] Dockerfile atualizado com Java + apktool + apksigner
- [ ] Tabela bypass_jobs criada e migrada
- [ ] ProcessBypassJob criado e salvo em app/Jobs/
- [ ] Endpoints API funcionando (show + download)
- [ ] Keystore em volume Docker acessível dentro do container
- [ ] Pelo menos 1 job completo testado (do pending ao completed)
- [ ] APK baixado é assinado e instalável