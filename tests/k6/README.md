# k6 Performance Testing — SampahRute

Folder ini berisi skrip pengujian performa (**Load Testing** dan **Stress Testing**) menggunakan [k6](https://k6.io/) untuk menguji ketahanan, responsivitas, dan batas kapasitas server aplikasi SampahRute.

---

## 📁 File Skrip

| File | Deskripsi | Target Virtual Users (VU) | Durasi |
| :--- | :--- | :--- | :--- |
| `load_test.js` | Menguji performa di bawah beban lalu lintas pengguna normal & bertahap (browsing publik, ekstraksi CSRF, login, dan dashboard) | 20 hingga 50 VUs | ~4 menit |
| `stress_test.js` | Menguji titik ketahanan maksimum server di bawah beban ekstrim hingga batas kapasitas (stress point) | 50 hingga 300 VUs | ~4.5 menit |

---

## ⚙️ Instalasi k6

### Windows (Menggunakan Winget / Chocolatey / Scoop):
```powershell
# Menggunakan winget:
winget install k6 --source winget

# Atau menggunakan Chocolatey:
choco install k6
```

### macOS:
```bash
brew install k6
```

### Linux (Debian / Ubuntu):
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

---

## 🚀 Cara Menjalankan Pengujian

Pastikan aplikasi SampahRute sedang aktif (`php artisan serve` pada port `8000` atau melalui Laragon).

### 1. Menjalankan Load Test (Beban Normal)
```bash
k6 run tests/k6/load_test.js
```

### 2. Menjalankan Stress Test (Beban Ekstrim)
```bash
k6 run tests/k6/stress_test.js
```

### 3. Mengubah Target Base URL (Opsional)
Jika aplikasi berjalan pada host lain (seperti virtual host Laragon):
```bash
k6 run -e BASE_URL=http://sampahrute.test tests/k6/load_test.js
```

---

## 📊 Metrik Evaluasi (Thresholds)
- **`http_req_duration`**: Mengukur latensi / waktu respon server (persentil p90 dan p95).
- **`http_req_failed`**: Persentase request yang gagal/error (HTTP status bukan 2xx/3xx).
- **`custom_error_rate`**: Memvalidasi integritas konten halaman dan autentikasi.
