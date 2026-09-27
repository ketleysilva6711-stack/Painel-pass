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
echo "Iniciando backup do banco de dados..."
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