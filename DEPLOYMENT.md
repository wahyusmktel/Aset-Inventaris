# 🚀 Panduan Lengkap Deploy Production ke Server Ubuntu
## Sistem Informasi Manajemen Aset & Inventaris Sekolah (SMK Telkom Lampung)
### Target Domain: `aset.smktelkom-lpg.id`

Panduan ini disusun secara komprehensif langkah demi langkah untuk menginstal dan menjalankan aplikasi **Aset & Inventaris SMK Telkom Lampung** di server **Ubuntu 20.04 / 22.04 / 24.04 LTS** menggunakan **Cloudflare Tunnel (`cloudflared`)** dan **Nginx**.

---

## 📋 1. Kebutuhan Sistem & Spesifikasi Server (Prerequisites)

- **OS:** Ubuntu 22.04 LTS / 24.04 LTS
- **Web Server:** Nginx (listening on `localhost:80`)
- **PHP Version:** PHP 8.2 atau 8.3 (`php8.2-fpm`, `mysql`, `gd`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `sqlite3`)
- **Database:** MySQL 8.0+ atau MariaDB 10.6+
- **Node.js:** v18.x / v20.x LTS & NPM
- **Composer:** v2.x+
- **Domain:** `smktelkom-lpg.id` yang sudah terhubung di Cloudflare DNS
- **Sub-domain:** `aset.smktelkom-lpg.id`

---

## 🛠️ 2. Langkah 1: Instalasi Paket Server (Stack Software)

Masuk ke server Ubuntu via SSH, lalu jalankan perintah berikut:

```bash
# Update repository server
sudo apt update && sudo apt upgrade -y

# Instal dependensi dasar & PPA PHP
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common curl git unzip ufw nginx mysql-server

# Tambahkan repositori PHP Ondřej
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instal PHP 8.2 & ekstensi lengkap yang dibutuhkan
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath \
    php8.2-intl php8.2-sqlite3

# Instal Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instal Node.js 20 LTS & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 🗄️ 3. Langkah 2: Konfigurasi Database MySQL

Buka terminal MySQL di server:

```bash
sudo mysql
```

Jalankan query SQL berikut (ganti password database sesuai keinginan Anda):

```sql
CREATE DATABASE aset_inventaris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'aset_user'@'localhost' IDENTIFIED BY 'PasswordKuatAnda123!';

GRANT ALL PRIVILEGES ON aset_inventaris_db.* TO 'aset_user'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

---

## 📥 4. Langkah 3: Clone Repository Proyek

Clone kode aplikasi dari GitHub ke `/var/www/aset-inventaris`:

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

Pastikan nilai baris berikut sudah sesuai dengan domain `aset.smktelkom-lpg.id` dan database MySQL Anda:

```env
APP_NAME="Aset & Inventaris SMK Telkom Lampung"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://aset.smktelkom-lpg.id

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

## 📦 6. Langkah 5: Instalasi Dependensi, Build Asset, & Seeding

Jalankan serangkaian perintah inisialisasi aplikasi:

```bash
# 1. Generate Application Key
php artisan key:generate

# 2. Instal dependensi Composer (Production)
composer install --no-dev --optimize-autoloader

# 3. Instal dependensi NPM & Kompilasi Frontend (Vite)
npm ci
npm run build

# 4. Jalankan Migrasi Database & Seeding Data Awal
php artisan migrate --seed --force

# 5. Buat Symlink Storage Publik
php artisan storage:link

# 6. Optimasi Cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🔒 7. Langkah 6: Hak Akses Folder (Permissions)

```bash
sudo chown -R www-data:www-data /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache
sudo chmod -R 775 /var/www/aset-inventaris/storage /var/www/aset-inventaris/bootstrap/cache
```

---

## 🌐 8. Langkah 7: Konfigurasi Nginx Web Server

Salin konfigurasi Nginx yang telah disiapkan untuk domain `aset.smktelkom-lpg.id`:

```bash
sudo cp /var/www/aset-inventaris/deploy/nginx/aset-inventaris.conf /etc/nginx/sites-available/aset-inventaris.conf

# Aktifkan virtual host
sudo ln -s /etc/nginx/sites-available/aset-inventaris.conf /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Tes & Reload Nginx
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## ☁️ 9. Langkah 8: Konfigurasi Cloudflare Tunnel & Routing DNS dari Server CLI

Cloudflare Tunnel memungkinkan server lokal Anda terhubung langsung ke jaringan global Cloudflare tanpa perlu IP Publik Statis, tanpa membuka port router (Port Forwarding), dan otomatis terenkripsi HTTPS SSL!

### A. Cara Cepat (Otomatis via Script):
```bash
sudo bash /var/www/aset-inventaris/deploy/scripts/setup-cloudflare-tunnel.sh
```

---

### B. Cara Manual (Langkah demi Langkah via Terminal Server):

#### 1. Instalasi `cloudflared` di Ubuntu
```bash
# Tambahkan repositori Cloudflare
sudo mkdir -p --mode=0755 /etc/apt/keyrings
curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg | sudo tee /etc/apt/keyrings/cloudflare-main.gpg >/dev/null
echo "deb [signed-by=/etc/apt/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/cloudflared.list
sudo apt update
sudo apt install -y cloudflared
```

#### 2. Login & Otorisasi Akun Cloudflare
```bash
cloudflared tunnel login
```
> Terminal akan memunculkan tautan login. Buka tautan tersebut di browser, pilih domain `smktelkom-lpg.id`, dan klik **Authorize**. File sertifikat `cert.pem` otomatis tersimpan di server.

#### 3. Buat Tunnel Baru
```bash
cloudflared tunnel create aset-inventaris
```
> Perintah ini akan menghasilkan **Tunnel UUID** (misalnya `a1b2c3d4-xxxx-xxxx-xxxx-xxxxxxxxxxxx`). Catat UUID ini.

#### 4. Tambahkan Subdomain DNS `aset.smktelkom-lpg.id` Langsung dari Server CLI 🎯
Jalankan perintah berikut untuk otomatis menambahkan CNAME record di Cloudflare DNS:
```bash
cloudflared tunnel route dns aset-inventaris aset.smktelkom-lpg.id
```
*(Record CNAME untuk `aset.smktelkom-lpg.id` otomatis tercipta di dashboard Cloudflare!)*

#### 5. Buat File Konfigurasi `/etc/cloudflared/config.yml`
```bash
sudo mkdir -p /etc/cloudflared
sudo cp ~/.cloudflared/*.json /etc/cloudflared/
sudo nano /etc/cloudflared/config.yml
```
Isikan konfigurasi berikut (ganti `<TUNNEL_ID>` dengan UUID Tunnel Anda):
```yaml
tunnel: <TUNNEL_ID>
credentials-file: /etc/cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: aset.smktelkom-lpg.id
    service: http://localhost:80
    originRequest:
      noTLSVerify: true
      connectTimeout: 30s
  - service: http_status:404
```

#### 6. Pasang & Jalankan Cloudflare Tunnel sebagai Service Otomatis
```bash
sudo cloudflared service install /etc/cloudflared/config.yml
sudo systemctl daemon-reload
sudo systemctl enable cloudflared
sudo systemctl restart cloudflared

# Cek status koneksi tunnel
sudo systemctl status cloudflared
```

Sekarang website Anda dapat langsung diakses secara aman di seluruh dunia melalui:
👉 **`https://aset.smktelkom-lpg.id`**

---

## ⏰ 10. Langkah 9: Pasang Crontab Laravel Task Scheduler

Buka crontab server:
```bash
sudo crontab -e -u www-data
```
Tambahkan baris berikut di paling bawah:
```cron
* * * * * cd /var/www/aset-inventaris && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 11. Cara Update / Deployment Berikutnya

Kapan pun Anda melakukan perubahan kode di repository, cukup jalankan skrip deploy 1-klik di server:

```bash
cd /var/www/aset-inventaris
bash deploy/scripts/deploy.sh
```

---

## 👥 Akun Default Pertama Kali (Initial Seed):
- **👑 Super Administrator:**
  - Email: `admin@admin.com`
  - Password: `password` *(atau sandi yang diubah saat seed)*
- **👷 Anggota Tim Pendata:**
  - Email: `anggota@smktelkom.sch.id`
  - Password: `password`
