# Bluesea Lounge POS

Sistem Point of Sale (POS) dan manajemen operasional untuk **Bluesea Lounge** — sebuah lounge di area bandara. Aplikasi ini mendigitalisasi transaksi penjualan, pengelolaan stok produk siap jual, pencatatan pembelian, pengeluaran operasional, pelaporan, dan audit log.

Dokumen kebutuhan lengkap tersedia di [`prd.md`](prd.md).

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Laravel Blade + Tailwind CSS + Vite |
| Database | MySQL |
| Autentikasi | Laravel Authentication (username & password) |
| Hak akses | Middleware role (Admin / Kasir) |
| Ekspor PDF | `barryvdh/laravel-dompdf` |
| Ekspor Excel | Response view berformat `.xls` |

---

## Persyaratan

- PHP **8.2** atau lebih baru (ekstensi `pdo_mysql`, `mbstring`, `gd`/`imagick` untuk gambar produk).
- Composer 2.x
- Node.js 18+ dan npm
- MySQL 8.x

---

## Instalasi

```bash
# 1. Dependensi PHP & JS
composer install
npm install

# 2. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3. Buat database MySQL (sesuaikan nama pada .env: DB_DATABASE=bluesea)
#    Contoh via mysql client:
#    CREATE DATABASE bluesea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Migrasi + data awal
php artisan migrate --seed

# 5. Symlink penyimpanan gambar produk
php artisan storage:link

# 6. Build aset frontend
npm run build
```

### Menjalankan aplikasi

```bash
# Mode produksi/lokal cepat
php artisan serve

# Mode pengembangan (server + queue + log + vite HMR)
composer run dev
```

Aplikasi default diakses melalui `http://localhost:8000`.

---

## Akun Default (hasil seeder)

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Kasir | `kasir1` | `kasir123` |

> **Penting:** segera ubah kata sandi default setelah instalasi. Admin dapat mengubahnya sendiri melalui **Profil & Kata Sandi**, sedangkan kata sandi akun Kasir diubah oleh Admin melalui **Manajemen Pengguna**.

---

## Peran & Hak Akses

- **Admin** — akses penuh: dashboard, produk, kategori, stok, stok opname, pembelian, pengeluaran, laporan & ekspor, manajemen pengguna, audit log, serta pembatalan transaksi berhasil.
- **Kasir** — terminal POS, riwayat transaksi miliknya sendiri, cetak struk, dan profil (hanya baca). Kasir **tidak** dapat mengubah harga, mengelola stok/pembelian/pengeluaran, membatalkan transaksi yang sudah berhasil, atau mengubah kata sandinya sendiri — perubahan kata sandi akun Kasir dilakukan Admin melalui menu **Manajemen Pengguna**.

Lihat matriks hak akses lengkap pada bagian 4.3 di [`prd.md`](prd.md).

---

## Modul / Fitur Utama

1. **Autentikasi & Manajemen Pengguna** — login/logout, pembuatan akun, aktif/nonaktif akun, pembatasan percobaan login (throttle), dan pencatatan login gagal ke audit log.
2. **Dashboard** — omzet harian/bulanan, jumlah transaksi valid, produk terlaris, peringatan stok minimum, ringkasan pembelian & pengeluaran.
3. **Manajemen Produk & Kategori** — SKU/barcode unik, harga, stok, batas minimum, satuan, foto, status aktif, snapshot harga pada transaksi.
4. **Transaksi Penjualan (POS)** — pencarian nama/SKU/barcode, keranjang, validasi stok, pembayaran **Tunai** (dengan kembalian) dan **QRIS**, serta **idempotensi** untuk mencegah transaksi ganda.
5. **Struk** — struk digital dan cetak thermal (80mm) melalui browser; cetak ulang tidak membuat transaksi baru.
6. **Manajemen Stok & Stok Opname** — penambahan stok manual, penyesuaian/opname, riwayat pergerakan stok lengkap (stok sebelum/sesudah, pengguna, alasan), serta **halaman detail per produk** dengan ringkasan stok, riwayat pergerakan, dan riwayat opname.
7. **Pembelian Barang** — pencatatan pembelian tanpa menambah stok otomatis, lengkap dengan **koreksi (ubah)** dan **penghapusan aman (soft delete)** yang tercatat di audit log.
8. **Pengeluaran Operasional** — pencatatan, pengubahan, dan penghapusan dengan jejak audit.
9. **Laporan & Ekspor** — omzet, produk terlaris, stok, dan pembelian; ekspor ke **Excel** dan **PDF** sesuai filter periode.
10. **Pembatalan Transaksi** — hanya Admin, wajib alasan, mengembalikan stok satu kali secara atomik, dan tercatat di audit log.
11. **Audit Log** — mencatat aktivitas penting (login, perubahan produk/harga, penyesuaian stok, transaksi, pembatalan, dsb.).

---

## Pengujian

Pengujian fitur berjalan terhadap koneksi database yang dikonfigurasi di `phpunit.xml` (secara default MySQL dan menggunakan transaksi per-test).

```bash
php artisan test
```

Cakupan uji mencakup: autentikasi, matriks hak akses, atomisitas transaksi POS, pencegahan stok negatif, **idempotensi transaksi**, pembatalan & pengembalian stok, stok opname, **koreksi & penghapusan pembelian berjejak audit**, **detail riwayat stok produk**, integritas hapus produk, ekspor PDF/Excel, dan render seluruh halaman utama.

---

## Catatan Operasional

- **Online-only (BR-15):** transaksi baru tidak dapat diselesaikan tanpa koneksi ke server. Jika koneksi terputus setelah permintaan dikirim, kasir dapat mengirim ulang; permintaan yang sama tidak akan menghasilkan transaksi ganda berkat idempotency key.
- **Zona waktu:** `Asia/Makassar` (diatur di `APP_TIMEZONE`).
- **Format nomor:** transaksi `TRX-YYYYMMDD-XXXX`, pembelian `PO-YYYYMMDD-XXXX`, stok opname `ST-YYYYMMDD-XXXX`.
- **Printer thermal:** struk dirancang untuk kertas 80mm; uji dengan printer & browser operasional sebelum go-live.
- **QRIS:** untuk MVP, keberhasilan pembayaran dikonfirmasi manual oleh kasir (belum ada integrasi API penyedia pembayaran).
- **Backup:** disarankan menjadwalkan `mysqldump` rutin untuk database `bluesea`.

---

## Struktur Singkat

```
app/
  Http/Controllers/    # Controller per modul (Pos, Sale, Product, Stock, ...)
  Http/Middleware/     # EnsureUserIsAdmin, EnsureUserIsActive
  Models/              # Eloquent: Product, Sale, StockMovement, AuditLog, ...
database/
  migrations/          # Skema POS
  seeders/             # Data awal (akun, kategori, produk, contoh transaksi)
resources/views/
  admin/               # Halaman administrasi
  pos/                 # Terminal kasir
  sales/               # Struk & riwayat transaksi
  layouts/app.blade.php
routes/web.php         # Definisi rute & pembatasan role
tests/Feature/         # Pengujian fitur bisnis kritis
```
