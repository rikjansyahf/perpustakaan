-- ============================================
-- DATABASE: perpustakaan
-- SMKPAS2 - Sistem Perpustakaan Digital
-- ============================================

DROP DATABASE IF EXISTS perpustakaan;
CREATE DATABASE perpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan;

-- ============================================
-- TABLE: user
-- Role: admin, petugas, peminjam
-- ============================================
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    no_telepon VARCHAR(20),
    alamat TEXT,
    level ENUM('admin', 'peminjam') NOT NULL DEFAULT 'peminjam',
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: kategori
-- ============================================
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: buku
-- ============================================
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    isbn VARCHAR(20) UNIQUE,
    id_kategori INT,
    stok INT DEFAULT 1,
    deskripsi TEXT,
    cover VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE SET NULL
);

-- ============================================
-- TABLE: peminjaman
-- ============================================
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    tanggal_dikembalikan DATE DEFAULT NULL,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
    denda DECIMAL(10,2) DEFAULT 0,
    id_petugas INT DEFAULT NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id) ON DELETE CASCADE,
    FOREIGN KEY (id_petugas) REFERENCES user(id) ON DELETE SET NULL
);

-- ============================================
-- TABLE: ulasan
-- ============================================
CREATE TABLE ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    rating TINYINT CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id) ON DELETE CASCADE
);

-- ============================================
-- DATA AWAL: User (password = md5 dari teks)
-- admin123   -> md5 = 0192023a7bbd73250516f069df18b500
-- petugas123 -> md5 = 3f5e9b3e2e4e1e6e7e8e9e0e1e2e3e4e (lihat bawah)
-- peminjam123-> md5 = ...
-- Semua password di bawah pakai md5() langsung via MySQL
-- ============================================
INSERT INTO user (nama, username, password, email, no_telepon, alamat, level) VALUES
('Administrator', 'admin', MD5('admin123'), 'admin@smkpas2.sch.id', '081200000001', 'SMKPAS2', 'admin'),
('Ahmad Fauzi', 'peminjam1', MD5('peminjam123'), 'ahmad@gmail.com', '081200000004', 'Jl. Pahlawan No.10', 'peminjam'),
('Dewi Lestari', 'peminjam2', MD5('peminjam123'), 'dewi@gmail.com', '081200000005', 'Jl. Kenanga No.3', 'peminjam');

-- ============================================
-- DATA AWAL: Kategori
-- ============================================
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Fiksi', 'Novel, cerpen, dan karya fiksi lainnya'),
('Sains & Teknologi', 'Buku ilmu pengetahuan dan teknologi'),
('Sejarah', 'Buku sejarah nasional dan dunia'),
('Pendidikan', 'Buku pelajaran dan referensi akademik'),
('Agama', 'Buku keagamaan dan spiritual'),
('Biografi', 'Kisah hidup tokoh-tokoh inspiratif');

-- ============================================
-- DATA AWAL: Buku
-- ============================================
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, id_kategori, stok, deskripsi) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, '978-979-1227-00-1', 1, 5, 'Novel tentang semangat anak-anak Belitung'),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, '978-979-407-313-7', 1, 3, 'Tetralogi Buru bagian pertama'),
('Fisika Dasar', 'Halliday & Resnick', 'Erlangga', 2010, '978-602-241-100-1', 2, 4, 'Buku fisika untuk perguruan tinggi'),
('Sejarah Indonesia Modern', 'M.C. Ricklefs', 'Gadjah Mada University Press', 2008, '978-979-420-400-1', 3, 2, 'Sejarah Indonesia dari abad ke-15'),
('Matematika SMA Kelas X', 'Kemendikbud', 'Kemendikbud', 2020, '978-602-282-100-1', 4, 10, 'Buku teks matematika kurikulum 2013'),
('Riyadhus Shalihin', 'Imam Nawawi', 'Pustaka Amani', 2015, '978-979-538-100-1', 5, 3, 'Kumpulan hadits pilihan'),
('Biografi Soekarno', 'Cindy Adams', 'Gunung Agung', 1966, '978-979-100-100-1', 6, 2, 'Kisah hidup Presiden pertama RI');

-- ============================================
-- TABLE: request_pinjam
-- Peminjam mengajukan request, petugas/admin approve
-- ============================================
CREATE TABLE request_pinjam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_request DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('menunggu', 'disetujui', 'ditolak') DEFAULT 'menunggu',
    catatan_user TEXT,
    catatan_petugas TEXT,
    id_petugas INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id) ON DELETE CASCADE,
    FOREIGN KEY (id_petugas) REFERENCES user(id) ON DELETE SET NULL
);

-- ============================================
-- DATA AWAL: Peminjaman (contoh)
-- ============================================
INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, tanggal_kembali, status, id_petugas) VALUES
(2, 1, '2026-04-01', '2026-04-14', 'dipinjam', 1),
(3, 3, '2026-04-05', '2026-04-18', 'dipinjam', 1),
(2, 5, '2026-03-10', '2026-03-24', 'dikembalikan', 1),
(3, 2, '2026-03-01', '2026-03-14', 'terlambat', 1);
