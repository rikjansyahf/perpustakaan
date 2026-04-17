<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Dashboard';

// Ambil statistik dari DB
$totalBuku     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM buku"))[0];
$totalAnggota  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM user WHERE level='peminjam'"))[0];
$totalDipinjam = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'"))[0];
$totalTerlambat= mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='terlambat'"))[0];

// Chart: peminjaman per bulan (tahun ini)
$chartData = array_fill(0, 12, 0);
$res = mysqli_query($koneksi, "SELECT MONTH(tanggal_pinjam) as bln, COUNT(*) as total FROM peminjaman WHERE YEAR(tanggal_pinjam)=YEAR(NOW()) GROUP BY bln");
while ($row = mysqli_fetch_assoc($res)) $chartData[$row['bln']-1] = (int)$row['total'];

// Peminjaman terbaru
$recentQuery = mysqli_query($koneksi, "
    SELECT p.*, u.nama as nama_user, b.judul as judul_buku
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id
    JOIN buku b ON p.id_buku = b.id
    ORDER BY p.created_at DESC LIMIT 5
");

include 'includes/header.php';
?>

<!-- Heading -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-0.5">Selamat datang, <?= htmlspecialchars($_SESSION['user']['nama']) ?></p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-blue-500">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-book text-blue-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Buku</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalBuku ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-green-500">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-book-open text-green-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Sedang Dipinjam</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalDipinjam ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-yellow-500">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-users text-yellow-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Anggota</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalAnggota ?></p>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 border-l-4 border-red-500">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-600 text-lg"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Terlambat</p>
            <p class="text-2xl font-bold text-gray-800"><?= $totalTerlambat ?></p>
        </div>
    </div>
</div>

<!-- Chart + Recent -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6 text-center">
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Peminjaman Bulanan <?= date('Y') ?></h3>
        <canvas id="areaChart" height="100"></canvas>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Status Peminjaman</h3>
        <div class="relative w-full" style="height:200px">
            <canvas id="pieChart"></canvas>
        </div>
        <div class="flex flex-wrap gap-3 mt-4 justify-center text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Dikembalikan</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>Dipinjam</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Terlambat</span>
        </div>
    </div>
</div>

<!-- Recent Peminjaman -->
<div class="bg-white rounded-2xl shadow-sm p-5">
    <h3 class="font-semibold text-gray-800 mb-4">Peminjaman Terbaru</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 uppercase border-b">
                    <th class="pb-3 pr-4">Peminjam</th>
                    <th class="pb-3 pr-4">Buku</th>
                    <th class="pb-3 pr-4">Tgl Pinjam</th>
                    <th class="pb-3 pr-4">Tgl Kembali</th>
                    <th class="pb-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php while ($row = mysqli_fetch_assoc($recentQuery)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pr-4 font-medium text-gray-700"><?= htmlspecialchars($row['nama_user']) ?></td>
                    <td class="py-3 pr-4 text-gray-600"><?= htmlspecialchars($row['judul_buku']) ?></td>
                    <td class="py-3 pr-4 text-gray-500"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                    <td class="py-3 pr-4 text-gray-500"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
                    <td class="py-3">
                        <?php
                        $badge = match($row['status']) {
                            'dikembalikan' => 'bg-green-100 text-green-700',
                            'terlambat'    => 'bg-red-100 text-red-700',
                            default        => 'bg-blue-100 text-blue-700',
                        };
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('areaChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: 'Peminjaman',
            data: <?= json_encode(array_values($chartData)) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true, tension: 0.4,
            pointBackgroundColor: '#3b82f6', pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } }
    }
});

<?php
$dikembalikan = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='dikembalikan'"))[0];
$dipinjam     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'"))[0];
$terlambat    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='terlambat'"))[0];
?>
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Dikembalikan','Dipinjam','Terlambat'],
        datasets: [{ data: [<?= $dikembalikan ?>,<?= $dipinjam ?>,<?= $terlambat ?>], backgroundColor: ['#22c55e','#3b82f6','#ef4444'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '65%' }
});
</script>

<?php include 'includes/footer.php'; ?>
