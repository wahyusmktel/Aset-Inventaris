#!/bin/bash
# ==============================================================================
# Script Otomasi Instalasi & Routing DNS Cloudflare Tunnel di Ubuntu
# Domain: aset.smktelkom-lpg.id -> Nginx Lokal (http://localhost:80)
# Jalankan sebagai root: sudo bash setup-cloudflare-tunnel.sh
# ==============================================================================

set -e

DOMAIN="aset.smktelkom-lpg.id"
TUNNEL_NAME="aset-inventaris"

echo "🌐 [1/6] Mengunduh dan menginstal Cloudflare Tunnel (cloudflared)..."
# Tambahkan repositori Cloudflare
mkdir -p --mode=0755 /etc/apt/keyrings
curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg | tee /etc/apt/keyrings/cloudflare-main.gpg >/dev/null
echo "deb [signed-by=/etc/apt/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared $(lsb_release -cs) main" | tee /etc/apt/sources.list.d/cloudflared.list
apt-get update
apt-get install -y cloudflared

echo ""
echo "🔑 [2/6] Autentikasi Cloudflare (Login)..."
echo "Silakan klik atau salin URL yang muncul di browser untuk mengotorisasi domain smktelkom-lpg.id Anda:"
cloudflared tunnel login

echo ""
echo "🛠️ [3/6] Membuat Tunnel '${TUNNEL_NAME}'..."
TUNNEL_OUTPUT=$(cloudflared tunnel create ${TUNNEL_NAME} 2>&1 || true)
echo "$TUNNEL_OUTPUT"

# Ambil Tunnel UUID
TUNNEL_ID=$(cloudflared tunnel list | grep ${TUNNEL_NAME} | awk '{print $1}')

if [ -z "$TUNNEL_ID" ]; then
    echo "❌ Gagal mendeteksi Tunnel ID. Silakan periksa daftar tunnel dengan: cloudflared tunnel list"
    exit 1
fi

echo "✅ Tunnel ID Berhasil Ditemukan: ${TUNNEL_ID}"

echo ""
echo "🌍 [4/6] Menambahkan Sub-domain DNS ${DOMAIN} via Server CLI..."
cloudflared tunnel route dns ${TUNNEL_NAME} ${DOMAIN}
echo "✅ DNS CNAME record untuk ${DOMAIN} berhasil ditambahkan otomatis di Cloudflare Dashboard!"

echo ""
echo "📝 [5/6] Menyusun konfigurasi /etc/cloudflared/config.yml..."
mkdir -p /etc/cloudflared
cp ~/.cloudflared/${TUNNEL_ID}.json /etc/cloudflared/ || true

cat <<EOF > /etc/cloudflared/config.yml
tunnel: ${TUNNEL_ID}
credentials-file: /etc/cloudflared/${TUNNEL_ID}.json

ingress:
  - hostname: ${DOMAIN}
    service: http://localhost:80
    originRequest:
      noTLSVerify: true
      connectTimeout: 30s
  - service: http_status:404
EOF

echo "✅ Konfigurasi /etc/cloudflared/config.yml telah dibuat."

echo ""
echo "🚀 [6/6] Memasang dan menjalankan Cloudflare Tunnel sebagai Systemd Service..."
cloudflared service install /etc/cloudflared/config.yml || true
systemctl daemon-reload
systemctl enable cloudflared
systemctl restart cloudflared

echo ""
echo "=============================================================================="
echo "🎉 CLOUDFLARE TUNNEL BERHASIL DIAKTIFKAN & TERHUBUNG!"
echo "Status Service:"
systemctl status cloudflared --no-pager
echo ""
echo "🌐 Aplikasi Anda sekarang dapat diakses secara publik dan aman melalui:"
echo "👉 https://${DOMAIN}"
echo "=============================================================================="
