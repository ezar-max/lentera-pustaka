# Lentera Pustaka — Aplikasi Web Perpustakaan Digital

**Lentera Pustaka** adalah sistem informasi perpustakaan digital berbasis web (Laravel + Blade + Tailwind CSS + Alpine.js + Chart.js + DomPDF + Gemini AI) yang mengimplementasikan alur sirkulasi peminjaman 7 tahap, pengelolaan stok total vs stok tersedia secara presisi dalam transaksi database, sistem denda berbasis poin, laporan PDF, visualisasi grafik statistik, serta layanan konsultasi hibrida (Chatbot AI Gemini & Live Chat Petugas/Admin).

---

## Fitur Utama

1. **Alur Keanggotaan dengan Persetujuan Admin**:
   - Pendaftaran anggota baru otomatis berstatus `pending` dan baru dapat login setelah disetujui (`aktif`) oleh Administrator.
   - Jika ditolak (`ditolak`), alasan penolakan wajib diisi oleh Admin dan ditampilkan melalui popup saat calon member mencoba masuk.
2. **Sirkulasi Peminjaman & Pengembalian 7 Tahap (`PeminjamanService`)**:
   - Validasi berlapis: akun aktif, poin denda di bawah ambang blokir (`< 50` poin), tidak sedang meminjam buku yang sama, `stok_tersedia > 0`, dan kuota pinjaman aktif maksimal `3` buku.
   - Alur status: `diajukan` $\rightarrow$ `ditolak` (wajib alasan) | `disetujui` (`stok_tersedia - 1`) $\rightarrow$ `dibatalkan` (otomatis jika lewat 2 hari tidak diambil, `stok_tersedia + 1`) | `dipinjam` (durasi 7 hari) $\rightarrow$ `pengajuan_kembali` $\rightarrow$ `kembali_disetujui` $\rightarrow$ `selesai`.
   - Pengembalian dua tahap dengan pemeriksaan kondisi fisik buku (`baik`, `rusak`, `hilang`) dan ketepatan waktu otomatis.
3. **Sistem Denda Berbasis Poin (`DendaService`)**:
   - Terlambat: `2 poin/hari`, Kondisi Rusak: `25 poin`, Buku Hilang: `100 poin` (untuk buku hilang, `stok_total` dan `stok_tersedia` berkurang permanen).
   - Akumulasi poin denda berstatus `aktif` disimpan pada `users.total_poin_denda`. Jika mencapai `50 poin`, tombol pengajuan pinjam otomatis dinonaktifkan hingga petugas menandai denda lunas.
4. **Chatbot AI (Gemini) & Chat Live Petugas/Admin (`ChatbotService`)**:
   - Chatbot AI menyusun konteks dinamis berisi daftar buku & stok, aturan peminjaman, serta ringkasan pinjaman & poin denda member.
   - Dilengkapi tombol **Hubungi Petugas** untuk membuka sesi percakapan langsung dengan petugas/admin yang diperbarui otomatis menggunakan *AJAX polling* setiap 4 detik.
5. **Laporan PDF & Grafik Statistik (`LaporanService` & `StatistikService`)**:
   - Cetak laporan PDF (Peminjaman, Stok Buku, Denda, dan Keanggotaan) menggunakan `barryvdh/laravel-dompdf`.
   - 6 Grafik interaktif Chart.js: Peminjaman per Bulan, 10 Buku Terpopuler, Kategori Terbanyak Dipinjam, Komposisi Status, Total Poin Denda per Bulan, dan Member Baru per Bulan (Admin).
6. **Desain UI & Popup Kustom (`SweetAlert2` + Tailwind Tokens)**:
   - Palet warna terstandarisasi: Forest Green (`#183B32`), Warm Gold (`#D9A441`), Ivory (`#F7F3E8`), Sage (`#6B7C70`), dan Charcoal (`#292D2A`) dengan radius sudut konsisten `15px` (`rounded-card`).
   - Seluruh konfirmasi aksi berisiko, form input alasan penolakan, konfirmasi kondisi pengembalian buku, dan notifikasi flash ditampilkan menggunakan popup/toast JavaScript kustom (`resources/js/popup.js`).

---

## Cara Instalasi & Menjalankan Proyek

1. **Instal dependensi PHP & JavaScript**:
   ```bash
   composer install
   npm install
   ```

2. **Konfigurasi file `.env`**:
   Salin `.env.example` ke `.env` (jika belum ada) dan buat application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   **Pengaturan Database (MySQL/MariaDB atau SQLite)**:
   Secara bawaan `.env` siap dijalankan langsung menggunakan SQLite (`DB_CONNECTION=sqlite`) maupun MySQL/MariaDB. Untuk menggunakan MySQL/MariaDB, sesuaikan bagian berikut di `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lentera_pustaka
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   **Pengaturan API Key Gemini (Chatbot AI)**:
   Tambahkan API Key Google Gemini Anda pada `.env`:
   ```env
   GEMINI_API_KEY=masukkan_api_key_gemini_anda_disini
   GEMINI_MODEL=gemini-2.5-flash
   ```
   *(Catatan: Kunci API selalu dibaca dari sisi server melalui `config/perpustakaan.php` dan tidak pernah diekspos ke frontend. Apabila `GEMINI_API_KEY` dikosongkan, `ChatbotService` otomatis menggunakan penjawab lokal berbasis konteks katalog dan aturan database sehingga fitur chat tetap dapat diuji tanpa kendala).*

3. **Jalankan Migrasi, Seeder, dan Storage Link**:
   ```bash
   php artisan migrate:fresh --seed
   php artisan storage:link
   ```

4. **Build Aset Frontend & Jalankan Server**:
   ```bash
   npm run build
   php artisan serve
   ```
   Buka aplikasi di peramban pada alamat `http://localhost:8000`.

5. **Menjalankan Command Terjadwal & Pengujian Otomatis**:
   ```bash
   # Membatalkan pengajuan disetujui yang tidak diambil > 2 hari & mengembalikan stok
   php artisan perpustakaan:batalkan-kadaluarsa

   # Menghitung keterlambatan harian pada pinjaman berjalan
   php artisan perpustakaan:hitung-keterlambatan

   # Menjalankan pengujian otomatis (PHPUnit)
   php artisan test
   ```

---

## Daftar Akun (Seeder)

Secara bawaan, database hanya menyisakan **1 akun Administrator**:

| Role | Username | Email | Kata Sandi | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin@gmail.com` | `admin123` | `aktif` |

---

## Catatan Asumsi Implementasi

1. **Pembatalan Pengajuan oleh Member**: Member dapat membatalkan sendiri pengajuan peminjamannya selama statusnya masih `diajukan` (belum disetujui petugas), sehingga statusnya berubah menjadi `dibatalkan` tanpa memengaruhi stok buku karena stok baru dikurangi saat petugas menyetujui (`disetujui`).
2. **Perlakuan Stok pada Buku Hilang**: Saat pengajuan disetujui petugas, `stok_tersedia` telah dikurangi `1`. Ketika buku dikonfirmasi `hilang` saat pengembalian, sistem mengurangi `stok_total` sebesar `1` dan tidak menambah `stok_tersedia`, sehingga baik `stok_total` maupun `stok_tersedia` berkurang secara permanen sebesar 1 eksemplar dari keadaan semula.
3. **Pewarisan Fitur Petugas untuk Administrator**: Seluruh rute modul petugas (`/buku`, `/kategori`, `/peminjaman`, `/pengembalian`, `/denda`, `/laporan`, `/statistik`, `/chat`) didaftarkan baik di bawah prefix `/petugas/...` maupun `/admin/...`, sehingga Administrator dapat menjalankan seluruh tugas petugas langsung dari panel `/admin/...` maupun mengakses `/petugas/...`.
4. **Konfigurasi Parameter Aturan**: Semua parameter angka aturan bisnis disimpan secara terpusat di `config/perpustakaan.php` dan dapat diubah melalui halaman `/admin/pengaturan`.
