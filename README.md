# 🎬 CinemaX - Website Pemesanan Tiket Bioskop

Website pemesanan tiket bioskop lengkap menggunakan PHP Native, MySQL, Bootstrap 5.

---

## 📁 Struktur Folder

```
bioskop-ticketing/
├── config/
│   └── koneksi.php         ← Konfigurasi database & helper functions
├── admin/
│   ├── dashboard.php       ← Dashboard admin dengan statistik
│   ├── film.php            ← CRUD film
│   ├── jadwal.php          ← CRUD jadwal tayang
│   ├── user.php            ← Manajemen user
│   └── transaksi.php       ← Lihat semua transaksi
├── user/
│   ├── home.php            ← Halaman utama / daftar film
│   ├── detail_film.php     ← Detail film + pilih jadwal
│   ├── pilih_kursi.php     ← Layout kursi interaktif
│   ├── pesan_tiket.php     ← Konfirmasi & proses pemesanan
│   └── tiket_saya.php      ← Riwayat tiket user
├── auth/
│   ├── login.php           ← Halaman login
│   ├── login_proses.php    ← Proses autentikasi
│   ├── register.php        ← Halaman registrasi
│   └── logout.php          ← Logout & destroy session
├── assets/
│   ├── css/                ← Custom CSS (opsional)
│   ├── js/                 ← Custom JS (opsional)
│   └── images/             ← Upload poster film di sini
├── bioskop_db.sql          ← File SQL untuk import database
├── index.php               ← Redirect otomatis
└── README.md
```

---

## ⚙️ Cara Install

### 1. Persyaratan
- XAMPP / Laragon (PHP 7.4+ & MySQL 5.7+)
- Web browser modern

### 2. Clone / Extract Project
Letakkan folder `bioskop-ticketing` di:
- **XAMPP**: `C:/xampp/htdocs/`
- **Laragon**: `C:/laragon/www/`

### 3. Buat Database
1. Buka **phpMyAdmin** → http://localhost/phpmyadmin
2. Klik **Import**
3. Pilih file `bioskop_db.sql`
4. Klik **Go**

### 4. Konfigurasi Database
Edit file `config/koneksi.php` sesuai konfigurasi lokal:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // sesuaikan password MySQL
define('DB_NAME', 'bioskop_db');
```

Juga sesuaikan `BASE_URL` jika nama folder berbeda:
```php
define('BASE_URL', '/bioskop-ticketing/');
```

### 5. Jalankan
Buka: **http://localhost/bioskop-ticketing**

---

## 🔑 Akun Demo

| Role  | Email                 | Password |
|-------|-----------------------|----------|
| Admin | admin@bioskop.com     | password |
| User  | user@bioskop.com      | password |

---

## ✨ Fitur Utama

### 👤 User
- ✅ Login / Register
- ✅ Lihat daftar film yang sedang tayang
- ✅ Detail film dengan informasi lengkap
- ✅ Pilih jadwal berdasarkan tanggal
- ✅ Layout kursi interaktif (hijau = tersedia, merah = terisi)
- ✅ Pemesanan tiket dengan konfirmasi
- ✅ Kode booking otomatis
- ✅ Riwayat tiket saya

### 🔧 Admin
- ✅ Dashboard dengan statistik lengkap
- ✅ CRUD Film (tambah, edit, hapus, upload poster)
- ✅ CRUD Jadwal (kursi auto-generated 40 kursi/jadwal)
- ✅ Manajemen User
- ✅ Laporan Transaksi dengan search

---

## 💾 Database

### Tabel Users
| Field     | Type         | Keterangan        |
|-----------|--------------|-------------------|
| id        | INT PK AI    | Primary key       |
| nama      | VARCHAR(100) | Nama lengkap      |
| email     | VARCHAR(100) | Email unik        |
| password  | VARCHAR(255) | Password (hashed) |
| role      | ENUM         | admin / user      |

### Tabel Film
| Field    | Type         | Keterangan        |
|----------|--------------|-------------------|
| id       | INT PK AI    |                   |
| judul    | VARCHAR(200) | Judul film        |
| genre    | VARCHAR(100) | Genre             |
| durasi   | INT          | Durasi (menit)    |
| rating   | VARCHAR(10)  | Rating umur       |
| sinopsis | TEXT         | Sinopsis film     |
| poster   | VARCHAR(255) | Nama file poster  |
| status   | ENUM         | tayang / tidak    |

### Tabel Jadwal
| Field   | Type          |
|---------|---------------|
| id      | INT PK AI     |
| film_id | FK → film     |
| studio  | VARCHAR(50)   |
| tanggal | DATE          |
| jam     | TIME          |
| harga   | DECIMAL(10,2) |

### Tabel Kursi
| Field       | Type       |
|-------------|------------|
| id          | INT PK AI  |
| jadwal_id   | FK → jadwal|
| nomor_kursi | VARCHAR(10)|
| status      | ENUM       |

### Tabel Transaksi
| Field         | Type          |
|---------------|---------------|
| id            | INT PK AI     |
| user_id       | FK → users    |
| jadwal_id     | FK → jadwal   |
| kursi         | TEXT          |
| total_harga   | DECIMAL(10,2) |
| tanggal_pesan | TIMESTAMP     |
| status        | ENUM          |
| kode_booking  | VARCHAR(20)   |

---

## 🎨 Teknologi

- **PHP** — Native PHP (no framework)
- **MySQL** — Database relasional
- **Bootstrap 5.3** — UI framework
- **Font Awesome 6** — Icons
- **CSS3** — Custom dark cinema theme

---

## 📌 Catatan

- Upload poster film ke folder `assets/images/`
- Kursi layout: 5 baris (A-E) × 8 kolom = **40 kursi per jadwal**
- Password di database menggunakan `password_hash()` PHP
- Session digunakan untuk autentikasi

---

*Dibuat sebagai project tugas website pemesanan tiket bioskop*
# bioskopticketing
