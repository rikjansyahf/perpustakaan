<?php
// Load semua settings ke variabel global $cfg
// Include file ini setelah koneksi.php
$cfg = getAllSettings();

// Fallback defaults
$cfg['app_name']        = $cfg['app_name']        ?? 'Perpustakaan Digital';
$cfg['app_subtitle']    = $cfg['app_subtitle']    ?? 'SMKPAS2';
$cfg['app_logo']        = $cfg['app_logo']        ?? 'logosmkpas2.png';
$cfg['denda_per_hari']  = $cfg['denda_per_hari']  ?? '1000';
$cfg['maks_hari_pinjam']= $cfg['maks_hari_pinjam']?? '14';
$cfg['footer_text']     = $cfg['footer_text']     ?? '© 2026 Perpustakaan Digital';
?>
