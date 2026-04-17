# Sistem Perpustakaan Digital — SMKPAS2

Aplikasi manajemen perpustakaan berbasis web yang dibangun dengan PHP native dan Tailwind CSS. Mendukung dua role pengguna dengan fitur lengkap mulai dari katalog buku, peminjaman, hingga ulasan.

---

## Deskripsi Program

Sistem Perpustakaan Digital SMKPAS2 adalah aplikasi web untuk mengelola koleksi buku, transaksi peminjaman, dan anggota perpustakaan. Admin dapat mengelola seluruh data, sementara peminjam dapat mengajukan permintaan pinjam secara mandiri melalui katalog online.

### Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8+ (native, tanpa framework) |
| Database | MySQL / MariaDB |
| Frontend | Tailwind CSS (CDN) |
| Icon | Font Awesome 6 |
| Chart | Chart.js |
| Font | Google Fonts — Inter |

---

## Struktur Direktori

```
perpustakaan/
├── index.php               # Dashboard utama
├── login.php               # Halaman login
├── register.php            # Halaman registrasi
├── logout.php              # Proses logout
├── koneksi.php             # Koneksi DB + helper fungsi
├── kategori.php            # CRUD kategori buku (admin)
├── tables.php              # Katalog & CRUD buku
├── peminjaman.php          # Kelola peminjaman + approve request (admin)
├── request.php             # Status permintaan peminjaman (peminjam)
├── riwayat.php             # Riwayat peminjaman pribadi (peminjam)
├── ulasan.php              # Ulasan & rating buku
├── user.php                # Kelola akun user (admin)
├── database.sql            # Script SQL untuk setup database
└── includes/
    ├── header.php          # HTML head + buka layout
    ├── footer.php          # Tutup layout + script global
    ├── sidebar.php         # Navigasi sidebar (role-based)
    ├── topbar.php          # Header atas
    ├── form_buku.php       # Partial form input buku
    └── form_user.php       # Partial form input user
```

---

## Instalasi & Setup

### Prasyarat

- PHP >= 8.0
- MySQL >= 5.7 atau MariaDB >= 10.3
- Web server: Apache (XAMPP / Laragon) atau Nginx
- Browser modern

### Langkah Instalasi

**1. Clone / copy project ke direktori web server**

```bash
# Untuk XAMPP
cp -r perpustakaan/ C:/xampp/htdocs/perpustakaan

# Untuk Laragon
cp -r perpustakaan/ C:/laragon/www/perpustakaan
```

**2. Import database**

Buka phpMyAdmin atau jalankan via terminal:

```bash
mysql -u root -p < database.sql
```

Atau lewat phpMyAdmin:
- Buka `http://localhost/phpmyadmin`
- Klik **Import** → pilih file `database.sql` → klik **Go**

**3. Konfigurasi koneksi database**

Edit file `koneksi.php` sesuaikan dengan konfigurasi lokal:

```php
$koneksi = mysqli_connect('localhost', 'root', '', 'perpustakaan');
//                         ^host       ^user   ^pass  ^nama_db
```

**4. Jalankan aplikasi**

Buka browser dan akses:

```
http://localhost/perpustakaan/
```

---

## Akun Default

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | Admin |
| `peminjam1` | `peminjam123` | Peminjam |
| `peminjam2` | `peminjam123` | Peminjam |

> **Penting:** Ganti password default setelah pertama kali login.

---

## Role & Hak Akses

### Admin
- Dashboard dengan statistik dan grafik real-time
- CRUD kategori buku
- CRUD data buku (stok, ISBN, pengarang, dll)
- Catat peminjaman manual
- Approve / tolak permintaan peminjaman dari anggota
- Proses pengembalian buku + hitung denda otomatis
- Kelola akun user (tambah, edit, hapus, ubah role)
- Lihat & hapus semua ulasan

### Peminjam
- Dashboard pribadi
- Lihat katalog buku dengan filter kategori & pencarian
- Ajukan permintaan peminjaman langsung dari katalog
- Pantau status permintaan (menunggu / disetujui / ditolak)
- Lihat riwayat peminjaman beserta countdown jatuh tempo
- Tulis & lihat ulasan buku

---

## Fitur Utama

### Sistem Peminjaman
- Peminjam mengajukan request dari halaman katalog
- Admin menerima notifikasi badge di sidebar
- Admin bisa approve (otomatis buat record + kurangi stok) atau tolak dengan catatan
- Saat pengembalian, denda dihitung otomatis **Rp1.000/hari** keterlambatan
- Stok buku otomatis bertambah saat buku dikembalikan

### Keamanan
- Session-based authentication
- Role check di setiap halaman (`requireLogin()`, `requireRole()`, `requireAdminOrPetugas()`)
- Password di-hash menggunakan MD5
- Input di-escape dengan `mysqli_real_escape_string()`

### UI/UX
- Responsive — mendukung mobile, tablet, dan desktop
- Sidebar collapsible di mobile
- Modal untuk form tambah/edit (tanpa pindah halaman)
- Badge notifikasi request masuk di sidebar admin
- Countdown sisa hari peminjaman aktif

---

## Struktur Database

### Tabel `user`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| nama | VARCHAR(100) | Nama lengkap |
| username | VARCHAR(50) UNIQUE | Username login |
| password | VARCHAR(255) | MD5 hash |
| email | VARCHAR(100) | Email |
| no_telepon | VARCHAR(20) | Nomor telepon |
| alamat | TEXT | Alamat |
| level | ENUM | `admin` / `peminjam` |

### Tabel `kategori`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| nama_kategori | VARCHAR(100) | Nama kategori |
| deskripsi | TEXT | Deskripsi kategori |

### Tabel `buku`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| judul | VARCHAR(200) | Judul buku |
| pengarang | VARCHAR(100) | Nama pengarang |
| penerbit | VARCHAR(100) | Nama penerbit |
| tahun_terbit | YEAR | Tahun terbit |
| isbn | VARCHAR(20) UNIQUE | Nomor ISBN |
| id_kategori | INT FK | Relasi ke `kategori` |
| stok | INT | Jumlah stok tersedia |
| deskripsi | TEXT | Sinopsis / deskripsi |

### Tabel `peminjaman`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| id_user | INT FK | Relasi ke `user` |
| id_buku | INT FK | Relasi ke `buku` |
| tanggal_pinjam | DATE | Tanggal mulai pinjam |
| tanggal_kembali | DATE | Batas tanggal kembali |
| tanggal_dikembalikan | DATE | Tanggal aktual kembali |
| status | ENUM | `dipinjam` / `dikembalikan` / `terlambat` |
| denda | DECIMAL | Total denda (Rp) |
| id_petugas | INT FK | Admin yang memproses |

### Tabel `request_pinjam`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| id_user | INT FK | Peminjam yang mengajukan |
| id_buku | INT FK | Buku yang diminta |
| tanggal_request | DATE | Tanggal pengajuan |
| tanggal_kembali | DATE | Rencana tanggal kembali |
| status | ENUM | `menunggu` / `disetujui` / `ditolak` |
| catatan_user | TEXT | Catatan dari peminjam |
| catatan_petugas | TEXT | Balasan dari admin |
| id_petugas | INT FK | Admin yang memproses |

### Tabel `ulasan`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK | Auto increment |
| id_user | INT FK | Relasi ke `user` |
| id_buku | INT FK | Relasi ke `buku` |
| rating | TINYINT | Nilai 1–5 |
| komentar | TEXT | Isi ulasan |

---

## Alur Penggunaan

### Alur Peminjam
```
Register / Login
    ↓
Katalog Buku → Klik "Pinjam" → Isi form (tanggal kembali, catatan)
    ↓
Permintaan Saya → Pantau status (Menunggu → Disetujui / Ditolak)
    ↓
Riwayat Saya → Lihat detail peminjaman aktif & countdown
    ↓
Ulasan → Tulis review setelah membaca
```

### Alur Admin
```
Login
    ↓
Dashboard → Lihat statistik & grafik
    ↓
Peminjaman → Tab "Permintaan" → Approve / Tolak request
    ↓
Peminjaman → Tab "Peminjaman" → Proses pengembalian
    ↓
Buku / Kategori → Kelola koleksi
    ↓
Kelola User → Manajemen akun anggota
```

---

## Helper Functions (`koneksi.php`)

```php
isAdmin()               // true jika user adalah admin
isPeminjam()            // true jika user adalah peminjam
requireLogin()          // redirect ke login.php jika belum login
requireRole('admin')    // redirect jika bukan role yang diminta
requireAdminOrPetugas() // alias requireRole('admin')
isAdminOrPetugas()      // alias isAdmin()
```

---

## Catatan Pengembangan

- Password menggunakan MD5 — untuk produksi disarankan upgrade ke `password_hash()` / bcrypt
- Query menggunakan `mysqli` langsung — untuk skala besar disarankan migrasi ke PDO dengan prepared statements
- Tailwind CSS dimuat via CDN — untuk produksi disarankan build lokal untuk performa optimal

---

## Lisensi

Proyek ini dibuat untuk keperluan akademik SMKPAS2. Bebas digunakan dan dimodifikasi untuk kebutuhan pendidikan.
