#!/usr/bin/env bash
# =============================================================================
# Puretec Pure POS - Safe Automated MySQL Backup Script
#
# Suitable for automated daily cron jobs on VPS or Linux servers.
# Usage:
#   bash scripts/backup-db.sh [DATABASE_NAME] [RETENTION_DAYS]
# =============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="${APP_DIR}/storage/app/backups"

# Read DB configuration from .env if available
DB_NAME="${1:-$(grep '^DB_DATABASE=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')}"
DB_USER="$(grep '^DB_USERNAME=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')"
DB_PASS="$(grep '^DB_PASSWORD=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')"
DB_HOST="$(grep '^DB_HOST=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ' || echo '127.0.0.1')"
DB_PORT="$(grep '^DB_PORT=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ' || echo '3306')"

DB_NAME="${DB_NAME:-purepos}"
DB_USER="${DB_USER:-root}"
RETENTION_DAYS="${2:-7}"

mkdir -p "${BACKUP_DIR}"

TIMESTAMP=$(date +"%Y-%m-%d_%H%M%S")
FILENAME="${DB_NAME}_${TIMESTAMP}.sql.gz"
FILEPATH="${BACKUP_DIR}/${FILENAME}"

echo "=== Starting MySQL Backup for '${DB_NAME}' ==="
echo "Target: ${FILEPATH}"

# Execute mysqldump with non-locking, crash-consistent transactional flags
if [ -n "${DB_PASS}" ]; then
  mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" \
    --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 \
    "${DB_NAME}" | gzip -9 > "${FILEPATH}"
else
  mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" \
    --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 \
    "${DB_NAME}" | gzip -9 > "${FILEPATH}"
fi

FILESIZE=$(du -h "${FILEPATH}" | cut -f1)
echo "Backup successful! Size: ${FILESIZE}"

# Prune older backups
echo "Pruning backups older than ${RETENTION_DAYS} days..."
find "${BACKUP_DIR}" -type f -name "${DB_NAME}_*.sql.gz" -mtime +"${RETENTION_DAYS}" -delete || true

echo "=== Backup procedure completed ==="

