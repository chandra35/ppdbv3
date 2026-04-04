#!/bin/bash

# ==============================================
# PPDB V3 DATABASE BACKUP SCRIPT
# ==============================================
# Backup database Laravel PPDB ke file .sql.gz
#
# Jalankan:
#   chmod +x backup-ppdb-db.sh
#   ./backup-ppdb-db.sh
# ==============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SCRIPT_DIR/artisan" ]; then
    DEFAULT_APP_DIR="$SCRIPT_DIR"
elif [ -f "$SCRIPT_DIR/ppdb.man1metro.sch.id/artisan" ]; then
    DEFAULT_APP_DIR="$SCRIPT_DIR/ppdb.man1metro.sch.id"
else
    DEFAULT_APP_DIR="$SCRIPT_DIR"
fi

APP_DIR="${APP_DIR:-$DEFAULT_APP_DIR}"
ROOT_HOME="$(cd "$APP_DIR/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$ROOT_HOME/backup}"
ENV_FILE="${ENV_FILE:-$APP_DIR/.env}"
TIMESTAMP="$(date '+%Y-%m-%d_%H-%M-%S')"
ARCHIVE_NAME="ppdb-db-${TIMESTAMP}.sql.gz"
ARCHIVE_PATH="${BACKUP_DIR}/${ARCHIVE_NAME}"

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

read_env() {
    local key="$1"
    local value
    value="$(grep -E "^${key}=" "$ENV_FILE" | tail -n 1 | cut -d '=' -f2-)"
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    echo "$value"
}

echo ""
echo -e "${GREEN}=============================================="
echo "   PPDB V3 - BACKUP DATABASE"
echo "=============================================="
echo -e "${NC}"

if [ ! -f "$ENV_FILE" ]; then
    print_error "File .env tidak ditemukan: $ENV_FILE"
    exit 1
fi

mkdir -p "$BACKUP_DIR"

DB_CONNECTION="${DB_CONNECTION:-$(read_env DB_CONNECTION)}"
DB_HOST="${DB_HOST:-$(read_env DB_HOST)}"
DB_PORT="${DB_PORT:-$(read_env DB_PORT)}"
DB_DATABASE="${DB_DATABASE:-$(read_env DB_DATABASE)}"
DB_USERNAME="${DB_USERNAME:-$(read_env DB_USERNAME)}"
DB_PASSWORD="${DB_PASSWORD:-$(read_env DB_PASSWORD)}"

if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    print_error "Konfigurasi database tidak lengkap di file .env"
    exit 1
fi

if command -v mysqldump >/dev/null 2>&1; then
    DUMP_CMD="mysqldump"
elif command -v mariadb-dump >/dev/null 2>&1; then
    DUMP_CMD="mariadb-dump"
else
    print_error "mysqldump/mariadb-dump tidak tersedia di server"
    exit 1
fi

print_status "Direktori aplikasi : $APP_DIR"
print_status "File env           : $ENV_FILE"
print_status "Direktori backup   : $BACKUP_DIR"
print_status "Nama arsip         : $ARCHIVE_NAME"
print_status "Database           : $DB_DATABASE"
print_status "Host               : ${DB_HOST:-127.0.0.1}:${DB_PORT:-3306}"
print_status "Dump command       : $DUMP_CMD"

MYSQL_PWD="$DB_PASSWORD" "$DUMP_CMD" \
    --host="${DB_HOST:-127.0.0.1}" \
    --port="${DB_PORT:-3306}" \
    --user="$DB_USERNAME" \
    --single-transaction \
    --quick \
    --no-tablespaces \
    --routines \
    --triggers \
    --default-character-set=utf8mb4 \
    "$DB_DATABASE" | gzip -9 > "$ARCHIVE_PATH"

if [ ! -f "$ARCHIVE_PATH" ]; then
    print_error "Backup database gagal dibuat."
    exit 1
fi

ARCHIVE_SIZE="$(du -h "$ARCHIVE_PATH" | awk '{print $1}')"

echo ""
print_success "Backup database berhasil dibuat."
print_status "File backup : $ARCHIVE_PATH"
print_status "Ukuran      : $ARCHIVE_SIZE"
echo ""
