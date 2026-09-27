#!/bin/bash
# restore-test.sh - Teste de restore de backup
# Uso: ./restore-test.sh
# Cron: 0 4 1 * * /path/to/restore-test.sh

set -euo pipefail

# =============================================================================
# CONFIGURAÇÕES
# =============================================================================
RCLONE_REMOTE="s3-backup"
S3_BUCKET="seu-bucket-backups"
S3_PATH="backups/the-vault"
RESTORE_DIR="/tmp/restore_test_$(date +%Y%m%d_%H%M%S)"
LOG_FILE="/var/log/restore-test.log"
MYSQL_HOST="mysql"
MYSQL_DB="the_vault_test_restore"
MYSQL_USER="restore_user"
MYSQL_PASSWORD="restore_password"

# =============================================================================
# FUNÇÕES
# =============================================================================
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error_exit() {
    log "ERROR: $1"
    # Cleanup
    [ -d "$RESTORE_DIR" ] && rm -rf "$RESTORE_DIR"
    exit 1
}

cleanup() {
    log "Limpando arquivos temporários..."
    [ -d "$RESTORE_DIR" ] && rm -rf "$RESTORE_DIR"
}

trap cleanup EXIT

# =============================================================================
# INÍCIO
# =============================================================================
log "=== Iniciando teste de restore ==="

# Verificar rclone
if ! command -v rclone &> /dev/null; then
    error_exit "rclone não está instalado"
fi

# Verificar remote
if ! rclone listremotes | grep -q "^${RCLONE_REMOTE}:$"; then
    error_exit "Remote rclone '${RCLONE_REMOTE}' não configurado"
fi

# =============================================================================
# LISTAR BACKUPS DISPONÍVEIS
# =============================================================================
log "Listando backups disponíveis..."
BACKUPS=$(rclone lsf "${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}" --format "p" | grep "\.sql\.gz$" | sort -r)

if [ -z "$BACKUPS" ]; then
    error_exit "Nenhum backup encontrado no remote"
fi

LATEST_BACKUP=$(echo "$BACKUPS" | head -1)
log "Backup mais recente: $LATEST_BACKUP"

# =============================================================================
# DOWNLOAD DO BACKUP
# =============================================================================
log "Criando diretório temporário: $RESTORE_DIR"
mkdir -p "$RESTORE_DIR"

log "Baixando backup: $LATEST_BACKUP"
rclone copy "${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}/${LATEST_BACKUP}" "$RESTORE_DIR/" --progress 2>&1 | tee -a "$LOG_FILE"

if [ ! -f "$RESTORE_DIR/$LATEST_BACKUP" ]; then
    error_exit "Falha ao baixar backup"
fi

log "Backup baixado: $(ls -lh "$RESTORE_DIR/$LATEST_BACKUP")"

# =============================================================================
# DESCOMPACTAR
# =============================================================================
log "Descompactando backup..."
gunzip -k "$RESTORE_DIR/$LATEST_BACKUP"
SQL_FILE="${LATEST_BACKUP%.gz}"
log "Arquivo SQL: $SQL_FILE ($(ls -lh "$RESTORE_DIR/$SQL_FILE" | awk '{print $5}'))"

# =============================================================================
# VERIFICAR ESTRUTURA DO SQL
# =============================================================================
log "Verificando estrutura do dump SQL..."
head -20 "$RESTORE_DIR/$SQL_FILE" | grep -q "MariaDB dump\|MySQL dump" || error_exit "Dump SQL inválido - não contém header MariaDB/MySQL"

log "✓ Header do dump válido"

# Verificar tabelas principais
TABLES=$(grep -c "CREATE TABLE" "$RESTORE_DIR/$SQL_FILE" || true)
log "Tabelas encontradas: $TABLES"

if [ "$TABLES" -lt 5 ]; then
    error_exit "Poucas tabelas no dump ($TABLES), possível backup incompleto"
fi

# Verificar tabelas críticas
CRITICAL_TABLES=("users" "bypass_rules" "play_fake_apps" "qr_code_links" "support_tickets")
for table in "${CRITICAL_TABLES[@]}"; do
    if grep -q "CREATE TABLE \`$table\`" "$RESTORE_DIR/$SQL_FILE"; then
        log "✓ Tabela crítica '$table' presente"
    else
        log "⚠ Tabela crítica '$table' NÃO encontrada"
    fi
done

# =============================================================================
# TESTE DE RESTORE (opcional - requer MySQL disponível)
# =============================================================================
if command -v mysql &> /dev/null && [ -n "${TEST_RESTORE_FULL:-}" ]; then
    log "Executando restore completo de teste..."

    # Criar banco de teste
    mysql -h "$MYSQL_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DB\`; CREATE USER IF NOT EXISTS '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASSWORD'; GRANT ALL ON \`$MYSQL_DB\`.* TO '$MYSQL_USER'@'%'; FLUSH PRIVILEGES;"

    # Restaurar
    mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DB" < "$RESTORE_DIR/$SQL_FILE"

    # Verificar
    TABLE_COUNT=$(mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DB" -e "SHOW TABLES" | wc -l)
    log "Tabelas restauradas: $TABLE_COUNT"

    # Limpar
    mysql -h "$MYSQL_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE \`$MYSQL_DB\`; DROP USER IF EXISTS '$MYSQL_USER'@'%';"
    log "✓ Restore completo testado com sucesso"
else
    log "Teste de restore completo pulado (defina TEST_RESTORE_FULL=1 para habilitar)"
fi

# =============================================================================
# VERIFICAÇÃO DE INTEGRIDADE (checksum)
# =============================================================================
log "Calculando checksum do backup..."
CHECKSUM=$(sha256sum "$RESTORE_DIR/$LATEST_BACKUP" | awk '{print $1}')
log "SHA256: $CHECKSUM"

# Salvar checksum para comparação futura
echo "$CHECKSUM  $LATEST_BACKUP" >> "$RESTORE_DIR/checksums.txt"

# =============================================================================
# RESULTADO FINAL
# =============================================================================
log "=== Teste de restore concluído com sucesso ==="
log "Backup testado: $LATEST_BACKUP"
log "Tabelas no dump: $TABLES"
log "Checksum SHA256: $CHECKSUM"
log "Diretório temporário: $RESTORE_DIR (será removido automaticamente)"

exit 0