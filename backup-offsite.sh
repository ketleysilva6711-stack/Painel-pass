#!/bin/bash
# backup-offsite.sh - Backup offsite para S3/MinIO
# Uso: ./backup-offsite.sh
# Cron: 0 3 * * * /path/to/backup-offsite.sh

set -euo pipefail

# =============================================================================
# CONFIGURAÇÕES
# =============================================================================
BACKUP_DIR="/var/www/html/storage/backups"
RCLONE_REMOTE="s3-backup"
S3_BUCKET="seu-bucket-backups"
S3_PATH="backups/the-vault"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/backup-offsite.log"

# =============================================================================
# FUNÇÕES
# =============================================================================
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error_exit() {
    log "ERROR: $1"
    exit 1
}

# =============================================================================
# VERIFICAÇÕES INICIAIS
# =============================================================================
log "=== Iniciando backup offsite ==="

# Verificar se rclone está instalado
if ! command -v rclone &> /dev/null; then
    error_exit "rclone não está instalado"
fi

# Verificar se config existe
if ! rclone listremotes | grep -q "^${RCLONE_REMOTE}:$"; then
    error_exit "Remote rclone '${RCLONE_REMOTE}' não configurado"
fi

# Verificar diretório de backup
if [ ! -d "$BACKUP_DIR" ]; then
    error_exit "Diretório de backup não existe: $BACKUP_DIR"
fi

# =============================================================================
# SINCRONIZAÇÃO
# =============================================================================
log "Iniciando sincronização para ${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}"

# Sincronizar apenas arquivos .gz (backups comprimidos)
rclone sync \
    "$BACKUP_DIR" \
    "${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}" \
    --include "*.sql.gz" \
    --progress \
    --stats 30s \
    --stats-file-name-length 0 \
    --transfers 4 \
    --checkers 8 \
    --retries 3 \
    --low-level-retries 10 \
    --retries-sleep 5s \
    2>&1 | tee -a "$LOG_FILE"

SYNC_EXIT_CODE=${PIPESTATUS[0]}

if [ $SYNC_EXIT_CODE -eq 0 ]; then
    log "Sincronização concluída com sucesso"
else
    error_exit "Falha na sincronização (código: $SYNC_EXIT_CODE)"
fi

# =============================================================================
# LIMPEZA LOCAL (manter apenas últimos 7 dias localmente)
# =============================================================================
log "Removendo backups locais antigos (> 7 dias)..."
find "$BACKUP_DIR" -name "the_vault_*.sql.gz" -type f -mtime +7 -delete
DELETED_LOCAL=$(find "$BACKUP_DIR" -name "the_vault_*.sql.gz" -type f -mtime +7 | wc -l)
log "Removidos $DELETED_LOCAL arquivos locais antigos"

# =============================================================================
# LIMPEZA REMOTA (S3 lifecycle ou manual)
# =============================================================================
log "Verificando retenção remota (> ${RETENTION_DAYS} dias)..."
# Nota: Preferencialmente configure lifecycle policy no S3/MinIO
# rclone delete --min-age ${RETENTION_DAYS}d "${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}"

# =============================================================================
# VERIFICAÇÃO FINAL
# =============================================================================
REMOTE_COUNT=$(rclone lsf "${RCLONE_REMOTE}:${S3_BUCKET}/${S3_PATH}" --format "p" | wc -l)
LOCAL_COUNT=$(find "$BACKUP_DIR" -name "the_vault_*.sql.gz" -type f | wc -l)

log "=== Backup offsite concluído ==="
log "Arquivos locais: $LOCAL_COUNT"
log "Arquivos remotos: $REMOTE_COUNT"
log "Log salvo em: $LOG_FILE"

exit 0