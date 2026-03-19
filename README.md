<div align="center">
  <img src="https://readme-typing-svg.herokuapp.com?font=Fira+Code&weight=600&size=30&pause=1000&color=2563EB&center=true&vCenter=true&width=800&lines=Toko+Roni+-+Point+of+Sales;Toko+Roni+-+Logistics+API;Toko+Roni+-+Face+Recognition+Engine;Toko+Roni+-+Real-time+Dashboard" alt="Typing SVG" />
  
  <p align="center">
    <strong>Sistem Manajemen Retail, Logistik, dan API Backend Berstandar Produksi</strong>
  </p>

  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"/>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
    <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind"/>
    <img src="https://img.shields.io/badge/FaceAPI.js-FFB020?style=for-the-badge&logo=javascript&logoColor=white" alt="FaceAPI"/>
  </p>
</div>

---

## 📖 Tentang Toko Roni

**Toko Roni Core** adalah lebih dari sekadar aplikasi *Point of Sales* kasir biasa. Sistem ini merupakan sebuah arsitektur hibrida (*hybrid architecture*) yang menggabungkan kemudahan manajemen *Web Dashboard* monolitik untuk back-office, sekaligus berfungsi sebagai **API Gateway** level-produksi (Production-Grade) bertenaga tinggi yang menyuplai data secara asinkron ke Aplikasi Mobile Toko Roni (Frontend Android/iOS).

Selain fungsi inti penjualan, aplikasi ini dibekali **Sistem Biometrik AI (Face Recognition)** yang memungkinkan otentikasi sentuhan-nol (*zero-touch authentication*) untuk mempercepat transaksi kasir dan login harian. Fitur supply chain canggih memungkinkan armada kurir memantau status kiriman logistik menggunakan perangkat seluler mereka masing-masing secara real-time.

---

## 🔥 Fitur Utama & Kapabilitas Sistem

<img align="right" width="300" src="https://raw.githubusercontent.com/ABSphreak/ABSphreak/master/gifs/code.gif" alt="Coding Animation">

### 1. **Production-Grade API Gateway (Mobile Backbone)**
Arsitektur API telah dikalibrasi untuk stabilitas produksi:
- **Global Data Standardization**: Melalui `ApiResponseTrait`, setiap pengiriman data dari web ke aplikasi mobile dijamin berstruktur baku: `{ "success": boolean, "message": string, "data": [] }`.
- **Bulletproof Exception Handling**: Bebas kebocoran *HTML Stack Trace*. Jika sistem rontok (500), salah otentikasi (401), form tidak valid (422), atau halaman tidak ditemukan (404), sistem memaksakan respons JSON 100% menggunakan middleware `ForceJsonResponse`.
- **DDoS Mitigation & Rate Limiting**: Limitasi eksekusi cerdas—maksimum **60 request/menit** untuk rute data global, dan perlindungan ekstra ketat **5 request/menit**  pada jalur autentikasi untuk memblokir aktivitas *brute-force*.

### 2. **Artificial Intelligence & Keamanan Biometrik**
- **FaceAPI.js Real-time Scan**: Pemodelan AI untuk konversi *Face Descriptor*. Sistem sanggup membedakan struktur tulang wajah jarak dekat untuk proses autentikasi.
- Tingkat perhitungan akurasi euclidean distance dikunci dengan threshold **0.60** di dalam skema enkripsi matriks JSON.

### 3. **Hierarki Bebasis Peran (RBAC) Canggih**
Birokrasi manajemen disokong *Role-Based Access Control* berlapis:
- 👑 **Owner**: Analitik dashboard penuh, agregat penjualan bulanan, dan histori modal.
- 👨‍💻 **Admin**: Manajemen data master, registrasi inventori/kategori, dan konfigurasi profil user.
- 🛒 **Kasir**: Modul *Point of Sales*, struk penjualan komprehensif.
- 📦 **Kepala Gudang**: *Supply management* pengadaan barang (*in-bound logistics*).
- 🚚 **Logistik**: Penjadwalan, kontrol armada kendaraan operasional.
- 🏍️ **Kurir/Driver**: Dedicated portal **My Deliveries** yang memberikan perintah pengiriman satu arah agar supir tahu kemana paket dialokasikan.

### 4. **Asynchronous Notification Ecosystem**
Sistem menolak konsep *page refresh* (F5).
- Web Dashboard secara konsisten melakukan *background polling* untuk menarik notifikasi perintah terbaru (AJAX).
- Notifikasi akan terpotret di layar via **Animated Global Toast UI** layaknya OS modern.


---

## 🛠 Panduan Instalasi (Development)

Proses deployment server di mode rekayasa lokal:

### Minimum Requirements
*   PHP `^8.2`
*   Composer `v2`
*   Node.js (NPM)
*   MySQL `^5.7` / `8.x`

### Setup Langkah demi Langkah

**1. Kloning Repositori**
```bash
git clone https://github.com/AffanRifai/Toko-Roni-Mobile-App.git
cd tokoroni-app
```

**2. Instalasi Paket Ekosistem**
```bash
composer install
npm install
npm run build
```

**3. Inisialisasi Environment**
```bash
cp .env.example .env
php artisan key:generate
```
*(Ingatlah untuk mensinergikan koneksi PDO `DB_DATABASE`, dll pada file `.env`)*

**4. Migrasi & Injeksi Data Rekayasa (Seeding)**
Kami telah mendesain spesifikasi data riil pasar Indonesia (daftar sembako asli, profil kurir lokal fiktif, varian armada) demi kelancaran testing UI.
```bash
php artisan migrate:fresh --seed
```

**5. AI Face Models**
Untuk fungsi login wajah WebRTC, pastikan Anda menarik bobot *neural network weights* standar FaceAPI.js:
*(simpan di dalam `/public/models/`)*

**6. Aktivasi Server**
```bash
php artisan serve
```
Jelajahi keagungan sistem di: `http://localhost:8000`

---

## 🔐 Kredensial Sampel Cepat (Data Dummy)
Karena *database* telah disuntik data, Anda bisa menggunakan pintasan otentikasi berikut (*Password semua akun adalah:* `password`):

| Peran | Kredensial Email | Fokus Pengujian UI |
|-------|-----------------|--------------------|
| **Owner** | `owner@tokoroni.com` | Cek Grafik Omset Bulanan & Notifikasi Global |
| **Kasir** | `kasir@tokoroni.com` | Pembuatan Transaksi POS Real-time |
| **Logistik** | `logistik@tokoroni.com` | Penugasan Pengiriman ke Armada Kurir |
| **Kurir** | `kurir_budi@tokoroni.com` | Cek Daftar Penugasan di *My Deliveries* |

---

## 👨‍💻 Tim Pengembang Inti

Dalam dedikasinya meracik kapabilitas Toko Roni, berikut orkestrator repositori terkait:

- 🎨 **Affan Rifa'i** — *UI/UX Designer & Mobile App Engineer*
- ⚙️ **TIO R** — *Lead Back-end Engineer*
- 🌐 **FAIZ J A** — *Full-stack Developer*
- 🖌️ **Fadhlan M R** — *Front-End Engineer*

<div align="center">
  <br>
  <i><b>Toko Roni Framework</b> — Dikembangkan dan direncanakan penuh semangat untuk industri modern.</i>
</div>
