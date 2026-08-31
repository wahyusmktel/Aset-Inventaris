#!/bin/bash
# ==============================================================================
# Script Setup Awal Server Ubuntu 22.04 / 24.04 LTS
# Untuk Aplikasi Aset & Inventaris SMK Telkom Lampung
# Jalankan sebagai root: sudo bash setup-ubuntu.sh
# ==============================================================================

set -e

echo "🔧 Memperbarui repositori paket Ubuntu..."
apt-get update && apt-get upgrade -y

echo "📦 Menginstal dependensi dasar..."
apt-get install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl git unzip ufw

echo "🐘 Menambahkan PPA PHP Ondřej Surý..."
add-apt-repository ppa:ondrej/php -y
apt-get update

echo "🐘 Menginstal PHP 8.2 & Ekstensi Lengkap yang Dibutuhkan..."
apt-get install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-intl php8.2-sqlite3 php8.2-redis

echo "🌐 Menginstal Web Server Nginx..."
apt-get install -y nginx

echo "🗄️ Menginstal Database MySQL Server..."
apt-get install -y mysql-server

echo "📦 Menginstal Composer (PHP Package Manager)..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "🟢 Menginstal Node.js 20 LTS & NPM..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

echo "🔒 Menginstal Certbot untuk SSL Let's Encrypt..."
apt-get install -y certbot python3-certbot-nginx

echo "📁 Menyiapkan direktori web /var/www/aset-inventaris..."
mkdir -p /var/www/aset-inventaris
chown -R $USER:www-data /var/www/aset-inventaris

echo "🛡️ Mengatur Firewall UFW..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
# ufw --force enable

echo "✅ ==========================================================================="
echo "🎉 SETUP SERVER SELESAI!"
echo "Langkah selanjutnya:"
echo "1. Clone repository ke /var/www/aset-inventaris"
echo "2. Buat database MySQL dan user database"
echo "3. Salin .env.example ke .env dan sesuaikan kredensial"
echo "4. Jalankan deploy.sh"
echo "=============================================================================="
