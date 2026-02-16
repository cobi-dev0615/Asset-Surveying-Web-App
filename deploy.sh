#!/bin/bash
# deploy.sh — SER Inventarios deployment script for HostGator CPanel
# Run from the web-platform/ directory on the server

set -e

echo "=== SER Inventarios — Deployment ==="

# 1. Install dependencies (production only)
echo "[1/7] Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Environment setup (first deploy only)
if [ ! -f .env ]; then
    echo "[2/7] Setting up environment..."
    cp .env.production .env
    php artisan key:generate --force
    echo "  ⚠  Edit .env with your CPanel database credentials before continuing!"
    echo "  Then run this script again."
    exit 1
else
    echo "[2/7] .env already exists, skipping..."
fi

# 3. Run migrations
echo "[3/7] Running migrations..."
php artisan migrate --force

# 4. Storage link
echo "[4/7] Creating storage link..."
if [ ! -L public/storage ]; then
    php artisan storage:link
else
    echo "  Storage link already exists."
fi

# 5. Create required directories
echo "[5/7] Creating directories..."
mkdir -p storage/app/public/fotos/activos
chmod -R 775 storage bootstrap/cache

# 6. Cache configuration
echo "[6/7] Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Done
echo "[7/7] Deployment complete!"
echo ""
echo "  URL: https://app.seretail.com.mx"
echo "  Login: avillegas / admin123"
echo ""
echo "  To clear caches: php artisan optimize:clear"
