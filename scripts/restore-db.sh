#!/usr/bin/env bash
# =============================================================================
# Puretec Pure POS - Safe Database Restoration Script
#
# Usage:
#   bash scripts/restore-db.sh <PATH_TO_BACKUP_FILE>
# =============================================================================

set -euo pipefail

BACKUP_FILE="${1:-}"

if [ -z "${BACKUP_FILE}" ] || [ ! -f "${BACKUP_FILE}" ]; then
  echo "Error: Backup file does not exist or path not provided." >&2
  echo "Usage: bash scripts/restore-db.sh <PATH_TO_BACKUP_FILE>" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

DB_NAME="$(grep '^DB_DATABASE=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')"
DB_USER="$(grep '^DB_USERNAME=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')"
DB_PASS="$(grep '^DB_PASSWORD=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ')"
DB_HOST="$(grep '^DB_HOST=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ' || echo '127.0.0.1')"
DB_PORT="$(grep '^DB_PORT=' "${APP_DIR}/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"'\'' ' || echo '3306')"

echo "============================================================"
echo "WARNING: Restoring will overwrite existing data in '${DB_NAME}'"
echo "Backup File: ${BACKUP_FILE}"
echo "============================================================"
read -p "Type 'RESTORE' to proceed: " CONFIRM

if [ "${CONFIRM}" != "RESTORE" ]; then
  echo "Restoration aborted."
  exit 0
fi

echo "Restoring database..."

AUTH_FLAG=""
if [ -n "${DB_PASS}" ]; then
  AUTH_FLAG="-p${DB_PASS}"
fi

if [[ "${BACKUP_FILE}" == *.gz ]]; then
  gunzip -c "${BACKUP_FILE}" | mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" ${AUTH_FLAG} "${DB_NAME}"
else
  mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" ${AUTH_FLAG} "${DB_NAME}" < "${BACKUP_FILE}"
fi

echo "Database restoration completed successfully!"

