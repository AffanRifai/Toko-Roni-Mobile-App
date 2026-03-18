<div align="center">
  <h1>🛒 Toko Roni - Core Engine & API Backend</h1>
  <p>
    <strong>Sistem Manajemen Toko Terpadu & Backend API untuk Aplikasi Mobile Toko Roni</strong>
  </p>
</div>

---

## 📋 Deskripsi Aplikasi

**Toko Roni Core** merupakan jantung operasional (Backend & Web Dashboard) dari ekosistem Point of Sales (POS) dan Supply Chain Toko Roni. Dibangun dengan framework **Laravel 11**, sistem ini tidak hanya melayani operasional manajemen toko berbasis web, melainkan bertindak sebagai *API Gateway* tingkat produksi (Production-Grade) yang mentenagai Aplikasi Mobile Toko Roni. 

Aplikasi ini dilengkapi dengan fitur **Manajemen Pengiriman (Logistik)**, **Sistem Notifikasi Real-time**, serta mekanisme keamanan **Biometrik Face Recognition Login** terintegrasi menggunakan FaceAPI.js.

## 🚀 Fitur Unggulan

### 1. **Production-Grade API Gateway**
Sistem telah dirancang standar industri untuk mensuplai data aplikasi Mobile:
- **Global Error Handling**: Pencegahan tumpahan HTML trace, seluruh *Exception* (404, 401, 500, 422) diformat 100% menggunakan `JSON`.
- **Standarisasi Respons**: Konfigurasi Trait seragam `{ success, message, data }` pada seluruh *endpoint*.
- **Anti-DDoS & Throttling**: Proteksi *Rate-Limiter* ketat (60 *request/minute* untuk API Global, 5 *request/minute* untuk Autentikasi).
- **Sanctum Authentication**: Manajemen token sesi aman untuk berbagai *device*.

### 2. **RBAC & Multi-Level Workspace**
Dukungan *Role-Based Access Control* (RBAC) yang spesifik pada struktur hierarki retail:
- **Owner**: Laporan komprehensif, finansial, dan audit operasional.
- **Admin**: Manajemen konfigurasi produk, kategori, serta pengaturan pengguna.
- **Kasir**: Modul *Point of Sales* terpadu dengan sinkronisasi inventori.
- **Kepala Gudang**: *Stock tracking* dan pengadaan barang (*supply*).
- **Logistik**: Manajemen armada kendaraan, penjadwalan, dan penugasan kiriman.
- **Kurir/Driver**: Dedicated *"My Deliveries"* dashboard untuk *update* status *real-time* ke sistem pusat.

### 3. **Smart Security & Biometrics**
- **Face Recognition**: Algoritma AI *FaceAPI.js* terintegrasi secara asinkron di klien untuk *Scan and Go*.
- Penilaian *Threshold* pengenalan wajah jarak 0.6 dengan enkripsi JSON Matrix.

### 4. **Asynchronous Notification System**
- Interval *polling* pintar (*Back-end Server Polling*).
- Antarmuka *Global Toast UI Widget* otomatis muncul di dashboard tanpa *reload*.

---

## 🛠️ Tech Stack & Ekosistem

| Lapisan | Teknologi | Peran |
|-------------|-----------|-------|
| **Backend** | Laravel 11 (PHP 8.2+) | Engine Utama & API Server |
| **Database** | MySQL 8.x | RDBMS Penyimpanan Utama |
| **Frontend** | Blade, TailwindCSS 3 | UI/UX Rendering Web Cepat |
| **Integrasi** | jQuery, FaceAPI.js, Chart.js| DOM Asynchronous, AI Klien, Analitik |
| **Keamanan**| Sanctum, RateLimiter, WebRTC | Autentikasi Token API & Kamera |

---

## 📁 Struktur Inti Arsitektur

```text
tokoroni-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # Controller API Mobile App
│   │   │   └── Web/          # Controller Web Dashboard
│   │   ├── Middleware/       # Keamanan (Role, ForceJsonResponse)
│   ├── Models/               # Skema Relasional ORM
│   ├── Traits/               # ApiResponseTrait (Keseragaman Data)
├── database/
│   ├── migrations/           # Skema Basis Data DDL
│   ├── seeders/              # Seeder Integrasi (Data Dummy Realistis)
├── public/
│   └── models/               # Weighted Data Model FaceAPI
├── routes/
│   ├── api.php               # Rute Endpoints Mobile Auth & Fitur
│   └── web.php               # Rute Web Dashboard Monolitik
```

---

## 🔧 Panduan Instalasi (Development)

Proses perancangan ekosistem secara lokal:

### 1. Kebutuhan Sistem Terkini
- **PHP** versi `^8.2`
- **Composer** `v2`
- **Node.js** & NPM
- **MySQL** versi `>= 5.7`

### 2. Kloning & Dependensi
```bash
git clone https://github.com/AffanRifai/Toko-Roni-Mobile-App.git
cd tokoroni-app

composer install
npm install
npm run build
```

### 3. Konfigurasi Sistem
Duplikasi environment konfigurasi dan siapkan *Key*:
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan parameter koneksi PDO (MySQL) Anda pada berkas `.env`.

### 4. Migrasi & Seeding Data Realistis
Proyek ini berisi *Seeder* canggih untuk simulasi data pasar riil (sembako, pengguna, kendaraan).
```bash
php artisan migrate:fresh --seed
```

### 5. Setup FaceAPI Models
Engine biometrik memerlukan parameter model AI awal.
```bash
mkdir -p public/models
# Silakan unduh manifest FaceAPI ke direktori ini secara manual jika fitur biometrik digunakan.
```

### 6. Jalankan Server
```bash
php artisan serve
```

Akses sistem di `http://localhost:8000`.

---

## 📡 Daftar Endpoint API

Semua rute bernaung di bawah awalan versi `/api/v1/`.

| Grup | Endpoint Utama | Metrik Throttle | Fungsi |
|------|---------------|----------------|--------|
| **Auth** | `POST /auth/login` | *Strict* (5/min) | Login Kredensial & Terbitkan Token |
| **Auth** | `POST /auth/face-login` | *Strict* (5/min) | Autentikasi berbasis AI |
| **Dashboard**| `GET /dashboard/` | *Global* (60/min) | Analitik Beranda Global |
| **Logistik** | `GET /deliveries/my-deliveries`| *Global* (60/min) | Pekerjaan Kurir Aktif |
| **Products** | `GET /products/` | *Global* (60/min) | Katalog Barang dan Stok |

*(Impor koleksi di dalam proyek ini ke **Postman** untuk menjelajahi fungsionalitas Payload lebih dalam).*

---

## 👥 Pengujian *Role Access* Default

Data telah digenerate (via seeder) dengan rincian login untuk kemudahan pengujian:
- **Owner**: `owner@tokoroni.com` | `password`
- **Admin**: `admin@tokoroni.com` | `password`
- **Kasir**: `kasir@tokoroni.com` | `password`
- **Gudang**: `gudang@tokoroni.com` | `password`
- **Logistik**: `logistik@tokoroni.com` | `password`
- **Kurir**: `kurir_budi@tokoroni.com` | `password`
- **Driver**: `driver_agus@tokoroni.com` | `password`

---

## 👨‍💻 Kontributor

- **Affan Rifaiz** - UI/UX Designer & Mobile App Engineer
- **Faiz J** - Lead Back-end Engineer
- **Tio R** - Full-stack Developer
- **Fadhlan M R** - Front-End Engineer

🚀 **Toko Roni Framework** - Copyright © 2024 All Rights Reserved.
