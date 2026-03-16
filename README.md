# Toko Roni - Face Recognition Login System

## 📋 Deskripsi Aplikasi
Toko Roni adalah sistem manajemen toko yang dilengkapi dengan fitur **Face Recognition Login** (login dengan pengenalan wajah) untuk meningkatkan keamanan dan kenyamanan akses. Aplikasi ini dibangun dengan Laravel dan menggunakan FaceAPI.js untuk pengenalan wajah real-time.

## 🚀 Fitur Utama

### 1. **Sistem Autentikasi**
- Login dengan password (konvensional)
- Login dengan Face Recognition (pengenalan wajah)
- Multi-level user roles (Owner, Admin, Kasir, Gudang, Logistik, Kurir)
- Registrasi wajah untuk user (khusus admin/owner)

### 2. **Dashboard Berbasis Role**
- **Owner**: Laporan penjualan, manajemen user, laporan keuangan
- **Admin**: Manajemen produk, kategori, user
- **Kasir**: Transaksi penjualan, cetak struk
- **Kepala Gudang**: Manajemen stok, produk
- **Logistik**: Manajemen pengiriman, kendaraan
- **Kurir**: Tracking pengiriman, update status

### 3. **Manajemen Data**
- Produk & Kategori
- Transaksi
- Member & Piutang
- Kendaraan
- Pengiriman
- Laporan (PDF/Excel)

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 11** - PHP Framework
- **MySQL** - Database
- **FaceAPI.js** - Face recognition library
- **SweetAlert2** - Notifikasi interaktif

### Frontend
- **TailwindCSS** - Styling
- **Font Awesome** - Icons
- **Chart.js** - Grafik dashboard
- **jQuery** - DOM manipulation

### Infrastructure
- **Cloudflare** - CDN & Security
- **Postman** - API Testing

## 📁 Struktur Direktori

```
tokoroni/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticatedSessionController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── TransactionController.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php
│   │       └── VerifyCsrfToken.php
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Transaction.php
│       └── ...
├── bootstrap/
│   └── app.php
├── config/
├── database/
│   └── migrations/
├── public/
│   └── models/ (face-api models)
├── resources/
│   └── views/
│       └── auth/
│           └── login.blade.php
├── routes/
│   └── web.php
└── .env
```

## 🔧 Instalasi

### Prerequisites
- PHP 8.2 atau lebih tinggi
- Composer
- MySQL 5.7 atau lebih tinggi
- Node.js & NPM (opsional)
- Web browser dengan dukungan WebRTC (Chrome, Firefox, Edge)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/tokoroni.git
cd tokoroni
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Copy Environment File**
```bash
cp .env.example .env
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tokoroni
DB_USERNAME=root
DB_PASSWORD=

# Untuk Cloudflare (production)
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
TRUSTED_PROXIES=**
```

6. **Migrate Database**
```bash
php artisan migrate --seed
```

7. **Download FaceAPI Models**
```bash
# Buat direktori models di public
mkdir -p public/models

# Download model dari repository face-api.js
# Atau jalankan script download:
php artisan face:download-models
```

8. **Jalankan Aplikasi**
```bash
php artisan serve
```

9. **Akses Aplikasi**
Buka browser: `http://localhost:8000`

## 🎯 Konfigurasi Face Recognition

### 1. **Setup di Local Development**
```javascript
// Di resources/views/auth/login.blade.php
const MODEL_PATH = '/models'; // Model sudah didownload
```

### 2. **Setup di Production (Cloudflare)**
```php
// Di .env
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=**
```

```php
// Di app/Http/Middleware/TrustProxies.php
protected $proxies = '*'; // Trust Cloudflare
```

### 3. **Konfigurasi Route**
```php
// routes/web.php
Route::post('/face-login', [AuthenticatedSessionController::class, 'faceLogin'])
    ->name('face.login.direct')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
```

## 👥 User Roles & Credentials

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Owner | owner@tokoroni.com | password123 | Full akses |
| Admin | admin@tokoroni.com | password123 | Manajemen user, produk |
| Kasir | kasir@tokoroni.com | password123 | Transaksi |
| Kepala Gudang | gudang@tokoroni.com | password123 | Stok produk |
| Logistik | logistik@tokoroni.com | password123 | Pengiriman |
| Kurir | kurir@tokoroni.com | password123 | Update pengiriman |

## 📡 API Endpoints

### Public Endpoints (No Auth Required)
```
POST   /face-login           - Login dengan face recognition
POST   /face-compare         - Membandingkan face descriptor
GET    /registered-faces     - Mendapatkan semua wajah terdaftar
GET    /csrf-token           - Refresh CSRF token
```

### Protected Endpoints (Auth Required)
```
POST   /face-register        - Registrasi wajah baru (admin/owner)
GET    /face-status          - Cek status face recognition
GET    /face-users           - Daftar user untuk registrasi
GET    /users                - Manajemen user (owner)
POST   /users                - Create user (owner)
PUT    /users/{id}           - Update user (owner)
DELETE /users/{id}           - Delete user (owner)
```

## 🔐 Keamanan

### CSRF Protection
- Semua form menggunakan CSRF token
- Route face-login dikecualikan dari CSRF untuk kompatibilitas
- Token refresh otomatis setiap 30 menit

### Face Recognition Security
- Descriptor wajah disimpan dalam bentuk encrypted JSON
- Threshold distance: 0.6 untuk validasi
- Minimal face score: 0.5 untuk registrasi

### Session Security
- Session lifetime: 120 menit
- Regenerate session setelah login
- Secure cookie di production

## 🐛 Troubleshooting

### 1. **Error 419 (Page Expired)**
**Penyebab**: CSRF token mismatch
**Solusi**:
- Pastikan route face-login di-exclude dari CSRF
- Jalankan `php artisan config:clear`
- Cek session configuration di `.env`

### 2. **Face Recognition Tidak Jalan**
**Penyebab**: Model face-api tidak terdownload
**Solusi**:
```bash
# Download model manually
cd public
mkdir -p models
cd models
wget https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/tiny_face_detector_model-weights_manifest.json
wget https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/face_landmark_68_model-weights_manifest.json
# ... download semua model
```

### 3. **Kamera Tidak Terdeteksi**
**Penyebab**: Browser tidak memiliki izin kamera
**Solusi**: 
- Izinkan akses kamera di browser
- Cek di pengaturan browser > Privacy & Security > Camera

### 4. **Error di Cloudflare**
**Penyebab**: Cloudflare caching
**Solusi**:
- Tambahkan Page Rule untuk bypass cache
- Set SSL/TLS ke "Full (strict)"
- Konfigurasi Trusted Proxies

## 📊 Testing dengan Postman

Import file `Toko Roni API.postman_collection.json` ke Postman.

### Environment Variables
```
base_url: https://yourdomain.com (atau http://localhost:8000)
csrf_token: (didapat dari /csrf-token)
auth_token: (didapat setelah login)
user_id: 1
```

## 🚀 Deployment ke Production

### 1. **Server Requirements**
- PHP 8.2+
- MySQL 5.7+
- Composer
- SSL Certificate

### 2. **Langkah Deployment**
```bash
# Set environment
cp .env.example .env
php artisan key:generate

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate database
php artisan migrate --force

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 3. **Konfigurasi Nginx**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    root /var/www/tokoroni/public;
    index index.php;

    ssl_certificate /etc/nginx/ssl/yourdomain.com.crt;
    ssl_certificate_key /etc/nginx/ssl/yourdomain.com.key;

    # Cloudflare
    set_real_ip_from 103.21.244.0/22;
    set_real_ip_from 103.22.200.0/22;
    # ... tambah semua IP Cloudflare
    real_ip_header CF-Connecting-IP;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 📝 Lisensi
Hak Cipta © 2024 Toko Roni. All rights reserved.

## 👨‍💻 Kontributor
- **Back-end Dev** - [Faiz J]
- **Full-stack Dev** - [Tio R]
- **Front-End Dev** - [Fadhlan M R]
- **UI/UX Designer & Mobile Dev** - [Affan Rifaiz]

## 📞 Kontak & Dukungan
- Email: faizalba74@gmail.com
- GitHub: https://github.com/AffanRifai/tokoroni

---

**Catatan**: Aplikasi ini masih dalam pengembangan. Untuk laporan bug atau saran fitur, silakan buat issue di repository GitHub.
