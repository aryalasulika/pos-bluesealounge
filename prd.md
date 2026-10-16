# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Bluesea Lounge POS — Sistem Point of Sale dan Manajemen Operasional

**Versi:** 1.0  
**Status:** Draft siap ditinjau untuk pengembangan  
**Jenis Produk:** Aplikasi web responsif  
**Target Pengguna:** Admin dan Kasir  
**Backend:** Laravel  
**Frontend:** Laravel Blade + Tailwind CSS  
**Database:** MySQL

---

## 1. Ringkasan Produk

Bluesea Lounge POS adalah aplikasi Point of Sale berbasis web yang dirancang untuk membantu pengelolaan operasional penjualan makanan dan minuman di Bluesea Lounge, sebuah lounge yang berlokasi di salah satu bandara.

Sistem ini bertujuan menggantikan proses pencatatan manual yang selama ini menimbulkan kesulitan dalam pencatatan transaksi, pengawasan stok, pemantauan omzet, pencatatan pembelian, pengelolaan pengeluaran operasional, dan pembuatan laporan.

Aplikasi akan memiliki dua jenis pengguna utama, yaitu Admin dan Kasir, dengan hak akses berbeda. Admin bertanggung jawab atas konfigurasi dan pengelolaan operasional, sedangkan Kasir berfokus pada transaksi penjualan.

Sistem dikembangkan menggunakan Laravel, MySQL, Laravel Blade, dan Tailwind CSS. Aplikasi dapat diakses melalui komputer, laptop, tablet, dan smartphone selama perangkat terhubung ke server.

### 1.1 Tujuan utama

1. Mendigitalisasi pencatatan transaksi penjualan.
2. Menyediakan manajemen stok produk siap jual.
3. Memisahkan hak akses Admin dan Kasir.
4. Menyediakan pencatatan pembelian barang dan pengeluaran operasional.
5. Menghasilkan laporan omzet, produk terlaris, stok, dan riwayat pembelian.
6. Menyediakan ekspor laporan ke Excel dan PDF.
7. Menjaga akurasi data melalui validasi transaksi dan audit log.

---

## 2. Latar Belakang dan Masalah

### 2.1 Masalah yang ingin diselesaikan

- Pencatatan transaksi masih dilakukan secara manual atau menggunakan spreadsheet.
- Jumlah stok dalam pencatatan dapat berbeda dengan kondisi fisik.
- Omzet harian, mingguan, dan bulanan sulit dipantau secara cepat.
- Identifikasi produk terlaris membutuhkan perhitungan manual.
- Riwayat pembelian barang dan pengeluaran operasional belum terpusat.
- Hak akses Admin dan Kasir perlu dipisahkan.
- Pembuatan laporan memerlukan waktu dan pekerjaan tambahan.

### 2.2 Solusi yang diusulkan

Membangun aplikasi POS terpusat yang mencatat seluruh transaksi dan perubahan persediaan, mengelola pengguna berdasarkan role, serta menghasilkan laporan secara otomatis dari data yang tersimpan di database.

---

## 3. Tujuan dan Indikator Keberhasilan

### 3.1 Tujuan produk

- Mempercepat proses transaksi penjualan.
- Mengurangi kesalahan pencatatan transaksi dan stok.
- Mempermudah Admin memantau penjualan dan persediaan.
- Menyediakan riwayat transaksi yang dapat ditelusuri.
- Mengurangi waktu pembuatan laporan operasional.

### 3.2 Indikator keberhasilan

| Indikator | Kriteria keberhasilan |
|---|---|
| Akurasi transaksi | Total transaksi dan detail pembayaran konsisten dengan data tersimpan |
| Akurasi stok | Penjualan dan penyesuaian stok menghasilkan jumlah yang konsisten |
| Hak akses | Pengguna tidak dapat mengakses fungsi di luar rolenya |
| Laporan omzet | Data laporan sesuai dengan transaksi valid pada periode yang dipilih |
| Audit transaksi | Pembatalan dan penyesuaian stok memiliki catatan yang dapat ditelusuri |
| Ekspor laporan | File Excel dan PDF dapat diunduh dan sesuai dengan filter laporan |
| Responsivitas | Antarmuka dapat digunakan pada komputer, tablet, dan smartphone |

Target numerik untuk waktu respons, waktu penyelesaian transaksi, dan ketersediaan layanan ditentukan saat perencanaan teknis dan pengujian performa.

---

## 4. Target Pengguna dan Hak Akses

### 4.1 Admin

Admin memiliki akses penuh terhadap pengelolaan operasional, kecuali tindakan yang dibatasi oleh aturan keamanan sistem.

Tanggung jawab Admin:

- Mengelola akun pengguna.
- Mengelola produk, kategori, harga, dan barcode.
- Mengelola penambahan serta penyesuaian stok.
- Melakukan stok opname.
- Mencatat pembelian barang.
- Mencatat pengeluaran operasional.
- Melihat dan mengekspor laporan.
- Melihat riwayat transaksi.
- Membatalkan transaksi yang telah berhasil dengan alasan wajib.
- Melihat audit log.

### 4.2 Kasir

Kasir hanya memiliki akses yang diperlukan untuk melakukan penjualan.

Tanggung jawab Kasir:

- Login dan logout.
- Melihat produk aktif dan harga jual.
- Mencari produk berdasarkan nama, kode produk, atau barcode.
- Menambahkan produk ke keranjang.
- Mengubah jumlah produk sebelum transaksi diselesaikan.
- Membatalkan pesanan sebelum pembayaran dikonfirmasi.
- Memilih pembayaran tunai atau QRIS.
- Menyelesaikan transaksi.
- Melihat dan mencetak struk transaksi sesuai kewenangan yang ditentukan.

Kasir tidak boleh:

- Mengubah harga produk.
- Memberikan diskon.
- Mengelola stok.
- Mencatat pembelian barang.
- Mencatat pengeluaran operasional.
- Mengelola kategori produk.
- Mengelola akun pengguna.
- Membatalkan transaksi yang sudah berhasil.
- Mengakses halaman administrasi yang dibatasi untuk Admin.

### 4.3 Matriks hak akses

| Modul | Admin | Kasir |
|---|---|---|
| Dashboard | Penuh | Ringkasan kasir |
| Transaksi penjualan | Ya | Ya |
| Pembatalan pesanan belum dibayar | Ya | Ya |
| Pembatalan transaksi berhasil | Ya, dengan alasan | Tidak |
| Manajemen produk | Ya | Tidak |
| Manajemen kategori | Ya | Tidak |
| Manajemen stok | Ya | Tidak |
| Stok opname | Ya | Tidak |
| Pembelian barang | Ya | Tidak |
| Pengeluaran operasional | Ya | Tidak |
| Laporan dan ekspor | Ya | Tidak |
| Manajemen akun | Ya | Tidak |
| Audit log | Ya | Tidak |

---

## 5. Ruang Lingkup Produk

### 5.1 Fitur dalam MVP

Versi pertama aplikasi wajib mencakup:

1. Login, logout, dan pengelolaan role.
2. Dashboard Admin dan tampilan Kasir.
3. Manajemen produk dan kategori.
4. Transaksi penjualan.
5. Pembayaran tunai dan QRIS.
6. Struk digital dan cetak thermal melalui browser.
7. Manajemen stok produk siap jual.
8. Penambahan stok manual dan stok opname.
9. Peringatan stok minimum.
10. Pencatatan pembelian barang.
11. Pencatatan pengeluaran operasional.
12. Pembatalan transaksi dengan audit log.
13. Laporan omzet, produk terlaris, stok, dan riwayat pembelian.
14. Ekspor laporan ke Excel dan PDF.
15. Audit log untuk aktivitas penting.

### 5.2 Di luar ruang lingkup MVP

Fitur berikut tidak termasuk dalam versi awal:

- Manajemen bahan baku dan resep.
- Pengurangan stok bahan baku berdasarkan resep.
- Mode transaksi offline.
- Integrasi API pembayaran QRIS.
- Pembayaran kartu debit/kredit dan transfer bank.
- Sistem shift kasir dan rekonsiliasi kas per shift.
- Diskon dan promosi.
- Pengubahan harga oleh Kasir.
- Aplikasi native Android atau iOS.
- Multi-cabang dan multi-outlet.
- Integrasi akuntansi atau perpajakan.
- Perhitungan laba bersih secara penuh tanpa data biaya yang memadai.

Fitur di luar ruang lingkup dapat dipertimbangkan pada pengembangan berikutnya.

---

## 6. Persyaratan Fungsional

### FR-01 — Autentikasi dan Manajemen Pengguna

**Deskripsi:** Sistem menyediakan login menggunakan username dan password.

Persyaratan:

- Pengguna harus login sebelum mengakses halaman aplikasi.
- Setiap akun memiliki role Admin atau Kasir.
- Admin dapat membuat dan mengedit akun.
- Admin dapat menonaktifkan akun pengguna.
- Registrasi publik tidak tersedia.
- Password disimpan menggunakan mekanisme hashing yang aman.
- Pengguna yang dinonaktifkan tidak dapat login.
- Sesi login harus dikelola secara aman.
- Akses terhadap setiap endpoint harus divalidasi di sisi server.

**Kriteria penerimaan:**

- Login dengan kredensial valid berhasil.
- Login dengan kredensial salah ditolak.
- Kasir tidak dapat membuka halaman Admin melalui URL langsung.
- Akun nonaktif tidak dapat digunakan untuk login.
- Password tidak tersimpan dalam bentuk teks biasa.

### FR-02 — Dashboard Admin

Dashboard menampilkan ringkasan operasional yang relevan, meliputi:

- Omzet hari ini.
- Omzet bulan berjalan.
- Jumlah transaksi valid.
- Produk terlaris.
- Jumlah produk yang stoknya mencapai batas minimum.
- Ringkasan pembelian barang.
- Ringkasan pengeluaran operasional.

Dashboard harus menampilkan data yang konsisten dengan transaksi dan stok pada database.

Nilai omzet tidak boleh memasukkan transaksi yang sudah dibatalkan.

### FR-03 — Dashboard Kasir

Dashboard Kasir menyediakan akses cepat menuju transaksi penjualan.

Komponen utama:

- Pencarian produk.
- Daftar produk aktif.
- Keranjang belanja.
- Pengubahan kuantitas.
- Ringkasan total pembayaran.
- Pemilihan metode pembayaran.
- Konfirmasi transaksi.
- Akses struk transaksi.

Antarmuka harus meminimalkan langkah yang diperlukan untuk menyelesaikan transaksi.

### FR-04 — Manajemen Produk

Setiap produk memiliki informasi:

- ID produk.
- Kode produk atau SKU.
- Barcode.
- Nama produk.
- Kategori.
- Harga jual.
- Jumlah stok.
- Batas minimum stok.
- Satuan.
- Foto produk opsional.
- Status aktif atau nonaktif.
- Waktu pembuatan dan pembaruan.

Persyaratan:

- SKU harus unik.
- Barcode harus unik jika diisi.
- Harga jual tidak boleh negatif.
- Stok tidak boleh negatif.
- Produk dapat dicari berdasarkan nama, SKU, atau barcode.
- Produk nonaktif tidak dapat ditambahkan ke transaksi baru.
- Produk yang sudah memiliki riwayat transaksi tidak boleh dihapus permanen jika dapat merusak integritas data.
- Perubahan harga hanya dapat dilakukan Admin.
- Harga produk yang digunakan pada transaksi harus disimpan sebagai snapshot agar perubahan harga di masa depan tidak mengubah transaksi lama.

### FR-05 — Manajemen Kategori

Admin dapat:

- Menambah kategori.
- Mengedit kategori.
- Melihat daftar kategori.
- Menonaktifkan atau menghapus kategori yang belum digunakan.

Persyaratan:

- Nama kategori wajib diisi.
- Nama kategori tidak boleh duplikat.
- Kategori yang sudah digunakan tidak boleh dihapus secara permanen jika dapat merusak relasi atau riwayat transaksi.
- Kategori yang tidak lagi digunakan dapat dinonaktifkan.

Kategori awal dapat berupa Makanan, Minuman, Snack, atau kategori lain yang ditentukan Admin.

### FR-06 — Transaksi Penjualan

Alur transaksi:

1. Kasir login.
2. Kasir membuka halaman transaksi.
3. Kasir mencari atau memilih produk.
4. Kasir memasukkan jumlah.
5. Sistem memvalidasi produk, harga, dan stok.
6. Sistem menghitung subtotal dan total.
7. Kasir memilih metode pembayaran.
8. Kasir mengonfirmasi pembayaran.
9. Sistem menyimpan transaksi dan detail produk.
10. Sistem mengurangi stok secara otomatis.
11. Sistem menghasilkan nomor transaksi.
12. Sistem menyediakan struk digital dan opsi cetak.

Persyaratan:

- Nomor transaksi harus unik.
- Harga diambil dan divalidasi dari database.
- Kasir tidak dapat mengubah harga melalui manipulasi permintaan.
- Sistem harus memvalidasi ulang stok pada saat transaksi diselesaikan.
- Stok tidak boleh menjadi negatif.
- Transaksi dan pengurangan stok harus dilakukan secara atomik menggunakan database transaction.
- Jika salah satu proses gagal, seluruh perubahan transaksi harus dibatalkan.
- Klik berulang atau pengiriman ulang permintaan tidak boleh membuat transaksi ganda.
- Sistem harus memeriksa status transaksi jika koneksi terputus setelah permintaan transaksi dikirim.

### FR-07 — Metode Pembayaran

Sistem mendukung dua metode pembayaran:

**Tunai**
- Kasir memilih pembayaran tunai.
- Sistem mencatat jumlah pembayaran.
- Jika jumlah uang yang diterima dimasukkan, sistem menghitung kembalian.
- Pembayaran harus mencukupi total transaksi sebelum transaksi diselesaikan.

**QRIS**
- Kasir memilih pembayaran QRIS.
- Sistem mencatat metode pembayaran dan nominal.
- Pembayaran dikonfirmasi berdasarkan prosedur operasional yang disepakati.
- Integrasi API penyedia pembayaran tidak termasuk dalam MVP.

Sistem tidak boleh menganggap pembayaran berhasil hanya karena metode QRIS dipilih; transaksi harus melalui proses konfirmasi pembayaran yang valid.

### FR-08 — Struk Transaksi

Struk harus memuat:

- Identitas Bluesea Lounge.
- Nomor transaksi.
- Tanggal dan waktu.
- Nama atau identitas kasir sesuai kebijakan.
- Nama produk.
- Kuantitas.
- Harga satuan.
- Subtotal setiap item.
- Total transaksi.
- Metode pembayaran.
- Informasi pembayaran tunai dan kembalian jika berlaku.

Persyaratan:

- Struk dapat ditampilkan dalam format digital.
- Struk dapat dicetak melalui browser menggunakan printer thermal yang kompatibel.
- Struk transaksi lama dapat dibuka kembali oleh pengguna yang berwenang.
- Cetak ulang tidak boleh membuat transaksi baru.
- Tampilan cetak harus mendukung ukuran kertas thermal yang ditentukan saat implementasi.

### FR-09 — Manajemen Stok

Admin dapat:

- Melihat stok seluruh produk.
- Menambahkan stok secara manual.
- Melakukan penyesuaian stok.
- Melihat riwayat pergerakan stok.
- Memfilter stok berdasarkan kategori dan status.
- Melihat produk dengan stok minimum.

Penambahan stok dilakukan melalui menu khusus dan tidak otomatis berasal dari pencatatan pembelian barang.

Setiap pergerakan stok harus menyimpan:

- Produk.
- Jenis pergerakan.
- Jumlah perubahan.
- Stok sebelum perubahan.
- Stok sesudah perubahan.
- Waktu kejadian.
- Pengguna yang melakukan perubahan.
- Alasan atau referensi transaksi jika diperlukan.

Jenis pergerakan stok meliputi:

- Stok masuk manual.
- Pengurangan karena penjualan.
- Pengembalian stok karena pembatalan.
- Penyesuaian stok opname.

### FR-10 — Peringatan Stok Minimum

Sistem membandingkan stok tersedia dengan batas minimum produk.

Persyaratan:

- Batas minimum ditentukan Admin.
- Produk yang mencapai atau berada di bawah batas minimum diberi indikator peringatan.
- Peringatan ditampilkan di dashboard dan halaman stok.
- Daftar produk dapat difilter berdasarkan status stok.
- Sistem tidak boleh mengurangi stok di bawah nol melalui transaksi normal.

### FR-11 — Stok Opname

Alur stok opname:

1. Admin membuka modul stok opname.
2. Admin memilih produk.
3. Sistem menampilkan stok menurut database.
4. Admin memasukkan jumlah fisik.
5. Sistem menghitung selisih.
6. Admin memasukkan alasan penyesuaian.
7. Sistem menyimpan hasil dan memperbarui stok.
8. Sistem mencatat aktivitas dalam audit log.

Persyaratan:

- Penyesuaian stok harus memiliki pengguna dan waktu.
- Stok sebelum dan sesudah penyesuaian harus tersimpan.
- Selisih stok tidak boleh mengubah riwayat penjualan.
- Proses penyesuaian harus aman terhadap perubahan stok bersamaan.
- Riwayat stok opname dapat dilihat kembali.

### FR-12 — Pencatatan Pembelian Barang

Admin dapat mencatat pembelian barang yang ditujukan untuk operasional lounge.

Informasi pembelian:

- Nomor pembelian unik.
- Tanggal pembelian.
- Nama pemasok atau supplier, opsional.
- Daftar produk.
- Jumlah setiap produk.
- Satuan.
- Harga beli per unit.
- Total biaya per item.
- Total pembelian.
- Keterangan tambahan.
- Admin yang mencatat.

Persyaratan:

- Sistem menghitung total biaya secara otomatis.
- Riwayat pembelian dapat dicari dan difilter.
- Data pembelian tidak otomatis menambah stok.
- Penambahan stok harus dilakukan secara terpisah.
- Data pembelian yang telah digunakan untuk laporan tidak boleh dihapus tanpa jejak audit.
- Koreksi catatan pembelian harus dapat ditelusuri.

### FR-13 — Pengeluaran Operasional

Admin dapat mencatat pengeluaran operasional.

Informasi yang disimpan:

- Tanggal pengeluaran.
- Kategori pengeluaran.
- Nominal.
- Keterangan.
- Pengguna yang mencatat.
- Waktu pembuatan dan pembaruan.

Persyaratan:

- Nominal harus lebih besar dari nol.
- Kategori dan tanggal wajib diisi.
- Bukti foto atau file tidak diwajibkan.
- Admin dapat menambah, mengedit, dan melihat pengeluaran.
- Kasir tidak dapat mengakses modul ini.
- Pengeluaran ditampilkan terpisah dari omzet penjualan.

Kategori pengeluaran dapat dikelola sesuai kebutuhan operasional.

### FR-14 — Laporan Omzet Penjualan

Laporan menyediakan periode:

- Harian.
- Mingguan.
- Bulanan.
- Rentang tanggal khusus.

Informasi laporan:

- Total omzet.
- Jumlah transaksi valid.
- Total item terjual.
- Ringkasan metode pembayaran.
- Ringkasan penjualan berdasarkan periode.

Aturan perhitungan:

- Omzet dihitung berdasarkan transaksi yang berhasil dan tidak dibatalkan.
- Transaksi yang dibatalkan dikecualikan dari omzet bersih yang dilaporkan.
- Sistem tetap mempertahankan riwayat transaksi yang dibatalkan.
- Zona waktu aplikasi harus ditentukan secara konsisten, dengan Asia/Makassar sebagai konfigurasi awal yang disarankan.
- Periode laporan menggunakan batas waktu yang konsisten.

Omzet tidak boleh disebut sebagai laba bersih.

### FR-15 — Laporan Produk Terlaris

Sistem menampilkan peringkat produk berdasarkan jumlah unit terjual dalam periode yang dipilih.

Informasi:

- Nama produk.
- Kode produk.
- Kategori.
- Jumlah unit terjual.
- Nilai penjualan produk jika diperlukan.
- Periode laporan.

Persyaratan:

- Transaksi yang dibatalkan tidak dihitung sebagai penjualan.
- Pengembalian stok akibat pembatalan tidak boleh menyebabkan produk tetap dihitung sebagai penjualan valid.
- Admin dapat memfilter berdasarkan periode dan kategori.

### FR-16 — Laporan Stok Produk

Laporan menampilkan:

- Kode produk.
- Barcode.
- Nama produk.
- Kategori.
- Stok saat ini.
- Satuan.
- Batas minimum.
- Status stok.
- Riwayat perubahan stok jika dibuka melalui halaman detail.

Status stok dapat berupa:

- Aman.
- Stok minimum.
- Stok habis.

### FR-17 — Laporan Riwayat Pembelian

Laporan menampilkan:

- Nomor pembelian.
- Tanggal.
- Pemasok.
- Produk.
- Kuantitas.
- Harga beli.
- Total pembelian.
- Admin yang mencatat.

Laporan dapat difilter berdasarkan periode dan pemasok jika tersedia.

### FR-18 — Ekspor Excel dan PDF

Laporan omzet, produk terlaris, stok produk, dan riwayat pembelian dapat diekspor ke Excel dan PDF.

Persyaratan:

- Ekspor hanya tersedia bagi Admin.
- Ekspor mengikuti filter dan periode yang dipilih.
- File memiliki judul laporan dan periode.
- Format angka mata uang menggunakan Rupiah.
- File harus mencerminkan data yang sesuai dengan laporan di aplikasi.
- Data transaksi yang dibatalkan harus mengikuti aturan perhitungan laporan.
- Ekspor harus dilakukan melalui server dan tidak boleh memberikan akses kepada pengguna yang tidak berwenang.

### FR-19 — Pembatalan Transaksi

Aturan pembatalan:

**Sebelum pembayaran dikonfirmasi**
- Kasir dapat membatalkan pesanan.
- Pesanan yang belum selesai tidak dihitung sebagai penjualan.
- Stok hanya berubah jika sebelumnya memang sudah dicadangkan atau dikurangi sesuai implementasi; rancangan MVP menyarankan stok baru dikurangi saat transaksi berhasil.

**Setelah transaksi berhasil**
- Hanya Admin yang dapat membatalkan.
- Alasan pembatalan wajib diisi.
- Transaksi tidak boleh dihapus permanen.
- Sistem mencatat pengguna, waktu, alasan, dan transaksi terkait.
- Stok dikembalikan secara otomatis melalui transaksi database yang aman.
- Pengembalian stok hanya boleh terjadi satu kali.
- Transaksi yang sudah dibatalkan tidak dapat dibatalkan kembali.
- Laporan omzet dan produk terlaris mengecualikan transaksi yang dibatalkan.

### FR-20 — Audit Log

Audit log mencatat aktivitas penting, antara lain:

- Login yang relevan untuk keamanan.
- Pembuatan dan penonaktifan akun.
- Perubahan produk dan harga.
- Penambahan dan penyesuaian stok.
- Stok opname.
- Pencatatan atau koreksi pembelian.
- Pencatatan atau koreksi pengeluaran.
- Penyelesaian transaksi.
- Pembatalan transaksi.
- Perubahan konfigurasi penting.

Data audit minimal:

- Pengguna.
- Jenis aktivitas.
- Objek yang berubah.
- Waktu aktivitas.
- Nilai sebelum dan sesudah jika relevan.
- Alasan jika diwajibkan.

Audit log harus dilindungi dari pengubahan atau penghapusan oleh pengguna biasa.

---

## 7. Alur Operasional Utama

### 7.1 Alur penjualan

Login Kasir → Pilih produk → Atur jumlah → Validasi stok → Hitung total → Pilih pembayaran → Konfirmasi pembayaran → Simpan transaksi → Kurangi stok → Tampilkan struk.

### 7.2 Alur penambahan stok

Login Admin → Buka manajemen stok → Pilih produk → Masukkan jumlah stok masuk → Masukkan alasan atau referensi → Konfirmasi → Perbarui stok → Simpan riwayat.

### 7.3 Alur pembelian barang

Login Admin → Buka pembelian → Masukkan tanggal dan pemasok → Tambahkan detail barang → Masukkan kuantitas dan harga beli → Hitung total → Simpan catatan pembelian.

**Catatan:** penyimpanan pembelian tidak mengubah stok.

### 7.4 Alur stok opname

Login Admin → Pilih produk → Periksa stok sistem → Masukkan stok fisik → Tinjau selisih → Masukkan alasan → Konfirmasi → Perbarui stok → Catat audit.

### 7.5 Alur pembatalan transaksi

Login Admin → Buka riwayat transaksi → Pilih transaksi berhasil → Masukkan alasan → Konfirmasi pembatalan → Kembalikan stok secara atomik → Perbarui status transaksi → Catat audit.

---

## 8. Persyaratan Antarmuka dan UX

### 8.1 Prinsip desain

- Modern dan profesional.
- Responsif pada berbagai ukuran layar.
- Navigasi sederhana.
- Prioritas pada kecepatan transaksi.
- Informasi penting mudah ditemukan.
- Status stok dan transaksi terlihat jelas.
- Formulir memiliki validasi dan pesan kesalahan yang mudah dipahami.

### 8.2 Navigasi Admin

Menu utama:

1. Dashboard.
2. Transaksi.
3. Produk.
4. Kategori.
5. Manajemen stok.
6. Stok opname.
7. Pembelian.
8. Pengeluaran.
9. Laporan.
10. Pengguna.
11. Audit log.
12. Pengaturan akun.

### 8.3 Navigasi Kasir

Menu utama:

1. Penjualan.
2. Riwayat transaksi sesuai kewenangan.
3. Akun dan logout.

### 8.4 Tampilan kasir

- Area pencarian produk.
- Grid atau daftar produk.
- Keranjang di sisi layar yang sesuai ukuran perangkat.
- Total pembayaran terlihat jelas.
- Tombol konfirmasi mudah digunakan.
- Form pembayaran tunai dan QRIS.
- Dialog konfirmasi untuk tindakan penting.
- Pesan yang jelas saat stok tidak mencukupi.

### 8.5 Tampilan smartphone

- Navigasi yang ringkas.
- Formulir tidak memerlukan pengguliran horizontal.
- Tombol memiliki ukuran yang nyaman untuk disentuh.
- Tabel panjang dapat diganti dengan daftar atau kartu pada layar kecil.

---

## 9. Persyaratan Teknis

### 9.1 Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel |
| Bahasa backend | PHP |
| Frontend | Laravel Blade |
| CSS | Tailwind CSS |
| Database | MySQL |
| Autentikasi | Laravel authentication |
| Hak akses | Role dan authorization policies |
| Ekspor Excel | Pustaka ekspor Excel yang kompatibel |
| Ekspor PDF | Pustaka PDF yang kompatibel |
| Deployment | Server web yang mendukung Laravel dan MySQL |

Versi Laravel dan PHP yang digunakan harus ditetapkan berdasarkan versi stabil yang didukung pada saat implementasi, beserta kompatibilitas seluruh dependensi.

### 9.2 Prinsip arsitektur

- Gunakan pola MVC Laravel.
- Pisahkan validasi permintaan, logika bisnis, dan akses database.
- Gunakan Form Request untuk validasi input yang kompleks.
- Gunakan Policies atau Gates untuk pembatasan akses.
- Gunakan database transaction untuk proses penjualan, pembatalan, dan penyesuaian stok.
- Gunakan foreign key dan indeks database yang relevan.
- Gunakan soft delete atau status aktif/nonaktif pada data yang perlu mempertahankan riwayat.
- Simpan harga transaksi sebagai snapshot.
- Simpan seluruh nominal uang menggunakan tipe numerik yang tepat, bukan floating-point.
- Gunakan mekanisme aman untuk nomor transaksi unik.
- Gunakan pagination pada tabel dengan jumlah data besar.

### 9.3 Keamanan

- Password harus di-hash.
- Validasi seluruh input di sisi server.
- Proteksi CSRF pada formulir web.
- Proteksi terhadap SQL injection melalui query builder atau ORM.
- Pembatasan akses berbasis role.
- Pengaturan session yang aman.
- HTTPS wajib digunakan pada deployment produksi.
- Kredensial database disimpan dalam konfigurasi lingkungan yang aman.
- Audit log dilindungi dari perubahan tanpa otorisasi.
- Backup database harus dijadwalkan dan dapat dipulihkan.
- Data sensitif tidak boleh ditulis ke log aplikasi tanpa kebutuhan yang jelas.

### 9.4 Konsistensi transaksi dan stok

Proses penjualan harus:

1. Memulai database transaction.
2. Mengunci atau memvalidasi stok produk secara aman.
3. Memvalidasi harga dan jumlah.
4. Menyimpan transaksi dan detail.
5. Mengurangi stok.
6. Mencatat pergerakan stok.
7. Menyelesaikan transaksi database.

Jika terjadi kesalahan pada salah satu tahap, sistem harus melakukan rollback.

Strategi penguncian database harus mencegah dua kasir menjual stok terakhir secara bersamaan hingga stok menjadi negatif.

### 9.5 Kebutuhan jaringan

- Sistem wajib online.
- Transaksi baru tidak dapat diselesaikan tanpa koneksi ke server.
- Sistem tidak menyediakan penyimpanan transaksi offline untuk sinkronisasi.
- Jika koneksi terputus setelah permintaan transaksi dikirim, Kasir harus dapat memeriksa status transaksi sebelum mengulang.
- Deployment perlu menyediakan koneksi jaringan yang stabil antara perangkat kasir dan server.

---

## 10. Rancangan Entitas Database

Rancangan berikut merupakan model awal yang dapat disempurnakan pada tahap desain database.

### 10.1 users
- id
- username
- password
- role
- name
- is_active
- created_at
- updated_at

### 10.2 categories
- id
- name
- description
- is_active
- created_at
- updated_at

### 10.3 products
- id
- category_id
- sku
- barcode
- name
- selling_price
- current_stock
- minimum_stock
- unit
- image_path
- is_active
- created_at
- updated_at

### 10.4 sales
- id
- transaction_number
- cashier_id
- transaction_date
- subtotal
- total
- payment_method
- payment_amount, jika relevan
- change_amount, jika pembayaran tunai
- status
- cancelled_by
- cancelled_at
- cancellation_reason
- created_at
- updated_at

### 10.5 sale_items
- id
- sale_id
- product_id
- product_name_snapshot
- sku_snapshot
- quantity
- unit_price
- subtotal

### 10.6 stock_movements
- id
- product_id
- movement_type
- quantity_change
- stock_before
- stock_after
- reference_type
- reference_id
- reason
- user_id
- created_at

### 10.7 purchases
- id
- purchase_number
- purchase_date
- supplier_name
- total_amount
- notes
- recorded_by
- created_at
- updated_at

### 10.8 purchase_items
- id
- purchase_id
- product_id
- product_name_snapshot
- quantity
- unit
- purchase_price
- subtotal

### 10.9 expenses
- id
- expense_date
- category
- amount
- description
- recorded_by
- created_at
- updated_at

### 10.10 stocktakes
- id
- stocktake_number
- product_id
- system_stock
- physical_stock
- difference
- reason
- recorded_by
- created_at

### 10.11 audit_logs
- id
- user_id
- action
- entity_type
- entity_id
- before_data
- after_data
- reason
- created_at

### 10.12 Catatan desain database

- Nama tabel dan kolom final mengikuti konvensi Laravel.
- Tambahkan foreign key, indeks, dan constraint unik yang relevan.
- SKU dan nomor transaksi harus unik.
- Barcode harus unik jika terisi.
- Harga dan jumlah uang menggunakan tipe `DECIMAL` dengan presisi yang ditetapkan pada implementasi.
- Kuantitas stok menggunakan tipe numerik yang sesuai dengan satuan produk.
- Produk dan kategori yang memiliki riwayat tidak boleh dihapus secara permanen tanpa strategi retensi data.
- Data snapshot diperlukan agar perubahan nama atau harga produk tidak mengubah catatan transaksi lama.
- Nilai stok saat ini harus konsisten dengan seluruh pergerakan stok.
- Status transaksi harus memiliki nilai yang terdefinisi dan transisi status yang tervalidasi.

---

## 11. Aturan Bisnis Utama

| ID | Aturan |
|---|---|
| BR-01 | Sistem hanya memiliki role Admin dan Kasir pada MVP. |
| BR-02 | Hanya Admin yang dapat mengubah harga produk. |
| BR-03 | Kasir tidak dapat memberikan diskon. |
| BR-04 | Pembayaran hanya mendukung tunai dan QRIS. |
| BR-05 | Stok berkurang ketika transaksi berhasil. |
| BR-06 | Penambahan stok dilakukan manual oleh Admin. |
| BR-07 | Pencatatan pembelian tidak menambah stok otomatis. |
| BR-08 | Stok tidak boleh negatif melalui transaksi normal. |
| BR-09 | Transaksi berhasil tidak boleh dihapus permanen. |
| BR-10 | Pembatalan transaksi berhasil hanya dapat dilakukan Admin. |
| BR-11 | Pembatalan transaksi berhasil membutuhkan alasan dan audit log. |
| BR-12 | Pembatalan mengembalikan stok satu kali melalui proses yang aman. |
| BR-13 | Transaksi yang dibatalkan tidak dihitung sebagai penjualan valid. |
| BR-14 | Pengeluaran operasional dikelola Admin dan terpisah dari omzet. |
| BR-15 | Sistem online-only dan tidak menerima transaksi baru ketika offline. |
| BR-16 | Laporan dapat diekspor ke Excel dan PDF oleh Admin. |
| BR-17 | Pengelolaan stok dan pembelian harus mempertahankan riwayat perubahan. |
| BR-18 | Produk nonaktif tidak dapat dijual pada transaksi baru. |

---

## 12. Persyaratan Nonfungsional

### 12.1 Performa
- Halaman umum harus dirancang agar responsif pada beban operasional normal.
- Query laporan harus menggunakan indeks dan optimasi yang sesuai.
- Data transaksi besar harus ditampilkan menggunakan pagination.
- Ekspor laporan besar perlu menggunakan strategi yang tidak membebani memori secara berlebihan.

### 12.2 Keandalan
- Kegagalan transaksi harus menghasilkan rollback.
- Sistem harus mencegah transaksi duplikat akibat pengiriman ulang permintaan.
- Backup database harus tersedia.
- Proses pemulihan database harus diuji.

### 12.3 Kemudahan penggunaan
- Formulir memiliki pesan validasi yang jelas.
- Alur transaksi harus ringkas.
- Kesalahan stok dan pembayaran harus terlihat jelas.
- Tindakan pembatalan dan penyesuaian stok memerlukan konfirmasi yang sesuai.

### 12.4 Kompatibilitas
- Mendukung browser modern yang masih mendapat dukungan keamanan.
- Antarmuka responsif pada desktop, tablet, dan smartphone.
- Pencetakan thermal harus diuji menggunakan printer dan browser yang benar-benar akan digunakan.

### 12.5 Pemeliharaan
- Kode mengikuti standar Laravel dan PSR yang relevan.
- Konfigurasi tidak ditulis langsung dalam kode.
- Migrasi database dikelola melalui Laravel migrations.
- Proses deployment dan backup didokumentasikan.
- Automated test disediakan untuk logika bisnis kritis.

---

## 13. Kriteria Penerimaan MVP

MVP dianggap siap digunakan setelah skenario berikut berhasil diuji:

1. Admin dapat login dan membuat akun Kasir.
2. Kasir hanya dapat mengakses fitur yang diizinkan.
3. Admin dapat membuat kategori dan produk lengkap.
4. SKU dan barcode duplikat ditolak.
5. Kasir dapat mencari produk dan menyelesaikan transaksi.
6. Transaksi tunai menghitung total dan kembalian dengan benar.
7. Transaksi QRIS mencatat metode dan status pembayaran dengan benar.
8. Penjualan mengurangi stok satu kali.
9. Transaksi tidak dapat menyebabkan stok negatif.
10. Struk digital dapat dibuka dan struk thermal dapat dicetak pada perangkat yang didukung.
11. Admin dapat menambah stok tanpa membuat catatan pembelian.
12. Catatan pembelian tidak mengubah stok otomatis.
13. Stok opname menghitung selisih dan menyimpan audit.
14. Peringatan stok minimum tampil pada produk yang memenuhi kondisi.
15. Admin dapat mencatat pengeluaran operasional.
16. Admin dapat membatalkan transaksi berhasil dengan alasan.
17. Pembatalan mengembalikan stok satu kali.
18. Transaksi yang dibatalkan tidak masuk dalam omzet valid.
19. Laporan harian, mingguan, dan bulanan menampilkan angka yang benar.
20. Laporan produk terlaris menghitung unit terjual secara benar.
21. Laporan stok dan pembelian menampilkan data sesuai database.
22. Ekspor Excel dan PDF sesuai dengan filter laporan.
23. Audit log merekam perubahan penting.
24. Sistem menolak transaksi baru ketika koneksi server tidak tersedia.
25. Pengulangan permintaan transaksi tidak menghasilkan transaksi duplikat.

---

## 14. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Stok tidak sesuai dengan kondisi fisik | Stok opname, riwayat pergerakan, dan peringatan stok minimum |
| Transaksi ganda | Validasi status transaksi dan mekanisme idempotensi |
| Stok negatif | Database transaction, penguncian stok, dan validasi server |
| Pembatalan tanpa otorisasi | Role-based authorization dan audit log |
| QRIS dicatat berhasil tanpa pembayaran aktual | Prosedur konfirmasi pembayaran dan status pembayaran yang tervalidasi |
| Internet terputus | Infrastruktur jaringan yang stabil dan pemeriksaan status transaksi |
| Perubahan harga mengubah riwayat lama | Snapshot harga pada detail transaksi |
| Penghapusan data merusak laporan | Soft delete, status nonaktif, dan retensi data historis |
| Printer tidak kompatibel | Uji coba printer thermal dan browser pada perangkat operasional |
| Backup tidak dapat dipulihkan | Uji pemulihan database secara berkala |

---

## 15. Tahapan Pengembangan

### Tahap 1 — Fondasi aplikasi
- Setup Laravel dan MySQL.
- Autentikasi dan role.
- Layout Blade dan Tailwind CSS.
- Dashboard dasar.
- Migrasi database dan seeding awal.

### Tahap 2 — Produk dan persediaan
- Manajemen kategori.
- Manajemen produk.
- SKU dan barcode.
- Penambahan stok manual.
- Riwayat stok.
- Stok minimum dan stok opname.

### Tahap 3 — Penjualan
- Antarmuka kasir.
- Keranjang belanja.
- Pembayaran tunai dan QRIS.
- Validasi stok.
- Penyimpanan transaksi.
- Struk digital dan cetak thermal.

### Tahap 4 — Pembelian dan pengeluaran
- Pencatatan pembelian.
- Detail barang dan biaya pembelian.
- Pencatatan pengeluaran operasional.
- Riwayat dan filter data.

### Tahap 5 — Laporan dan audit
- Laporan omzet.
- Laporan produk terlaris.
- Laporan stok.
- Laporan pembelian.
- Ekspor Excel dan PDF.
- Audit log.
- Pembatalan transaksi.

### Tahap 6 — QA dan deployment
- Pengujian fungsional.
- Pengujian role dan keamanan.
- Pengujian transaksi bersamaan.
- Pengujian pembatalan dan stok.
- Pengujian responsivitas.
- Pengujian printer thermal.
- Pengujian ekspor.
- Backup, deployment, dan dokumentasi operasional.

---

## 16. Hal yang Perlu Dikonfirmasi Sebelum Implementasi

Beberapa keputusan berikut belum ditetapkan secara eksplisit dan harus diselesaikan sebelum pengembangan terkait dimulai:

1. Versi Laravel, PHP, dan MySQL yang digunakan.
2. Infrastruktur hosting dan konfigurasi jaringan lounge.
3. Apakah harga produk sudah termasuk pajak atau biaya layanan.
4. Format nomor transaksi dan nomor pembelian.
5. Kebijakan konfirmasi pembayaran QRIS.
6. Format serta ukuran kertas printer thermal.
7. Format satuan stok, termasuk apakah kuantitas hanya bilangan bulat.
8. Aturan pengeditan transaksi pembelian dan pengeluaran setelah disimpan.
9. Periode penyimpanan audit log dan transaksi.
10. Target performa, kapasitas pengguna bersamaan, backup, dan waktu pemulihan.
11. Kebutuhan tampilan identitas Bluesea Lounge, logo, dan format struk.

Keputusan tersebut dapat ditetapkan tanpa mengubah fondasi utama PRD.

---

## 17. Definisi Selesai (Definition of Done)

Fitur dianggap selesai apabila:

- Implementasi memenuhi persyaratan fungsional.
- Hak akses telah diuji.
- Validasi backend berfungsi.
- Transaksi dan perubahan stok konsisten.
- Penanganan kesalahan telah diuji.
- Audit log berfungsi untuk tindakan terkait.
- Antarmuka responsif pada perangkat target.
- Pengujian otomatis dan pengujian manual yang relevan lulus.
- Dokumentasi penggunaan tersedia.
- Perubahan database dan langkah deployment terdokumentasi.
- Fitur telah ditinjau dan disetujui oleh pihak yang bertanggung jawab atas Bluesea Lounge.

---

## 18. Kesimpulan

Bluesea Lounge POS dirancang sebagai aplikasi web terpusat untuk mengelola penjualan makanan dan minuman, stok produk siap jual, pembelian barang, pengeluaran operasional, serta pelaporan.

Fondasi implementasinya adalah Laravel, MySQL, Laravel Blade, dan Tailwind CSS, dengan fokus pada keamanan, konsistensi transaksi, pemisahan hak akses, serta riwayat perubahan yang dapat ditelusuri.

Prioritas pengembangan adalah memastikan proses penjualan dan pengurangan stok berjalan akurat terlebih dahulu, kemudian melengkapi pengelolaan persediaan, pencatatan pembelian dan pengeluaran, laporan, ekspor, serta audit log.

PRD ini menjadi acuan awal bagi proses desain sistem, penyusunan skema database final, implementasi backend dan frontend, serta pengujian sebelum sistem digunakan di lingkungan operasional Bluesea Lounge.
