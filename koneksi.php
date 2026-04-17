<?php
session_start();

$koneksi = mysqli_connect('localhost', 'root', '', 'perpustakaan');
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, 'utf8mb4');

// ── Helper: cek role ──────────────────────────────────────
function isAdmin() {
    return isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'admin';
}

function isPetugas() {
    return isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'petugas';
}

function isPeminjam() {
    return isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'peminjam';
}

function isAdminOrPetugas() {
    return isAdmin() || isPetugas();
}

// ── Helper: redirect jika tidak login ────────────────────
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }
}

// ── Helper: redirect jika bukan role tertentu ────────────
function requireRole(string $role) {
    requireLogin();
    if ($_SESSION['user']['level'] !== $role) {
        header('Location: index.php');
        exit();
    }
}

function requireAdminOrPetugas() {
    requireLogin();
    if (!isAdminOrPetugas()) {
        header('Location: index.php');
        exit();
    }
}
?>
