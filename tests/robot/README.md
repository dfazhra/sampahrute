# Robot Framework Automated Tests — SampahRute

Folder ini berisi suite pengujian End-to-End (E2E) menggunakan **Robot Framework** dan **SeleniumLibrary** untuk menguji alur pengguna (user flow) utama pada aplikasi SampahRute.

---

## 📁 Struktur Direktori

```text
tests/robot/
├── resources/
│   └── common.resource          # Konfigurasi variabel, reusable keywords (login, logout, browser setup)
├── 01_auth_testing.robot        # Skenario login & register (positif & negatif)
├── 02_laporan_testing.robot     # Skenario warga membuat laporan & cek laporan saya
├── 03_admin_petugas_flow.robot  # Skenario alur admin (kelola laporan) & petugas (tugas harian)
└── README.md
```

---

## ⚙️ Persyaratan (Prerequisites)

1. **Python 3.8+**
2. **Web Browser & Driver** (Google Chrome & chromedriver yang sesuai)
3. Instal library pengujian melalui terminal:
   ```bash
   pip install robotframework robotframework-seleniumlibrary
   ```

---

## 🚀 Cara Menjalankan Pengujian

Pastikan server aplikasi SampahRute sedang berjalan (misalnya pada port `8000`):
```bash
php artisan serve
```

### 1. Menjalankan Seluruh Suite Pengujian
```bash
robot -d tests/robot/results tests/robot/
```
*(Hasil laporan HTML akan tersimpan di direktori `tests/robot/results/`)*

### 2. Menjalankan Suite Secara Terpisah
- **Hanya Auth (Login & Register):**
  ```bash
  robot -d tests/robot/results tests/robot/01_auth_testing.robot
  ```
- **Hanya Pengujian Laporan Warga:**
  ```bash
  robot -d tests/robot/results tests/robot/02_laporan_testing.robot
  ```
- **Hanya Alur Admin & Petugas:**
  ```bash
  robot -d tests/robot/results tests/robot/03_admin_petugas_flow.robot
  ```

### 3. Menjalankan di Mode Headless (Tanpa Tampilan Browser)
Cocok untuk lingkungan CI/CD atau background execution:
```bash
robot -d tests/robot/results -v HEADLESS:true tests/robot/
```

### 4. Menyesuaikan Base URL (Misal menggunakan Virtual Host Laragon)
```bash
robot -d tests/robot/results -v BASE_URL:http://sampahrute.test tests/robot/
```
