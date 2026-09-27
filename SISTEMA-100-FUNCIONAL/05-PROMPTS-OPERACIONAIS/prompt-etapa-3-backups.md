🎯 OBJETIVO: Implementar Backups Automáticos do Banco de Dados

📋 CONTEXTUALIZAÇÃO:
- Sistema já com modules core funcionando
- Necessário: Segurança de dados com backups agendados
- Recuperação de desastre

🔧 TASKS EXECUTÁVEIS:

1. Criar script de backup no host
   ```
   mkdir ./backup
   ```
   ```
   # ./backup.sh
   #!/bin/bash
   
   # Configurações
   DB_CONTAINER="the-vault-mysql"
   DB_NAME="the_vault"
   DATE=$(date +%Y%m%d_%H%M%S)
   BACKUP_DIR="./backup"
   RETENTION_DAYS=30
   
   # Criar diretório se não existir
   mkdir -p "$BACKUP_DIR"
   
   # Fazer backup do MySQL
   docker exec "$DB_CONTAINER" mysqldump -u root "$DB_NAME" > "$BACKUP_DIR/the_vault_$DATE.sql" 2>&1
   
   # Verificar se backup ok
   if [ $? -eq 0 ]; then
       # Comprimir
       gzip "$BACKUP_DIR/the_vault_$DATE.sql"
       echo "Backup criado: the_vault_$DATE.sql.gz"
       
       # Remover antigos
       find "$BACKUP_DIR" -name "the_vault_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete
       echo "Backups antigos removidos (retenção: $RETENTION_DAYS dias)"
   else
       echo "ERRO: Falha ao fazer backup"
       exit 1
   fi
   ```
   
   Tornar executável: chmod +x ./backup.sh

2. Testar backup manual
   No PowerShell: `.\backup.sh`
   Verificar: `Get-ChildItem ./backup/*.gz | Select-Object -Last 1`
   Esperado: Arquivo `the_vault_20260927_XXXXXX.sql.gz`

3. Criar Artisan command
   ```
   docker compose exec php php artisan make:command RunBackupCommand
   ```
   No app/Console/Commands/RunBackupCommand.php:
   ```
   protected $signature = 'backup:run';
   protected $description = 'Executar backup automático do banco de dados';
   
   public function handle(): void
   {
       $this->info('Iniciando backup automático...');
       $output = shell_exec('./backup.sh 2>&1');
       $this->info('Backup concluído.');
       $this->info($output);
   }
   ```

4. Configurar scheduler no Kernel.php
   ```
   protected function schedule(Schedule $schedule)
   {
       $schedule->command('backup:run')->at('02:00');
   }
   ```
   
   Verificar scheduler rodando:
   No host: `docker compose exec the-vault-scheduler bash -c "php /var/www/html/artisan schedule:run --quiet"`

5. Validar backup
   - Verificar arquivo gerado na pasta ./backup/
   - Testar descompress: `gunzip -k ./backup/the_vault_20260927_020000.sql.gz`
   - Verificar conteúdo: `head -20 ./backup/the_vault_20260927_020000.sql`

✅ CRITÉRIO DE ACEITE ETAPA 3:
- [ ] Script backup.sh criado no host e executável
- [ ] Backup manual gera .sql.gz em pasta ./backup/
- [ ] Artisan command `php artisan backup:run` executando o script
- [ ] Scheduler configurado para 02:00 diariamente
- [ ] Pelo menos 1 backup testado com arquivo verificado (estrutura SQL OK)
- [ ] Retenção de 30 dias funcionando (backups antigos removidos automaticamente)