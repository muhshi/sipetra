#!/bin/bash
# ==========================================================
#  deploy.sh - Script Deploy Cepat & Cerdas untuk SIPETRA
#  Penggunaan: 
#    bash deploy.sh                (Deploy cerdas: build hanya jika diperlukan)
#    bash deploy.sh --build        (Paksa rebuild image Docker)
#    bash deploy.sh --build-assets (Paksa compile asset Vite frontend)
#    bash deploy.sh --all          (Paksa rebuild image Docker & asset frontend)
# ==========================================================

set -e  # Berhenti jika ada error

# Direktori aplikasi
if [ -d "$HOME/apps/sipetra" ]; then
    APP_DIR="$HOME/apps/sipetra"
else
    APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi

CONTAINER_NAME=sipetra-app
BRANCH=main
FORCE_BUILD_DOCKER=false
FORCE_BUILD_ASSETS=false

# Cek argumen CLI
for arg in "$@"; do
    case "$arg" in
        --build|-b)
            FORCE_BUILD_DOCKER=true
            ;;
        --build-assets|-a)
            FORCE_BUILD_ASSETS=true
            ;;
        --all)
            FORCE_BUILD_DOCKER=true
            FORCE_BUILD_ASSETS=true
            ;;
        --help|-h)
            echo ""
            echo "Penggunaan: bash deploy.sh [OPSI]"
            echo "Opsi:"
            echo "  (tanpa opsi)          Deploy cerdas (hanya build jika diperlukan)"
            echo "  --build, -b           Paksa rebuild Docker image"
            echo "  --build-assets, -a    Paksa rebuild asset frontend (Vite)"
            echo "  --all                 Paksa rebuild Docker image dan frontend"
            echo ""
            exit 0
            ;;
    esac
done

echo ""
echo "=========================================="
echo "  🚀 Memulai Deploy SIPETRA..."
echo "=========================================="
echo ""

# 1. Masuk ke direktori aplikasi
cd "$APP_DIR"
echo "📂 Direktori: $(pwd)"

# Simpan commit lama sebelum pull untuk deteksi file yang berubah
OLD_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "")

# 2. Fetch & Reset ke kode terbaru dari GitHub
echo ""
echo "📥 [1/7] Mengambil kode terbaru dari GitHub..."
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"
echo "   ✅ Kode terbaru berhasil ditarik."

NEW_COMMIT=$(git rev-parse HEAD)

# Dapatkan daftar file yang berubah jika ada commit sebelumnya
CHANGED_FILES=""
if [ -n "$OLD_COMMIT" ] && [ "$OLD_COMMIT" != "$NEW_COMMIT" ]; then
    CHANGED_FILES=$(git diff --name-only "$OLD_COMMIT" "$NEW_COMMIT")
fi

# ---------------------------------------------------------
# Deteksi Kebutuhan Build: Docker Image
# ---------------------------------------------------------
NEED_DOCKER_BUILD=$FORCE_BUILD_DOCKER

if [ "$FORCE_BUILD_DOCKER" = true ]; then
    echo "   ℹ️  Rebuild Docker image dipicu secara manual (flag --build/--all)."
elif [ -n "$CHANGED_FILES" ]; then
    if echo "$CHANGED_FILES" | grep -qE "^(Dockerfile|docker-compose\.ya?ml|Caddyfile|\.docker/)"; then
        echo "   ℹ️  Terdeteksi perubahan konfigurasi Docker/Caddy. Rebuild Docker diperlukan."
        NEED_DOCKER_BUILD=true
    fi
fi

# Cek apakah container sedang berjalan
if [ -z "$(docker ps -q -f name=^/${CONTAINER_NAME}$ 2>/dev/null)" ]; then
    echo "   ℹ️  Container ${CONTAINER_NAME} belum berjalan. Docker build/up diperlukan."
    NEED_DOCKER_BUILD=true
fi

# 3. Jalankan / Rebuild Container Docker
echo ""
if [ "$NEED_DOCKER_BUILD" = true ]; then
    echo "🔨 [2/7] Rebuild Docker image & restart container..."
    docker compose build
    docker compose down
    docker compose up -d --build
    echo "   ✅ Image berhasil di-build & container dinyalakan ulang."
else
    echo "⚡ [2/7] Lewati build Docker (menggunakan image & container aktif)..."
    docker compose up -d --no-build
    echo "   ✅ Container aktif."
fi

# 4. Update dependencies PHP via Composer (Hanya jika composer.lock / composer.json berubah)
echo ""
echo "📦 [3/7] Pengecekan dependencies PHP (Composer)..."
if [ -n "$CHANGED_FILES" ] && echo "$CHANGED_FILES" | grep -qE "^(composer\.lock|composer\.json)"; then
    echo "   ℹ️  Terdeteksi perubahan file Composer. Menjalankan composer install..."
    docker exec "$CONTAINER_NAME" composer install --no-dev --optimize-autoloader
    echo "   ✅ Dependencies PHP berhasil diperbarui."
else
    echo "   ⚡ Lewati composer install (tidak ada perubahan dependencies PHP)."
fi

# ---------------------------------------------------------
# Deteksi Kebutuhan Build: Frontend Assets (Vite)
# ---------------------------------------------------------
NEED_ASSET_BUILD=$FORCE_BUILD_ASSETS

if [ "$FORCE_BUILD_ASSETS" = true ]; then
    echo "   ℹ️  Build asset frontend dipicu secara manual (flag --build-assets/--all)."
elif [ ! -d "public/build" ] || [ ! -f "public/build/manifest.json" ]; then
    echo "   ℹ️  Direktori public/build belum tersedia. Build assets diperlukan."
    NEED_ASSET_BUILD=true
elif [ -n "$CHANGED_FILES" ]; then
    if echo "$CHANGED_FILES" | grep -qE "^(package\.json|package-lock\.json|vite\.config\.js|resources/css/|resources/js/|resources/views/)"; then
        echo "   ℹ️  Terdeteksi perubahan pada file asset/views frontend. Rebuild assets diperlukan."
        NEED_ASSET_BUILD=true
    fi
fi

# 5. Build Frontend Assets (Hanya jika diperlukan)
echo ""
if [ "$NEED_ASSET_BUILD" = true ]; then
    echo "🎨 [4/7] Membangun asset frontend (Vite)..."
    # Cek apakah package.json / package-lock.json berubah untuk npm install
    if [ -n "$CHANGED_FILES" ] && echo "$CHANGED_FILES" | grep -qE "^(package\.json|package-lock\.json)"; then
        echo "   📦 Menginstal dependency npm baru..."
        docker exec "$CONTAINER_NAME" npm install --no-audit --no-fund
    fi
    docker exec "$CONTAINER_NAME" npm run build
    echo "   ✅ Frontend assets berhasil di-compile."
else
    echo "⚡ [4/7] Lewati build asset frontend (tidak ada perubahan assets/views)..."
fi

# 6. Jalankan migrasi database
echo ""
echo "🗄️  [5/7] Menjalankan migrasi database..."
docker exec "$CONTAINER_NAME" php artisan migrate --force
echo "   ✅ Migrasi selesai."

# 7. Optimasi Laravel & Reload FrankenPHP
echo ""
echo "⚡ [6/7] Optimasi cache Laravel..."
docker exec "$CONTAINER_NAME" php artisan optimize:clear
docker exec "$CONTAINER_NAME" php artisan config:cache
docker exec "$CONTAINER_NAME" php artisan route:cache
docker exec "$CONTAINER_NAME" php artisan view:cache
docker exec "$CONTAINER_NAME" php artisan event:cache
docker exec "$CONTAINER_NAME" php artisan icons:cache 2>/dev/null || true
docker exec "$CONTAINER_NAME" php artisan filament:cache-components 2>/dev/null || true
echo "   ✅ Cache Laravel berhasil di-generate."

echo ""
echo "🔄 Reload web server (FrankenPHP)..."
docker restart "$CONTAINER_NAME" >/dev/null 2>&1 || true
echo "   ✅ Web server di-reload dengan konfigurasi dan kode terbaru."

# 8. Bersihkan image Docker lama jika tadi melakukan build
echo ""
if [ "$NEED_DOCKER_BUILD" = true ]; then
    echo "🧹 [7/7] Membersihkan image Docker lama yang tidak terpakai..."
    docker image prune -f
    echo "   ✅ Image lama dibersihkan."
else
    echo "🧹 [7/7] Lewati pembersihan image Docker."
fi

# 9. Verifikasi Status
echo ""
echo "=========================================="
echo "  ✅ Deploy SIPETRA Selesai!"
echo "=========================================="
echo ""
echo "📊 Status Container:"
docker ps --filter "name=^/${CONTAINER_NAME}$" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || true
echo ""
echo "🧠 PHP Environment:"
docker exec "$CONTAINER_NAME" php -r "echo 'PHP: ' . PHP_VERSION . ' | Memory: ' . ini_get('memory_limit') . ' | Max Execution: ' . ini_get('max_execution_time') . 's' . PHP_EOL;" 2>/dev/null || true
echo ""
