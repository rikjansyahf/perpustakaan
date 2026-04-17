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

// ── Settings helper ───────────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    global $koneksi;
    $key = mysqli_real_escape_string($koneksi, $key);
    $res = mysqli_fetch_row(mysqli_query($koneksi, "SELECT setting_value FROM settings WHERE setting_key='$key'"));
    return $res ? (string)$res[0] : $default;
}

// Cache semua settings sekaligus (panggil sekali per request)
function getAllSettings(): array {
    global $koneksi;
    $settings = [];
    $res = mysqli_query($koneksi, "SELECT setting_key, setting_value FROM settings");
    while ($row = mysqli_fetch_row($res)) $settings[$row[0]] = $row[1];
    return $settings;
}
?>
