# 🚀 Panduan Lengkap Deploy Production ke Server Ubuntu
## Sistem Informasi Manajemen Aset & Inventaris Sekolah (SMK Telkom Lampung)

Panduan ini disusun langkah demi langkah untuk menginstal dan menjalankan aplikasi **Aset & Inventaris SMK Telkom Lampung** di server **Ubuntu 20.04 / 22.04 / 24.04 LTS**.

---

## 📋 1. Kebutuhan Sistem & Spesifikasi Server (Prerequisites)

- **OS:** Ubuntu 22.04 LTS / 24.04 LTS
- **Web Server:** Nginx
- **PHP Version:** PHP 8.2 atau 8.3 (dengan modul `fpm`, `mysql`, `gd`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`)
- **Database:** MySQL 8.0+ atau MariaDB 10.6+
- **Node.js:** v18.x / v20.x LTS & NPM
- **Composer:** v2.x+
- **Domain & SSL:** Domain aktif (misal `aset.smktelkom-lpg.sch.id`) dengan Certbot Let's Encrypt

---

## 🛠️ 2. Langkah 1: Instalasi Paket Server (Setup Awal)

Masuk ke server Ubuntu via SSH, lalu jalankan perintah berikut:

```bash
# Update repository server
sudo apt update && sudo apt upgrade -y

# Instal dependensi dasar & PPA PHP
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common curl git unzip ufw nginx mysql-server

# Tambahkan repositori PHP Ondřej
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instal PHP 8.2 & modul yang dibutuhkan
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-intl php8.2-sqlite3

# Instal Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instal Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 🗄️ 3. Langkah 2: Konfigurasi Database MySQL

Buka terminal MySQL dan buat database serta user khusus untuk aplikasi:

```bash
sudo mysql
```

Jalankan query SQL berikut (ganti password sesuai kebutuhan Anda):

```sql
CREATE DATABASE aset_inventaris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'aset_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda123!';

GRANT ALL PRIVILEGES ON aset_inventaris_db.* TO 'aset_user'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

---

## 📥 4. Langkah 3: Clone Repository Proyek

Clone kode aplikasi dari GitHub ke folder `/var/www/aset-inventaris`:

```bash
# Buat folder dan atur hak akses
sudo mkdir -p /var/www/aset-inventaris
sudo chown -R $USER:www-data /var/www/aset-inventaris

# Clone repository
cd /var/www
git clone git@github.com:wahyusmktel/Aset-Inventaris.git aset-inventaris
cd /var/www/aset-inventaris
```

---

## ⚙️ 5. Langkah 4: Konfigurasi Environment (`.env`)

Salin file contoh konfigurasi dan sesuaikan nilai production:

```bash
cp .env.example .env
nano .env
```

Sesuaikan baris-baris penting berikut pada file `.env`:

```env
APP_NAME="Aset & Inventaris SMK Telkom Lampung"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://aset.smktelkom-lpg.sch.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aset_inventaris_db
DB_USERNAME=aset_user
DB_PASSWORD=PasswordKuatAnda123!

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=public
```

---

## 📦 6. Langkah 5: Instalasi Dependensi & Build Asset Frontend

Jalankan perintah berikut di dalam folder `/var/www/aset-inventaris`:

```bash
# 1. Generate Application Key
php artisan key:generate

# 2. Instal dependensi Composer (mode Production)
composer install --no-dev --optimize-autoloader

# 3. Instal dependensi NPM & Compile Frontend (Vite)
npm ci
npm run build

# 4. Jalankan Migrasi Database & Seeding Data Awal
php artisan migrate --seed --force

# 5. Buat Symlink Storage Publik
php artisan storage:link

# 6. Cache & Optimasi Kecepatan Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🔒 7. Langkah 6: Pengaturan Hak Akses Folder (Permissions)

Pastikan web server (`www-data`) memiliki izin tulis pada folder `storage` dan `bootstrap/cache`:

```bash
sudo chown -R www-data:www-data /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache
sudo chmod -R 775 /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache
```

---

## 🌐 8. Langkah 7: Konfigurasi Nginx Web Server

Salin file konfigurasi Nginx yang telah disediakan:

```bash
sudo cp /var/www/aset-inventaris/deploy/nginx/aset-inventaris.conf /etc/nginx/sites-available/aset-inventaris.conf
```

Buka file konfigurasi untuk menyesuaikan nama domain Anda:
```bash
sudo nano /etc/nginx/sites-available/aset-inventaris.conf
```

Aktifkan konfigurasi virtual host di Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/aset-inventaris.conf /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default # Hapus default site jika ada

# Uji konfigurasi Nginx
sudo nginx -t

# Restart Nginx & PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## 🛡️ 9. Langkah 8: Pasang SSL Gratis (Let's Encrypt HTTPS)

Pasang sertifikat SSL otomatis menggunakan Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d aset.smktelkom-lpg.sch.id
```
Pilih opsi **Redirect HTTP to HTTPS (Opsi 2)** saat ditanya oleh Certbot.

---

## ⏰ 10. Langkah 9: Pasang Crontab Laravel Task Scheduler

Buka crontab server:
```bash
sudo crontab -e -u www-data
```
Tambahkan baris berikut di bagian paling bawah:
```cron
* * * * * cd /var/www/aset-inventaris && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 11. Cara Melakukan Update / Deployment Berikutnya

Kapan pun Anda melakukan perubahan kode di masa mendatang, cukup jalankan script otomatis:

```bash
cd /var/www/aset-inventaris
bash deploy/scripts/deploy.sh
```

---

## 👥 Akun Default Pertama Kali (Initial Seed):
- **Super Administrator:**
  - Email: `admin@admin.com`
  - Password: `password` *(atau sandi yang diubah saat seed)*
- **Anggota Tim Pendata:**
  - Email: `anggota@smktelkom.sch.id`
  - Password: `password`
