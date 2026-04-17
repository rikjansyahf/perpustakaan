<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Permintaan Peminjaman';
$id_user   = (int)$_SESSION['user']['id'];
$msg = '';

// Peminjam: batalkan request
if (isPeminjam() && isset($_GET['batal'])) {
    $id = (int)$_GET['batal'];
    mysqli_query($koneksi, "DELETE FROM request_pinjam WHERE id=$id AND id_user=$id_user AND status='menunggu'");
    $msg = 'success|Permintaan berhasil dibatalkan.';
}

$statusFilter = mysqli_real_escape_string($koneksi, $_GET['status'] ?? '');
$where = "WHERE rp.id_user=$id_user";
if ($statusFilter) $where .= " AND rp.status='$statusFilter'";

$data = mysqli_query($koneksi, "
    SELECT rp.*, b.judul, b.pengarang, k.nama_kategori, pt.nama as nama_petugas
    FROM request_pinjam rp
    JOIN buku b ON rp.id_buku=b.id
    LEFT JOIN kategori k ON b.id_kategori=k.id
    LEFT JOIN user pt ON rp.id_petugas=pt.id
    $where ORDER BY rp.id DESC
");

$totalMenunggu  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE id_user=$id_user AND status='menunggu'"))[0];
$totalDisetujui = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE id_user=$id_user AND status='disetujui'"))[0];
$totalDitolak   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE id_user=$id_user AND status='ditolak'"))[0];

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Permintaan Peminjaman Saya</h1>
    <p class="text-gray-500 text-sm mt-0.5">Status pengajuan peminjaman buku</p>
</div>

<!-- Stats -->
<div class="flex flex-wrap gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-yellow-400 min-w-[160px]">
        <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Menunggu</p>
            <p class="text-lg font-bold text-gray-800"><?= $totalMenunggu ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-green-500 min-w-[160px]">
        <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-green-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Disetujui</p>
            <p class="text-lg font-bold text-gray-800"><?= $totalDisetujui ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm px-5 py-4 flex items-center gap-3 border-l-4 border-red-500 min-w-[160px]">
        <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-times text-red-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase">Ditolak</p>
            <p class="text-lg font-bold text-gray-800"><?= $totalDitolak ?></p>
        </div>
    </div>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-check-circle mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Filter -->
<form method="GET" class="mb-4 flex gap-2">
    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Status</option>
        <option value="menunggu"   <?= $statusFilter === 'menunggu'   ? 'selected' : '' ?>>Menunggu</option>
        <option value="disetujui"  <?= $statusFilter === 'disetujui'  ? 'selected' : '' ?>>Disetujui</option>
        <option value="ditolak"    <?= $statusFilter === 'ditolak'    ? 'selected' : '' ?>>Ditolak</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Filter</button>
    <?php if ($statusFilter): ?><a href="request.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Cards -->
<div class="space-y-3">
    <?php $empty = true; while ($row = mysqli_fetch_assoc($data)): $empty = false; ?>
    <?php
    $borderColor = match($row['status']) {
        'disetujui' => 'border-green-400',
        'ditolak'   => 'border-red-400',
        default     => 'border-yellow-400',
    };
    $badge = match($row['status']) {
        'disetujui' => 'bg-green-100 text-green-700',
        'ditolak'   => 'bg-red-100 text-red-700',
        default     => 'bg-yellow-100 text-yellow-700',
    };
    $icon = match($row['status']) {
        'disetujui' => 'fa-check-circle',
        'ditolak'   => 'fa-times-circle',
        default     => 'fa-clock',
    };
    ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 border-l-4 <?= $borderColor ?>">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($row['judul']) ?></h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $badge ?>">
                        <i class="fas <?= $icon ?> mr-1"></i><?= ucfirst($row['status']) ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-2">
                    <?= htmlspecialchars($row['pengarang']) ?>
                    <?php if ($row['nama_kategori']): ?>
                    · <span class="text-indigo-600"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                    <?php endif; ?>
                </p>
                <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                    <span><i class="fas fa-calendar mr-1"></i>Diajukan: <?= date('d M Y', strtotime($row['tanggal_request'])) ?></span>
                    <span><i class="fas fa-calendar-check mr-1"></i>Rencana kembali: <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></span>
                    <?php if ($row['nama_petugas']): ?>
                    <span><i class="fas fa-user-tie mr-1"></i>Diproses oleh: <?= htmlspecialchars($row['nama_petugas']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($row['catatan_user']): ?>
                <p class="text-xs text-gray-400 mt-2"><i class="fas fa-comment mr-1"></i>Catatan: <?= htmlspecialchars($row['catatan_user']) ?></p>
                <?php endif; ?>
                <?php if ($row['catatan_petugas']): ?>
                <p class="text-xs <?= $row['status'] === 'ditolak' ? 'text-red-500' : 'text-green-600' ?> mt-1">
                    <i class="fas fa-reply mr-1"></i>Balasan petugas: <?= htmlspecialchars($row['catatan_petugas']) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php if ($row['status'] === 'menunggu'): ?>
            <a href="?batal=<?= $row['id'] ?>" onclick="return confirm('Batalkan permintaan ini?')"
                class="flex-shrink-0 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition">
                <i class="fas fa-times mr-1"></i>Batalkan
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
    <?php if ($empty): ?>
    <div class="bg-white rounded-2xl shadow-sm p-10 text-center text-gray-400">
        <i class="fas fa-inbox text-4xl mb-3 block"></i>
        <p class="text-sm">Belum ada permintaan peminjaman.</p>
        <a href="tables.php" class="mt-3 inline-block text-blue-600 hover:underline text-sm">Lihat katalog buku</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
