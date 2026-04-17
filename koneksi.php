<?php
session_start();

$koneksi = mysqli_connect('localhost', 'root', '', 'perpustakaan');
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
mysqli_set_charset($koneksi, 'utf8mb4');

function isAdmin() {
    return isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'admin';
}

function isPeminjam() {
    return isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'peminjam';
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }
}

function requireRole(string $role) {
    requireLogin();
    if ($_SESSION['user']['level'] !== $role) {
        header('Location: index.php');
        exit();
    }
}

// Alias — dulu admin+petugas, sekarang admin saja
function requireAdminOrPetugas() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function isAdminOrPetugas() {
    return isAdmin();
}
?>
