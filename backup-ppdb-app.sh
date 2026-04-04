#!/bin/bash

# ==============================================
# PPDB V3 APP-ONLY BACKUP SCRIPT
# ==============================================
# Backup ini hanya untuk aplikasi Laravel, tanpa
# file dokumen upload pendaftar agar ukuran kecil.
#
# Jalankan:
#   chmod +x backup-ppdb-app.sh
#   ./backup-ppdb-app.sh
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
TIMESTAMP="$(date '+%Y-%m-%d_%H-%M-%S')"
BACKUP_MODE="${BACKUP_MODE:-slim}"
ARCHIVE_NAME="ppdb-app-only-${TIMESTAMP}.tar.gz"
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

EXCLUDES=(
    "--exclude=./.git"
    "--exclude=./node_modules"
    "--exclude=./storage/app/public/dokumen"
    "--exclude=./storage/app/public/dokumen_pendaftar"
    "--exclude=./storage/app/public/kelulusan"
    "--exclude=./storage/app/backups"
    "--exclude=./storage/app/temp"
    "--exclude=./storage/app/private/temp"
    "--exclude=./storage/framework/cache"
    "--exclude=./storage/framework/sessions"
    "--exclude=./storage/framework/testing"
    "--exclude=./storage/framework/views"
    "--exclude=./storage/logs"
    "--exclude=./bootstrap/cache/*"
)

if [ "$BACKUP_MODE" = "slim" ]; then
    EXCLUDES+=("--exclude=./vendor")
fi

echo ""
echo -e "${GREEN}=============================================="
echo "   PPDB V3 - BACKUP APLIKASI SAJA"
echo "=============================================="
echo -e "${NC}"

if [ ! -d "$APP_DIR" ]; then
    print_error "Direktori aplikasi tidak ditemukan: $APP_DIR"
    exit 1
fi

mkdir -p "$BACKUP_DIR"

print_status "Direktori aplikasi : $APP_DIR"
print_status "Direktori backup   : $BACKUP_DIR"
print_status "Nama arsip         : $ARCHIVE_NAME"
print_status "Mode backup        : $BACKUP_MODE"
print_warning "Dokumen upload, cache, log, dan backup lama tidak akan ikut disimpan."
if [ "$BACKUP_MODE" = "slim" ]; then
    print_warning "Mode slim aktif: folder vendor tidak ikut dibackup."
fi

tar \
    "${EXCLUDES[@]}" \
    --transform 's,^\./,ppdb/,' \
    -czf "$ARCHIVE_PATH" \
    -C "$APP_DIR" .

if [ ! -f "$ARCHIVE_PATH" ]; then
    print_error "Backup gagal dibuat."
    exit 1
fi

ARCHIVE_SIZE="$(du -h "$ARCHIVE_PATH" | awk '{print $1}')"

echo ""
print_success "Backup aplikasi berhasil dibuat."
print_status "File backup : $ARCHIVE_PATH"
print_status "Ukuran      : $ARCHIVE_SIZE"
print_status "Isi arsip   : folder root 'ppdb/'"
echo ""
print_warning "Backup ini tidak menyertakan dokumen upload pendaftar."
print_warning "Untuk rollback total, tetap lakukan backup database terpisah."
if [ "$BACKUP_MODE" = "slim" ]; then
    print_warning "Saat restore mode slim, jalankan composer install di hosting."
fi
echo ""
