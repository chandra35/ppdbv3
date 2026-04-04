#!/bin/bash

# ==============================================
# PPDB V3 FULL BACKUP RUNNER
# ==============================================
# Menjalankan backup aplikasi dan database
# sekaligus ke folder /home/.../backup
#
# Jalankan:
#   chmod +x backup-ppdb-all.sh
#   ./backup-ppdb-all.sh
# ==============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

APP_SCRIPT="$SCRIPT_DIR/backup-ppdb-app.sh"
DB_SCRIPT="$SCRIPT_DIR/backup-ppdb-db.sh"

echo ""
echo -e "${GREEN}=============================================="
echo "   PPDB V3 - BACKUP APP + DATABASE"
echo "=============================================="
echo -e "${NC}"

if [ ! -x "$APP_SCRIPT" ]; then
    print_error "Script backup aplikasi tidak ditemukan/ belum executable: $APP_SCRIPT"
    exit 1
fi

if [ ! -x "$DB_SCRIPT" ]; then
    print_error "Script backup database tidak ditemukan/ belum executable: $DB_SCRIPT"
    exit 1
fi

print_status "Menjalankan backup aplikasi..."
"$APP_SCRIPT"

echo ""
print_status "Menjalankan backup database..."
"$DB_SCRIPT"

echo ""
print_success "Backup aplikasi dan database selesai."
print_status "Periksa hasil file di folder backup."
echo ""
