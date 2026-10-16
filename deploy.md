# Tutorial Deploy — Bluesea Lounge POS

Panduan langkah-demi-langkah men-deploy aplikasi **Bluesea Lounge POS** (Laravel 11) ke server **Ubuntu 24.04 LTS** menggunakan **Nginx + PHP-FPM 8.3 + MySQL 8 + Node.js 20**.

Panduan ini ditulis agar **tidak ada dependensi yang terlewat**, sehingga proses deploy berjalan tanpa error.

---

## 1. Ringkasan Aplikasi & Arsitektur

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 11 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS + Vite 6 |
| Database | MySQL 8 |
| Session / Cache / Queue | Driver `database` (butuh tabel di MySQL) |
| Ekspor PDF | `barryvdh/laravel-dompdf` (butuh ekstensi **GD**) |
| Ekspor Excel | Response view `.xls` (tanpa dependensi tambahan) |
| Penyimpanan gambar produk | Disk `public` + `php artisan storage:link` |

**Arsitektur deploy:**

```
Internet
   │  80/443
   ▼
[ Nginx ] ──(FastCGI socket)──► [ PHP-FPM 8.3 ] ──► [ Laravel app /public ]
                                                     │
                                                     ├─► [ MySQL 8 ]  (data, session, cache, queue)
                                                     └─► [ storage/app/public ] (gambar produk)

[ Supervisor ] menjalankan  php artisan queue:work
[ Cron ]       menjalankan  php artisan schedule:run
```

---

## 2. Prasyarat Server

- **OS:** Ubuntu 24.04 LTS (fresh install / VPS).
- **Spesifikasi minimum:** 1 vCPU, 2 GB RAM, 20 GB disk (2 GB RAM penting untuk `npm run build`).
- **Akses:** user dengan hak `sudo`.
- **Domain** (opsional, untuk HTTPS): contoh `pos.bluesea.example.com` sudah diarahkan ke IP server (A record).

> ⚠️ **Catatan versi:** PHP wajib **8.2+**. Ubuntu 24.04 secara default menyediakan **PHP 8.3** di repositori resmi — sudah cocok. Jangan pakai PHP 8.1 ke bawah.

---

## 3. Daftar Dependensi yang WAJIB Ada (Checklist Anti-Error)

| Dependensi | Versi | Kenapa perlu |
|---|---|---|
| PHP | 8.2 / 8.3 | Framework Laravel 11 |
| `php-fpm` | 8.3 | Menjalankan PHP via Nginx |
| Ekstensi `pdo_mysql` | - | Koneksi ke MySQL |
| Ekstensi `mbstring` | - | Validasi & manipulasi string |
| Ekstensi `dom` (paket `php-xml`) | - | DOMDocument untuk dompdf |
| Ekstensi **`gd`** | - | **Render gambar di PDF** (dompdf gagal tanpa ini) |
| Ekstensi `curl` | - | HTTP client Laravel |
| Ekstensi `zip` | - | Utilitas arsip / composer |
| Ekstensi `bcmath` | - | Perhitungan presisi (harga/desimal) |
| Ekstensi `intl` | - | Format lokalisasi (`APP_LOCALE=id`) |
| Ekstensi `fileinfo`, `ctype`, `tokenizer`, `openssl`, `filter`, `hash`, `session`, `json` | - | Sudah otomatis ikut `php8.3-common` |
| Composer | 2.x | Manajer dependensi PHP |
| Node.js | 18+ (disarankan 20 LTS) | Build aset Vite 6 |
| npm | bawaan Node | Build aset Vite |
| MySQL Server | 8.x | Database |
| Nginx | 1.24+ | Web server |
| Supervisor | 4.x | Menjalankan queue worker |
| Certbot | opsional | Sertifikat HTTPS |

---

## 4. Langkah Deploy

### Langkah 1 — Update server & siapkan user

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y unzip git curl ca-certificates gnupg software-properties-common
```

(Opsional) Buat user deploy non-root:

```bash
sudo adduser bluesea
sudo usermod -aG sudo bluesea
su - bluesea
```

Semua perintah selanjutnya dijalankan sebagai user yang punya `sudo`.

---

### Langkah 2 — Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable --now nginx
```

Cek: buka `http://IP_SERVER` → muncul halaman default Nginx.

---

### Langkah 3 — Install PHP 8.3 + SEMUA ekstensi yang dibutuhkan

```bash
sudo apt install -y \
  php8.3-cli \
  php8.3-fpm \
  php8.3-mysql \
  php8.3-mbstring \
  php8.3-xml \
  php8.3-gd \
  php8.3-curl \
  php8.3-zip \
  php8.3-bcmath \
  php8.3-intl \
  php8.3-opcache
```

Verifikasi ekstensi kritis (harus muncul semua **tanpa error**):

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring|dom|gd|curl|zip|bcmath|intl|fileinfo|tokenizer|openssl'
```

Aktifkan dan jalankan PHP-FPM:

```bash
sudo systemctl enable --now php8.3-fpm
sudo systemctl status php8.3-fpm --no-pager
```

> ❗ Jika `gd` tidak muncul, dompdf akan gagal saat mengekspor PDF berisi gambar. Pastikan paket `php8.3-gd` benar-benar terpasang.

---

### Langkah 4 — Install Composer 2

```bash
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm /tmp/composer-setup.php
composer --version
```

---

### Langkah 5 — Install & konfigurasi MySQL 8

```bash
sudo apt install -y mysql-server
sudo systemctl enable --now mysql
sudo mysql_secure_installation
```

Buat database dan user khusus aplikasi (ganti `PASSWORD_KUAT`):

```bash
sudo mysql
```

```sql
CREATE DATABASE bluesea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bluesea_user'@'%' IDENTIFIED BY 'nadya22';
GRANT ALL PRIVILEGES ON bluesea.* TO 'bluesea_user'@'%';
FLUSH PRIVILEGES;
EXIT;
```

> Database `bluesea` juga dipakai untuk tabel `sessions`, `cache`, dan `jobs`. Tidak perlu database terpisah.

---

### Langkah 6 — Install Node.js 20 LTS (untuk build aset)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v && npm -v
```

> 🔁 **Alternatif tanpa Node di server:** jalankan `npm install && npm run build` di komputer lokal, lalu unggah folder `public/build/` hasilnya ke server. Lewati langkah `npm` di Langkah 8.

---

### Langkah 7 — Ambil kode aplikasi

Letakkan aplikasi di `/var/www/bluesea-lounge` (bukan di `/public`; Nginx akan diarahkan ke dalam `/public`):

```bash
sudo mkdir -p /var/www/bluesea-lounge
sudo chown -R $USER:$USER /var/www/bluesea-lounge
```

**Opsi A — Git clone:**

```bash
git clone <URL_REPOSITORY_ANDA> /var/www/bluesea-lounge
cd /var/www/bluesea-lounge
```

**Opsi B — Upload** seluruh isi proyek (tanpa `node_modules/` dan tanpa `vendor/`) ke `/var/www/bluesea-lounge`.

---

### Langkah 8 — Install dependensi aplikasi (PHP & JS)

```bash
cd /var/www/bluesea-lounge

# Dependensi PHP (produksi, tanpa paket dev)
composer install --no-dev --optimize-autoloader

# Dependensi JS + build aset produksi
npm ci
npm run build
```

Cek bahwa hasil build ada: `ls public/build` → harus berisi `manifest.json` dan folder `assets`.

> Jika `npm ci` gagal karena tidak ada `node_modules` awal, gunakan `npm install`.

---

### Langkah 9 — Konfigurasi environment (`.env` produksi)

```bash
cp .env.example .env
nano .env
```

Isi/sesuaikan minimal nilai berikut:

```dotenv
APP_NAME="Bluesea Lounge POS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Makassar
APP_URL=https://pos.bluesea.example.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

# Database (samakan dengan Langkah 5)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bluesea
DB_USERNAME=bluesea_user
DB_PASSWORD=PASSWORD_KUAT

# Session/Cache/Queue memakai database
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log

VITE_APP_NAME="${APP_NAME}"
```

> ⚠️ `APP_DEBUG=false` **wajib** di produksi, dan `APP_URL` harus memakai `https://` bila memakai SSL agar URL aset/redirect benar.

Generate application key:

```bash
php artisan key:generate
```

---

### Langkah 10 — Migrasi database, seeder, dan storage link

```bash
cd /var/www/bluesea-lounge

# Buat skema tabel (users, sessions, cache, jobs, POS, dll.)
php artisan migrate --seed --force

# Symlink agar gambar produk dapat diakses publik
php artisan storage:link
```

`--seed` mengisi data awal: akun admin, kasir, kategori, produk, contoh transaksi.

---

### Langkah 11 — Hak akses & kepemilikan file

Nginx/PHP-FPM harus bisa **menulis** ke `storage` dan `bootstrap/cache`.

```bash
cd /var/www/bluesea-lounge

# Pemilik: user deploy, grup: www-data
sudo chown -R $USER:www-data /var/www/bluesea-lounge
sudo find /var/www/bluesea-lounge -type d -exec chmod 755 {} \;
sudo find /var/www/bluesea-lounge -type f -exec chmod 644 {} \;

# Folder yang perlu ditulis web server
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

# File executable
sudo chmod +x artisan
```

---

### Langkah 12 — Konfigurasi Nginx (server block)

Buat file konfigurasi:

```bash
sudo nano /etc/nginx/sites-available/bluesea-lounge
```

Isi (ganti `pos.bluesea.example.com` dan `php8.3-fpm.sock` bila versi PHP berbeda):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pos.bluesea.example.com;

    root /var/www/bluesea-lounge/public;
    index index.php index.html;

    # Ukuran upload gambar produk
    client_max_body_size 10M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site dan nonaktifkan default:

```bash
sudo ln -s /etc/nginx/sites-available/bluesea-lounge /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

### Langkah 13 — Optimasi cache Laravel (produksi)

```bash
cd /var/www/bluesea-lounge

php artisan optimize        # cache config, route, view
```

> Setiap kali `.env` atau file config berubah, jalankan `php artisan optimize:clear` lalu `php artisan optimize` lagi.

---

### Langkah 14 — Queue Worker (Supervisor)

`QUEUE_CONNECTION=database` di `.env` menuntut adanya worker. Agar worker otomatis jalan saat boot dan restart bila crash:

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/bluesea-worker.conf
```

Isi:

```ini
[program:bluesea-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bluesea-lounge/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/bluesea-lounge/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bluesea-worker:*
sudo supervisorctl status
```

> Jika aplikasi **tidak memakai queue** (mis. tidak ada job di background), Anda boleh menyetel `QUEUE_CONNECTION=sync` di `.env` dan melewati langkah ini. Namun `database` + Supervisor lebih aman untuk pengembangan fitur ke depan.

---

### Langkah 15 — Scheduler (Cron)

Tambahkan entri cron Laravel:

```bash
sudo crontab -e -u www-data
```

Tambahkan baris:

```cron
* * * * * cd /var/www/bluesea-lounge && php artisan schedule:run >> /dev/null 2>&1
```

---

### Langkah 16 — Firewall (UFW)

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

### Langkah 17 — HTTPS dengan Let's Encrypt (opsional tapi disarankan)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d pos.bluesea.example.com
```

Certbot otomatis mengubah konfigurasi Nginx ke HTTPS dan memasang renew timer. Uji:

```bash
sudo certbot renew --dry-run
```

Setelah HTTPS aktif, pastikan `APP_URL=https://...` di `.env`, lalu:

```bash
cd /var/www/bluesea-lounge
php artisan optimize:clear && php artisan optimize
```

---

## 5. Verifikasi Akhir

```bash
# Layanan berjalan?
sudo systemctl status nginx php8.3-fpm mysql --no-pager

# Nginx & PHP-FPM cocok?
sudo nginx -t

# Aplikasi (config cache OK, koneksi DB OK)
cd /var/www/bluesea-lounge
php artisan about
php artisan migrate:status
```

Buka `https://pos.bluesea.example.com` di browser, lalu login:

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Kasir | `kasir1` | `kasir123` |

> 🔐 **PENTING:** segera ganti kata sandi default setelah login pertama (Admin via menu Profil; akun Kasir diubah Admin via **Manajemen Pengguna**).

Uji cepat fitur kritis:
1. Login sebagai admin.
2. Tambah produk beserta **gambar** → cek gambar tampil (uji `storage:link`).
3. Buat transaksi POS → cek struk.
4. Ekspor laporan **PDF** dan **Excel** (uji ekstensi **GD** untuk PDF).
5. (Jika pakai Supervisor) tambah transaksi lalu cek `storage/logs/worker.log` tidak error.

---

## 6. Update Aplikasi (Rilis Berikutnya)

Setiap kali ada versi baru:

```bash
cd /var/www/bluesea-lounge

# Maintenance mode singkat
php artisan down

git pull origin main

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link

sudo supervisorctl restart bluesea-worker:*

php artisan up
```

---

## 7. Backup & Maintenance

**Backup database harian (cron):**

```bash
sudo mkdir -p /var/backups/bluesea
sudo crontab -e -u www-data
```

Tambahkan:

```cron
0 2 * * * mysqldump -u bluesea_user -pPASSWORD_KUAT bluesea | gzip > /var/backups/bluesea/bluesea_$(date +\%F).sql.gz
```

**Lihat log aplikasi:**

```bash
tail -f /var/www/bluesea-lounge/storage/logs/laravel.log
sudo tail -f /var/log/nginx/error.log
sudo journalctl -u php8.3-fpm -n 50 --no-pager
```

---

## 8. Troubleshooting Umum

| Gejala | Penyebab & Solusi |
|---|---|
| Halaman tampil **502 Bad Gateway** | FastCGI socket salah / PHP-FPM mati. Cek `sudo systemctl status php8.3-fpm` dan path socket di `/etc/php/8.3/fpm/pool.d/www.conf`. |
| **500 error** / blank page | `APP_KEY` kosong (`php artisan key:generate`) atau permission `storage`/`bootstrap/cache` salah. Cek `storage/logs/laravel.log`. |
| **Ekspor PDF error** | Ekstensi `gd` belum ada. `sudo apt install php8.3-gd && sudo systemctl restart php8.3-fpm`. |
| **CSS/JS tidak muncul** | Aset belum di-build atau `public/build` hilang. Jalankan `npm run build`. |
| **Gambar produk tidak tampil** | Symlink belum dibuat → `php artisan storage:link`. |
| **"could not find driver"** | `php8.3-mysql` belum terpasang. |
| **Session/CSRF error acak** | Tabel `sessions`/`cache` belum ada → `php artisan migrate --force`. |
| **Perubahan `.env` tidak berefek** | `php artisan optimize:clear && php artisan optimize` (config ter-cache). |
| **Upload gambar gagal (>2MB)** | Naikkan `upload_max_filesize` & `post_max_size` di `/etc/php/8.3/fpm/php.ini`, lalu restart PHP-FPM. |

---

## 9. Checklist Deploy

- [ ] Nginx terpasang & jalan
- [ ] PHP 8.3 + FPM + ekstensi (`pdo_mysql`, `mbstring`, `xml`, **`gd`**, `curl`, `zip`, `bcmath`, `intl`) 
- [ ] Composer 2 terpasang
- [ ] MySQL 8 + database `bluesea` + user khusus
- [ ] Node.js 20 + `npm run build` sukses (`public/build/manifest.json` ada)
- [ ] Kode di `/var/www/bluesea-lounge`
- [ ] `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` benar)
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --seed --force`
- [ ] `php artisan storage:link`
- [ ] Permission `storage` & `bootstrap/cache` = writeable
- [ ] Nginx root → `.../public`, site enabled, `nginx -t` OK
- [ ] Supervisor queue worker aktif (bila `QUEUE_CONNECTION=database`)
- [ ] Cron `schedule:run` aktif
- [ ] UFW aktif
- [ ] HTTPS aktif (Certbot)
- [ ] `php artisan optimize`
- [ ] Kata sandi default `admin` / `kasir1` **sudah diganti**
- [ ] Backup database terjadwal
