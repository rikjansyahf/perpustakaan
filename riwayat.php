<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Riwayat Peminjaman';

$id_user = (int)$_SESSION['user']['id'];
$statusFilter = mysqli_real_escape_string($koneksi, $_GET['status'] ?? '');
$where = "WHERE p.id_user=$id_user";
if ($statusFilter) $where .= " AND p.status='$statusFilter'";

$data = mysqli_query($koneksi, "
    SELECT p.*, b.judul, b.pengarang, k.nama_kategori
    FROM peminjaman p
    JOIN buku b ON p.id_buku=b.id
    LEFT JOIN kategori k ON b.id_kategori=k.id
    $where ORDER BY p.id DESC
");

// Statistik
$totalPinjam    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE id_user=$id_user"))[0];
$sedangDipinjam = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE id_user=$id_user AND status='dipinjam'"))[0];
$totalDenda     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COALESCE(SUM(denda),0) FROM peminjaman WHERE id_user=$id_user"))[0];

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Riwayat Peminjaman Saya</h1>
    <p class="text-gray-500 text-sm mt-0.5">Histori peminjaman buku anda</p>
</div>

<!-- Stats -->
<div class="flex flex-wrap gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-blue-500 min-w-[180px]">
        <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-book text-blue-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Pinjam</p>
            <p class="text-lg font-bold text-gray-800"><?= $totalPinjam ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-yellow-500 min-w-[180px]">
        <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-book-open text-yellow-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Sedang Dipinjam</p>
            <p class="text-lg font-bold text-gray-800"><?= $sedangDipinjam ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-red-500 min-w-[180px]">
        <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-money-bill text-red-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Total Denda</p>
            <p class="text-lg font-bold text-gray-800">Rp<?= number_format($totalDenda, 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="mb-4 flex gap-2">
    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Status</option>
        <option value="dipinjam"     <?= $statusFilter === 'dipinjam'     ? 'selected' : '' ?>>Dipinjam</option>
        <option value="dikembalikan" <?= $statusFilter === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
        <option value="terlambat"    <?= $statusFilter === 'terlambat'    ? 'selected' : '' ?>>Terlambat</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Filter</button>
    <?php if ($statusFilter): ?><a href="riwayat.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Cards -->
<div class="space-y-3">
    <?php while ($row = mysqli_fetch_assoc($data)): ?>
    <?php
    $isLate    = $row['status'] === 'terlambat';
    $isActive  = $row['status'] === 'dipinjam';
    $isDone    = $row['status'] === 'dikembalikan';
    $borderColor = $isLate ? 'border-red-400' : ($isActive ? 'border-blue-400' : 'border-green-400');
    $badge = match($row['status']) {
        'dikembalikan' => 'bg-green-100 text-green-700',
        'terlambat'    => 'bg-red-100 text-red-700',
        default        => 'bg-blue-100 text-blue-700',
    };
    ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 <?= $borderColor ?>">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($row['judul']) ?></h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $badge ?>"><?= ucfirst($row['status']) ?></span>
                </div>
                <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($row['pengarang']) ?>
                    <?php if ($row['nama_kategori']): ?>
                    · <span class="text-indigo-600"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                    <?php endif; ?>
                </p>
                <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                    <span><i class="fas fa-calendar-plus mr-1"></i>Pinjam: <?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></span>
                    <span><i class="fas fa-calendar-check mr-1"></i>Batas: <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></span>
                    <?php if ($row['tanggal_dikembalikan']): ?>
                    <span><i class="fas fa-undo mr-1"></i>Dikembalikan: <?= date('d M Y', strtotime($row['tanggal_dikembalikan'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($row['denda'] > 0): ?>
            <div class="text-right flex-shrink-0">
                <p class="text-xs text-gray-400">Denda</p>
                <p class="font-bold text-red-600">Rp<?= number_format($row['denda'], 0, ',', '.') ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($isActive): ?>
        <?php $sisa = ceil((strtotime($row['tanggal_kembali']) - time()) / 86400); ?>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs <?= $sisa < 0 ? 'text-red-600 font-semibold' : ($sisa <= 2 ? 'text-yellow-600' : 'text-gray-500') ?>">
                <i class="fas fa-clock mr-1"></i>
                <?= $sisa < 0 ? abs($sisa) . ' hari terlambat' : ($sisa === 0 ? 'Jatuh tempo hari ini!' : "Sisa $sisa hari") ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
