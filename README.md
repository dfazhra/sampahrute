# SampahRute — Sistem Monitoring & Pelaporan Pengangkutan Sampah Kota Surakarta

Aplikasi web berbasis **Laravel 11 + SQLite + Blade** untuk memantau jadwal pengangkutan sampah, mencatat pengangkutan harian oleh petugas lapangan, dan mengelola laporan warga di Kota Surakarta.

---

## Cara Menjalankan Aplikasi

### A. Langsung Menjalankan (Jika Proyek Sudah Terkonfigurasi)
Jika folder `vendor/`, file `.env`, dan database `database/database.sqlite` sudah tersedia:

```bash
php artisan serve
```

Lalu buka di browser:
- **Beranda Publik**: `http://localhost:8000`
- **Halaman Login**: `http://localhost:8000/login`
- **Pendaftaran Warga Baru**: `http://localhost:8000/register`

---

### B. Instalasi dari Awal (Clone Baru / Komputer Lain)
Jika proyek baru saja di-*clone* dari repositori Git:

```bash
# 1. Instal dependensi PHP melalui Composer
composer install

# 2. Salin konfigurasi environment & buat APP_KEY
cp .env.example .env
php artisan key:generate

# 3. Buat file database SQLite (jika belum ada)
# Di PowerShell (Windows):
New-Item -ItemType File -Path database/database.sqlite -Force

# 4. Jalankan migrasi tabel & isi data awal (Seeder Surakarta)
php artisan migrate:fresh --seed

# 5. Jalankan server pengembangan Laravel
php artisan serve
```

---

## Akun Bawaan (Seeder)

Di halaman `/login`, tersedia tombol pengisian cepat (**Admin / Petugas / Warga**) atau Anda dapat masuk manual menggunakan akun berikut:

| Peran | Username | Password | Nama | Wilayah / Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Rahmat Hidayat | Semua wilayah (Pengelola Lingkungan) |
| **Admin** | `lina.admin` | `admin123` | Lina Marlina | Semua wilayah (Pengelola Lingkungan) |
| **Petugas** | `petugas` | `petugas123` | Agus Wibowo | `PTG-01` — Banjarsari & Laweyan |
| **Petugas** | `joko` | `petugas123` | Joko Susilo | `PTG-02` — Jebres & Pasar Kliwon |
| **Petugas** | `wahyu` | `petugas123` | Wahyu Hidayat | `PTG-03` — Serengan |
| **Warga** | `warga` | `warga123` | Andi Pratama | Kecamatan Banjarsari |
| **Warga** | `rina.putri` | `warga123` | Rina Putri | Kecamatan Jebres |

> **Reset Data ke Kondisi Awal:** Klik tombol **↺ Reset data demo** di bagian bawah *sidebar* dashboard, atau jalankan `php artisan migrate:fresh --seed`.

---

## Fitur Utama Backend & Frontend

1. **Autentikasi Berbasis Database & Role Middleware (`role:admin`, `role:petugas`, `role:warga`)**
   - Login menggunakan `username` atau `email` dengan `Hash` password Laravel (`Auth::login`).
   - Proteksi rute otomatis berdasarkan peran menggunakan middleware `App\Http\Middleware\EnsureUserRole`.
   - **Registrasi Akun Warga** (`/register`) agar warga baru dapat langsung mendaftar dan membuat laporan.

2. **Panel Admin (`/admin/*`)**
   - **Dashboard Analitik (`/admin/dashboard`)**: Statistik laporan 7 hari terakhir, komposisi status laporan, lokasi hampir penuh ($\ge 85\%$), dan progres pengangkutan harian.
   - **Kelola Laporan Warga (`/admin/laporan`)**: Verifikasi laporan masuk, tolak laporan disertai alasan, atau tugaskan ke petugas wilayah.
   - **Kelola Lokasi (`/admin/lokasi`)**: Tambah, ubah, dan hapus titik TPS/Depo/Kontainer dengan pemilih koordinat peta interaktif (Leaflet).
   - **Kelola Jadwal (`/admin/jadwal`)**: Atur hari pengangkutan, rentang jam, dan petugas penanggung jawab untuk setiap titik.
   - **Monitoring Riwayat Pengangkutan (`/admin/riwayat`)**: Kalender ketepatan 14 hari terakhir (`tepat`, `terlambat`, `terlewat`) dan ekspor CSV.
   - **Kelola Pengguna (`/admin/pengguna`)**: Tambah, ubah, dan hapus akun Admin, Petugas, maupun Warga.

3. **Panel Petugas (`/petugas/*`)**
   - **Dashboard & Peta Rute Harian (`/petugas/dashboard`)**: Urutan titik pengangkutan terjadwal hari ini beserta visualisasi rute di peta.
   - **Tugas Hari Ini (`/petugas/tugas`)**: Mulai pengangkutan (`diproses`), konfirmasi selesai diangkut (`selesai`) beserta jam aktual, volume ($\text{m}^3$), catatan, dan unggah foto bukti (`public/uploads/pickups`), serta selesaikan laporan warga yang ditugaskan.
   - **Riwayat Pengangkutan Saya (`/petugas/riwayat`)**: Log pengangkutan khusus petugas yang sedang login.

4. **Portal Warga (`/warga/*`)**
   - **Dashboard Warga (`/warga/dashboard`)**: Ringkasan laporan aktif dan jadwal angkut di kecamatan sekitar.
   - **Buat Laporan (`/warga/laporan/buat`)**: Kirim laporan masalah sampah dengan pemilihan titik di peta dan unggah foto bukti (`public/uploads/reports`).
   - **Laporan Saya & Timeline Detail (`/warga/laporan/{id}`)**: Pantau riwayat tahap penanganan laporan dari dikirim, diverifikasi admin, ditugaskan ke petugas, hingga selesai.

---

## Struktur Database & File Penting

- **Database SQLite**: `database/database.sqlite`
- **Migrasi**:
  - `database/migrations/0001_01_01_000000_create_users_table.php` (`users`, `sessions`)
  - `database/migrations/2026_01_01_000003_create_sampahrute_tables.php` (`officers`, `locations`, `reports`, `pickup_logs`)
- **Model Eloquent**:
  - `app/Models/User.php`
  - `app/Models/Officer.php`
  - `app/Models/Location.php`
  - `app/Models/Report.php`
  - `app/Models/PickupLog.php`
- **Controller & Layanan Data**:
  - `app/Http/Controllers/SampahRuteDemoController.php`
  - `app/Support/DemoData.php`
  - `app/Http/Middleware/EnsureUserRole.php`
- **Seeder**:
  - `database/seeders/DatabaseSeeder.php`

---

## Menjalankan Pengujian Otomatis (Testing)

Untuk menjalankan pengujian fitur otomatis (halaman publik, autentikasi, *role middleware*, registrasi warga, pembuatan laporan, CRUD admin, dan konfirmasi pengangkutan petugas):

```bash
php artisan test
```
