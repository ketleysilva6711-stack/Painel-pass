🎯 OBJETIVO: Implementar 2FA TOTP para Admin/Revendedor

📋 CONTEXTUALIZAÇÃO:
- Etapa 1 (Pipeline Bypass) já validada
- Necessário: Autenticação dois fatores para contas privilegiadas
- Segurança adicional para admin/revendedor

🔧 TASKS EXECUTÁVEIS:

1. Instalar pacote TOTP
   ```
   docker compose exec php composer require guaviyard/laravel-totp
   ```

2. Configurar no modelo User (app/Models/User.php)
   ```
   use HasTOTP;

   public function setupTotp(): void
   {
       $this->totpSecret = totp_secret();
   }

   public function getTotpSecretAttribute()
   {
       return $this->totpSecret;
   }

   public function verifyTotp(string $code): bool
   {
       return totp_verify($code, $this->totpSecret);
   }
   ```

3. Criar rotas API (routes/api.php)
   ```
   Route::middleware('auth:sanctum')->group(function () {
       // ... rotas existentes ...
       
       // Rotas 2FA
       Route::get('/api/auth/totp-secret', [AuthController::class, 'totpSecret']);
       Route::post('/api/auth/verify-totp', [AuthController::class, 'verifyTOTP']);
   });
   ```

4. Adicionar métodos no AuthController
   ```
   public function totpSecret()
   {
       $user = auth()->user();
       $user->setupTotp();
       $user->save();
       
       $qrCode = 'otpauth://totp/The%20Vault%20Panel?secret=' . $user->totpSecret . '&issuer=The%20Vault';
       
       return response()->json([
           'secret' => $user->totpSecret,
           'qr_code' => $qrCode // Pode ser renderizado como imagem base64
       ]);
   }

   public function verifyTOTP(Request $request)
   {
       $valid = auth()->user()->verifyTotp($request->code);
       
       if ($valid) {
           $request->session()->put('totp_verified', true);
           return response()->json(['success' => true, 'message' => '2FA verificado']);
       }
       
       return response()->json(['success' => false, 'message' => 'Código inválido'], 401);
   }
   ```

5. Criar middleware TwoFactorAuth
   ```
   docker compose exec php php artisan make:middleware TwoFactorAuth
   ```
   No handle() do middleware:
   - Pular 2FA para rotas públicas (login, register, me)
   - Verificar se usuário é admin ou revendedor
   - Se não for, passar adiante (user normal não precisa)
   - Se for admin/revendedor, verificar session['totp_verified']
   - Se não verificado, verificar POST totp_code
   - Verificar com user->verifyTotp($request->totp_code)
   - Se válido, session['totp_verified'] = true
   - Se inválido, retornar 401 com erro

   Salvar em: app/Http/Middleware/TwoFactorAuth.php

6. Aplicar middleware nas rotas admin
   No routes/api.php:
   ```
   Route::middleware('auth:sanctum', 'twofactorauth')->group(function () {
       Route::get('/admin/logs', [AdminController::class, 'logs']);
       Route::put('/admin/users/{user}', [AdminController::class, 'update']);
       // ... demais rotas admin
   });
   ```

7. Testar fluxo 2FA
   No PowerShell:
   ```powershell
   # 1. Gerar secret e QR Code
   $secretData = Invoke-RestMethod -Method Get -Uri 'http://localhost:8080/api/auth/totp-secret' -Headers @{Authorization="Bearer $adminToken"}
   
   # 2. (No navegador do usuário) Escanear QR Code no Google Authenticator/Appy
   #    Ou usar o secret: $secretData.secret
   
   # 3. Login admin e tentar acessar rota protegida SEM código
   #    Esperado: 401 {"error":"Autenticação de dois fatores necessária", "action":"verify_totp"}
   
   # 4. Fazer POST com código TOTP
   $code = (Get-Date).Second % 1000  # Código de exemplo - usar gerador real
   $result = Invoke-RestMethod -Method Post -Uri 'http://localhost:8080/api/auth/verify-totp' -Body '{"code":"'.$code.'"}' -Headers @{Authorization="Bearer $adminToken"}
   Write-Host "Resultado: $($result.success)"
   ```

✅ CRITÉRIO DE ACEITE ETAPA 2:
- [ ] Pacote guaviyard/laravel-totp instalado
- [ ] User model com métodos totpSecret/verifyTotp funcionando
- [ ] Rota /api/auth/totp-secret retorna secret + QR Code
- [ ] Rota /api/auth/verify-totp valida códigos corretamente
- [ ] Middleware TwoFactorAuth bloqueando admin/revendedor sem código (401)
- [ ] Acesso liberado após código TOTP válido enviado
- [ ] Session['totp_verified'] marcado após verificação bem-sucedida