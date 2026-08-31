#!/bin/bash
# ==============================================================================
# Script Otomasi Deploy & Pembaruan Aplikasi Aset & Inventaris SMK Telkom Lampung
# Jalankan di server: bash deploy.sh
# ==============================================================================

set -e

echo "🚀 [1/8] Memulai proses Deployment Production..."

# Pastikan berada di root direktori proyek
cd /var/www/aset-inventaris

echo "📥 [2/8] Mengambil pembaruan kode terbaru dari Git (Branch: main)..."
git pull origin main

echo "📦 [3/8] Menginstal dependensi PHP (Composer Production)..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo "⚡ [4/8] Menginstal dependensi NPM & Mengompilasi Asset Frontend (Vite)..."
npm ci
npm run build

echo "🗄️ [5/8] Menjalankan migrasi database..."
php artisan migrate --force

echo "🔗 [6/8] Memastikan symlink storage publik terpasang..."
php artisan storage:link || true

echo "🧹 [7/8] Mengoptimalkan cache konfigurasi, route, dan view..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "🔒 [8/8] Mengatur kepemilikan dan permission direktori..."
chown -R www-data:www-data /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache
chmod -R 775 /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache

# Restart PHP-FPM jika diperlukan
if systemctl is-active --quiet php8.2-fpm; then
    systemctl reload php8.2-fpm
    echo "🔄 PHP 8.2 FPM reloaded."
elif systemctl is-active --quiet php8.3-fpm; then
    systemctl reload php8.3-fpm
    echo "🔄 PHP 8.3 FPM reloaded."
fi

echo "✅ ==========================================================================="
echo "🎉 DEPLOYMENT PRODUCTION SELESAI DENGAN SUKSES!"
echo "Aplikasi Aset & Inventaris SMK Telkom Lampung siap digunakan di server."
echo "=============================================================================="
