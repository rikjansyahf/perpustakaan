<?php
include 'koneksi.php';
requireAdminOrPetugas();
$pageTitle = 'Peminjaman';
$msg = '';
$tab = $_GET['tab'] ?? 'peminjaman'; // 'peminjaman' | 'request'

// ── APPROVE request ──────────────────────────────────────
if (isset($_POST['approve'])) {
    $id_req     = (int)$_POST['id_req'];
    $catatan_pt = mysqli_real_escape_string($koneksi, trim($_POST['catatan_petugas'] ?? ''));
    $id_petugas = (int)$_SESSION['user']['id'];

    $req = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM request_pinjam WHERE id=$id_req AND status='menunggu'"));
    if ($req) {
        $stok = mysqli_fetch_row(mysqli_query($koneksi, "SELECT stok FROM buku WHERE id={$req['id_buku']}"))[0];
        if ($stok < 1) {
            $msg = 'error|Stok buku habis, tidak bisa disetujui.';
        } else {
            // Buat record peminjaman
            mysqli_query($koneksi, "INSERT INTO peminjaman (id_user,id_buku,tanggal_pinjam,tanggal_kembali,status,id_petugas)
                VALUES ({$req['id_user']},{$req['id_buku']},CURDATE(),'{$req['tanggal_kembali']}','dipinjam',$id_petugas)");
            // Kurangi stok
            mysqli_query($koneksi, "UPDATE buku SET stok=stok-1 WHERE id={$req['id_buku']}");
            // Update status request
            mysqli_query($koneksi, "UPDATE request_pinjam SET status='disetujui', id_petugas=$id_petugas, catatan_petugas='$catatan_pt' WHERE id=$id_req");
            $msg = 'success|Permintaan disetujui dan peminjaman dicatat.';
        }
    }
    $tab = 'request';
}

// ── TOLAK request ─────────────────────────────────────────
if (isset($_POST['tolak'])) {
    $id_req     = (int)$_POST['id_req'];
    $catatan_pt = mysqli_real_escape_string($koneksi, trim($_POST['catatan_petugas'] ?? ''));
    $id_petugas = (int)$_SESSION['user']['id'];
    mysqli_query($koneksi, "UPDATE request_pinjam SET status='ditolak', id_petugas=$id_petugas, catatan_petugas='$catatan_pt' WHERE id=$id_req AND status='menunggu'");
    $msg = 'success|Permintaan ditolak.';
    $tab = 'request';
}

// ── TAMBAH peminjaman manual ──────────────────────────────
if (isset($_POST['tambah'])) {
    $id_user     = (int)$_POST['id_user'];
    $id_buku     = (int)$_POST['id_buku'];
    $tgl_pinjam  = $_POST['tanggal_pinjam'];
    $tgl_kembali = $_POST['tanggal_kembali'];
    $id_petugas  = (int)$_SESSION['user']['id'];

    $stok = mysqli_fetch_row(mysqli_query($koneksi, "SELECT stok FROM buku WHERE id=$id_buku"))[0];
    if ($stok < 1) {
        $msg = 'error|Stok buku habis!';
    } else {
        mysqli_query($koneksi, "INSERT INTO peminjaman (id_user,id_buku,tanggal_pinjam,tanggal_kembali,status,id_petugas)
            VALUES ($id_user,$id_buku,'$tgl_pinjam','$tgl_kembali','dipinjam',$id_petugas)");
        mysqli_query($koneksi, "UPDATE buku SET stok=stok-1 WHERE id=$id_buku");
        $msg = 'success|Peminjaman berhasil dicatat.';
    }
}

// ── KEMBALIKAN buku ───────────────────────────────────────
if (isset($_POST['kembalikan'])) {
    $id = (int)$_POST['id'];
    $tgl_aktual = date('Y-m-d');
    $pinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id=$id"));
    $status = 'dikembalikan';
    $denda  = 0;
    if ($tgl_aktual > $pinjam['tanggal_kembali']) {
        $selisih = (strtotime($tgl_aktual) - strtotime($pinjam['tanggal_kembali'])) / 86400;
        $denda   = $selisih * (int)getSetting('denda_per_hari', '1000');
        $status  = 'terlambat';
    }
    mysqli_query($koneksi, "UPDATE peminjaman SET status='$status', tanggal_dikembalikan='$tgl_aktual', denda=$denda WHERE id=$id");
    mysqli_query($koneksi, "UPDATE buku SET stok=stok+1 WHERE id={$pinjam['id_buku']}");
    $msg = 'success|Buku berhasil dikembalikan.' . ($denda > 0 ? ' Denda: Rp' . number_format($denda, 0, ',', '.') : '');
}

// ── HAPUS peminjaman ──────────────────────────────────────
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id=$id");
    $msg = 'success|Data peminjaman dihapus.';
}

// ── Query data ────────────────────────────────────────────
$search       = mysqli_real_escape_string($koneksi, trim($_GET['q'] ?? ''));
$statusFilter = mysqli_real_escape_string($koneksi, $_GET['status'] ?? '');
$where = "WHERE 1=1";
if ($search)       $where .= " AND (u.nama LIKE '%$search%' OR b.judul LIKE '%$search%')";
if ($statusFilter) $where .= " AND p.status='$statusFilter'";

$dataPinjam = mysqli_query($koneksi, "
    SELECT p.*, u.nama as nama_user, b.judul as judul_buku, pt.nama as nama_petugas
    FROM peminjaman p
    JOIN user u ON p.id_user=u.id
    JOIN buku b ON p.id_buku=b.id
    LEFT JOIN user pt ON p.id_petugas=pt.id
    $where ORDER BY p.id DESC
");

$dataRequest = mysqli_query($koneksi, "
    SELECT rp.*, u.nama as nama_user, b.judul as judul_buku, b.stok, pt.nama as nama_petugas
    FROM request_pinjam rp
    JOIN user u ON rp.id_user=u.id
    JOIN buku b ON rp.id_buku=b.id
    LEFT JOIN user pt ON rp.id_petugas=pt.id
    ORDER BY rp.status='menunggu' DESC, rp.id DESC
");

$totalMenunggu = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE status='menunggu'"))[0];

$userList = mysqli_query($koneksi, "SELECT id, nama, username FROM user WHERE level='peminjam' ORDER BY nama");
$bukuList = mysqli_query($koneksi, "SELECT id, judul, stok FROM buku WHERE stok > 0 ORDER BY judul");

include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Peminjaman Buku</h1>
        <p class="text-gray-500 text-sm mt-0.5">Kelola transaksi peminjaman</p>
    </div>
    <button onclick="openModal('modalTambah')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Catat Manual
    </button>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg, 2); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="flex gap-1 mb-5 bg-gray-100 p-1 rounded-xl w-fit">
    <a href="?tab=peminjaman" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $tab === 'peminjaman' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">
        <i class="fas fa-book-open mr-1.5"></i>Peminjaman
    </a>
    <a href="?tab=request" class="px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5 <?= $tab === 'request' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' ?>">
        <i class="fas fa-inbox"></i>Permintaan
        <?php if ($totalMenunggu > 0): ?>
        <span class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $totalMenunggu ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if ($tab === 'peminjaman'): ?>
<!-- ── TAB PEMINJAMAN ── -->

<!-- Filter -->
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="hidden" name="tab" value="peminjaman">
    <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama / judul buku..."
            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
    </div>
    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Status</option>
        <option value="dipinjam"     <?= $statusFilter === 'dipinjam'     ? 'selected' : '' ?>>Dipinjam</option>
        <option value="dikembalikan" <?= $statusFilter === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
        <option value="terlambat"    <?= $statusFilter === 'terlambat'    ? 'selected' : '' ?>>Terlambat</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Filter</button>
    <?php if ($search || $statusFilter): ?><a href="?tab=peminjaman" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-4">#</th>
                    <th class="px-4 py-4">Peminjam</th>
                    <th class="px-4 py-4">Buku</th>
                    <th class="px-4 py-4">Tgl Pinjam</th>
                    <th class="px-4 py-4">Tgl Kembali</th>
                    <th class="px-4 py-4">Status</th>
                    <th class="px-4 py-4">Denda</th>
                    <th class="px-4 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($dataPinjam)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400"><?= $no++ ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($row['nama_user']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($row['judul_buku']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                    <td class="px-4 py-3 text-gray-500">
                        <?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?>
                        <?php if ($row['status'] === 'dipinjam' && date('Y-m-d') > $row['tanggal_kembali']): ?>
                        <span class="block text-xs text-red-500 font-medium">Terlambat!</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php $badge = match($row['status']) {
                            'dikembalikan' => 'bg-green-100 text-green-700',
                            'terlambat'    => 'bg-red-100 text-red-700',
                            default        => 'bg-blue-100 text-blue-700',
                        }; ?>
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>"><?= ucfirst($row['status']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <?= $row['denda'] > 0 ? 'Rp' . number_format($row['denda'], 0, ',', '.') : '-' ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <?php if ($row['status'] === 'dipinjam'): ?>
                            <form method="POST" onsubmit="return confirm('Konfirmasi pengembalian buku ini?')">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="kembalikan"
                                    class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg text-xs font-medium transition">
                                    <i class="fas fa-undo mr-1"></i>Kembalikan
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="?hapus=<?= $row['id'] ?>&tab=peminjaman" onclick="return confirm('Hapus data ini?')"
                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash text-sm"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- ── TAB REQUEST ── -->

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-4">#</th>
                    <th class="px-4 py-4">Peminjam</th>
                    <th class="px-4 py-4">Buku</th>
                    <th class="px-4 py-4">Tgl Request</th>
                    <th class="px-4 py-4">Rencana Kembali</th>
                    <th class="px-4 py-4">Stok</th>
                    <th class="px-4 py-4">Status</th>
                    <th class="px-4 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($dataRequest)): ?>
                <?php $badge = match($row['status']) {
                    'disetujui' => 'bg-green-100 text-green-700',
                    'ditolak'   => 'bg-red-100 text-red-700',
                    default     => 'bg-yellow-100 text-yellow-700',
                }; ?>
                <tr class="hover:bg-gray-50 <?= $row['status'] === 'menunggu' ? 'bg-yellow-50' : '' ?>">
                    <td class="px-4 py-3 text-gray-400"><?= $no++ ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($row['nama_user']) ?></td>
                    <td class="px-4 py-3">
                        <p class="text-gray-700 font-medium"><?= htmlspecialchars($row['judul_buku']) ?></p>
                        <?php if ($row['catatan_user']): ?>
                        <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-comment mr-1"></i><?= htmlspecialchars($row['catatan_user']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($row['tanggal_request'])) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $row['stok'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $row['stok'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badge ?>"><?= ucfirst($row['status']) ?></span>
                        <?php if ($row['nama_petugas']): ?>
                        <p class="text-xs text-gray-400 mt-0.5">oleh <?= htmlspecialchars($row['nama_petugas']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($row['status'] === 'menunggu'): ?>
                        <div class="flex gap-1">
                            <button onclick="openApproveModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_user'])) ?>', '<?= htmlspecialchars(addslashes($row['judul_buku'])) ?>', 'approve')"
                                class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg text-xs font-medium transition">
                                <i class="fas fa-check mr-1"></i>Setujui
                            </button>
                            <button onclick="openApproveModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_user'])) ?>', '<?= htmlspecialchars(addslashes($row['judul_buku'])) ?>', 'tolak')"
                                class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition">
                                <i class="fas fa-times mr-1"></i>Tolak
                            </button>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal Tambah Peminjaman Manual -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Catat Peminjaman Manual</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peminjam</label>
                <select name="id_user" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Peminjam --</option>
                    <?php while ($u = mysqli_fetch_assoc($userList)): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?> (<?= $u['username'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buku</label>
                <select name="id_buku" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Buku --</option>
                    <?php while ($b = mysqli_fetch_assoc($bukuList)): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['judul']) ?> (stok: <?= $b['stok'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tgl Pinjam</label>
                    <input type="date" name="tanggal_pinjam" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tgl Kembali</label>
                    <input type="date" name="tanggal_kembali" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="tambah" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Simpan</button>
                <button type="button" onclick="closeModal('modalTambah')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Approve / Tolak -->
<div id="modalApprove" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800" id="approveTitle">Konfirmasi</h3>
            <button onclick="closeModal('modalApprove')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 mb-4">
            <p class="text-sm text-gray-600">Peminjam: <span class="font-semibold text-gray-800" id="approveUser"></span></p>
            <p class="text-sm text-gray-600 mt-1">Buku: <span class="font-semibold text-gray-800" id="approveBuku"></span></p>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="id_req" id="approveIdReq">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan untuk Peminjam <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="catatan_petugas" rows="2"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Catatan..."></textarea>
            </div>
            <div class="flex gap-3 pt-1" id="approveButtons"></div>
        </form>
    </div>
</div>

<script>
function openApproveModal(id, user, buku, action) {
    document.getElementById('approveIdReq').value = id;
    document.getElementById('approveUser').textContent = user;
    document.getElementById('approveBuku').textContent = buku;

    const btns = document.getElementById('approveButtons');
    if (action === 'approve') {
        document.getElementById('approveTitle').textContent = 'Setujui Permintaan';
        btns.innerHTML = `
            <button type="submit" name="approve" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 rounded-lg text-sm transition"><i class="fas fa-check mr-1"></i>Setujui</button>
            <button type="button" onclick="closeModal('modalApprove')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
        `;
    } else {
        document.getElementById('approveTitle').textContent = 'Tolak Permintaan';
        btns.innerHTML = `
            <button type="submit" name="tolak" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 rounded-lg text-sm transition"><i class="fas fa-times mr-1"></i>Tolak</button>
            <button type="button" onclick="closeModal('modalApprove')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
        `;
    }
    openModal('modalApprove');
}
</script>

<?php include 'includes/footer.php'; ?>
