<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Buku';
$isStaff   = isAdminOrPetugas();
$msg = '';
$id_user   = (int)$_SESSION['user']['id'];

// Peminjam: ajukan request pinjam
if (isPeminjam() && isset($_POST['request_pinjam'])) {
    $id_buku       = (int)$_POST['id_buku'];
    $tgl_kembali   = $_POST['tanggal_kembali'];
    $catatan       = mysqli_real_escape_string($koneksi, trim($_POST['catatan'] ?? ''));
    $tgl_request   = date('Y-m-d');

    // Cek stok
    $stok = mysqli_fetch_row(mysqli_query($koneksi, "SELECT stok FROM buku WHERE id=$id_buku"))[0];
    if ($stok < 1) {
        $msg = 'error|Stok buku habis, tidak bisa mengajukan peminjaman.';
    } else {
        // Cek apakah sudah ada request aktif untuk buku ini
        $cekReq = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE id_user=$id_user AND id_buku=$id_buku AND status='menunggu'"))[0];
        // Cek apakah sedang meminjam buku ini
        $cekPinjam = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE id_user=$id_user AND id_buku=$id_buku AND status='dipinjam'"))[0];
        if ($cekReq > 0) {
            $msg = 'error|Anda sudah memiliki request aktif untuk buku ini.';
        } elseif ($cekPinjam > 0) {
            $msg = 'error|Anda sedang meminjam buku ini.';
        } else {
            mysqli_query($koneksi, "INSERT INTO request_pinjam (id_user,id_buku,tanggal_request,tanggal_kembali,catatan_user)
                VALUES ($id_user,$id_buku,'$tgl_request','$tgl_kembali','$catatan')");
            $msg = 'success|Permintaan peminjaman berhasil diajukan. Tunggu persetujuan admin.';
        }
    }
}

// Ambil daftar buku yang sedang dipinjam user (untuk cek status tombol)
$bukuDipinjam = [];
$resDipinjam = mysqli_query($koneksi, "SELECT id_buku FROM peminjaman WHERE id_user=$id_user AND status='dipinjam'");
while ($r = mysqli_fetch_row($resDipinjam)) $bukuDipinjam[] = $r[0];

// Ambil daftar request menunggu user
$bukuRequested = [];
$resReq = mysqli_query($koneksi, "SELECT id_buku FROM request_pinjam WHERE id_user=$id_user AND status='menunggu'");
while ($r = mysqli_fetch_row($resReq)) $bukuRequested[] = $r[0];

if ($isStaff) {
    // Tambah
    if (isset($_POST['tambah'])) {
        $judul    = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
        $pengarang= mysqli_real_escape_string($koneksi, trim($_POST['pengarang']));
        $penerbit = mysqli_real_escape_string($koneksi, trim($_POST['penerbit']));
        $tahun    = (int)$_POST['tahun_terbit'];
        $isbn     = mysqli_real_escape_string($koneksi, trim($_POST['isbn']));
        $id_kat   = (int)$_POST['id_kategori'];
        $stok     = (int)$_POST['stok'];
        $desk     = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
        mysqli_query($koneksi, "INSERT INTO buku (judul,pengarang,penerbit,tahun_terbit,isbn,id_kategori,stok,deskripsi)
            VALUES ('$judul','$pengarang','$penerbit',$tahun,'$isbn',$id_kat,$stok,'$desk')");
        $msg = 'success|Buku berhasil ditambahkan.';
    }
    // Edit
    if (isset($_POST['edit'])) {
        $id       = (int)$_POST['id'];
        $judul    = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
        $pengarang= mysqli_real_escape_string($koneksi, trim($_POST['pengarang']));
        $penerbit = mysqli_real_escape_string($koneksi, trim($_POST['penerbit']));
        $tahun    = (int)$_POST['tahun_terbit'];
        $isbn     = mysqli_real_escape_string($koneksi, trim($_POST['isbn']));
        $id_kat   = (int)$_POST['id_kategori'];
        $stok     = (int)$_POST['stok'];
        $desk     = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
        mysqli_query($koneksi, "UPDATE buku SET judul='$judul',pengarang='$pengarang',penerbit='$penerbit',
            tahun_terbit=$tahun,isbn='$isbn',id_kategori=$id_kat,stok=$stok,deskripsi='$desk' WHERE id=$id");
        $msg = 'success|Buku berhasil diperbarui.';
    }
    // Hapus
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        mysqli_query($koneksi, "DELETE FROM buku WHERE id=$id");
        $msg = 'success|Buku berhasil dihapus.';
    }
}

$search  = mysqli_real_escape_string($koneksi, trim($_GET['q'] ?? ''));
$katFilter = (int)($_GET['kat'] ?? 0);
$where   = "WHERE 1=1";
if ($search)    $where .= " AND (b.judul LIKE '%$search%' OR b.pengarang LIKE '%$search%' OR b.isbn LIKE '%$search%')";
if ($katFilter) $where .= " AND b.id_kategori=$katFilter";

$data      = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori=k.id $where ORDER BY b.id DESC");
$kategoriList = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800"><?= $isStaff ? 'Kelola Buku' : 'Katalog Buku' ?></h1>
        <p class="text-gray-500 text-sm mt-0.5">Koleksi buku perpustakaan SMKPAS2</p>
    </div>
    <?php if ($isStaff): ?>
    <button onclick="openModal('modalTambah')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Tambah Buku
    </button>
    <?php endif; ?>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-check-circle mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Filter -->
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul, pengarang, ISBN..."
            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
    </div>
    <select name="kat" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Kategori</option>
        <?php
        $katOpt = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");
        while ($k = mysqli_fetch_assoc($katOpt)):
        ?>
        <option value="<?= $k['id'] ?>" <?= $katFilter == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
        <?php endwhile; ?>
    </select>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Filter</button>
    <?php if ($search || $katFilter): ?><a href="tables.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Judul</th>
                    <th class="px-6 py-4">Pengarang</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4">Tahun</th>
                    <th class="px-6 py-4">Stok</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-400"><?= $no++ ?></td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($row['judul']) ?></p>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($row['isbn'] ?: '-') ?></p>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($row['pengarang']) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs"><?= htmlspecialchars($row['nama_kategori'] ?: '-') ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-500"><?= $row['tahun_terbit'] ?: '-' ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $row['stok'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $row['stok'] ?> tersedia
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($isStaff): ?>
                        <div class="flex gap-2">
                            <button onclick='openEditModal(<?= json_encode($row) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-edit text-sm"></i></button>
                            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus buku ini?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash text-sm"></i></a>
                        </div>
                        <?php elseif (isPeminjam()): ?>
                            <?php if (in_array($row['id'], $bukuDipinjam)): ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium">Sedang dipinjam</span>
                            <?php elseif (in_array($row['id'], $bukuRequested)): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-medium"><i class="fas fa-clock mr-1"></i>Menunggu</span>
                            <?php elseif ($row['stok'] > 0): ?>
                                <button onclick='openPinjamModal(<?= json_encode(['id' => $row['id'], 'judul' => $row['judul'], 'pengarang' => $row['pengarang']]) ?>)'
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition">
                                    <i class="fas fa-book-open mr-1"></i>Pinjam
                                </button>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-500 rounded-lg text-xs font-medium">Stok habis</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($isStaff): ?>
<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tambah Buku</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-3">
            <?php include 'includes/form_buku.php'; ?>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="tambah" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Simpan</button>
                <button type="button" onclick="closeModal('modalTambah')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Edit Buku</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEdit" class="space-y-3">
            <input type="hidden" name="id" id="editId">
            <?php include 'includes/form_buku.php'; ?>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Update</button>
                <button type="button" onclick="closeModal('modalEdit')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    const f = document.getElementById('formEdit');
    document.getElementById('editId').value = data.id;
    f.querySelector('[name=judul]').value       = data.judul;
    f.querySelector('[name=pengarang]').value   = data.pengarang;
    f.querySelector('[name=penerbit]').value    = data.penerbit || '';
    f.querySelector('[name=tahun_terbit]').value= data.tahun_terbit || '';
    f.querySelector('[name=isbn]').value        = data.isbn || '';
    f.querySelector('[name=id_kategori]').value = data.id_kategori || '';
    f.querySelector('[name=stok]').value        = data.stok;
    f.querySelector('[name=deskripsi]').value   = data.deskripsi || '';
    openModal('modalEdit');
}
</script>
<?php endif; ?>

<?php if (isPeminjam()): ?>
<!-- Modal Ajukan Pinjam -->
<div id="modalPinjam" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Ajukan Peminjaman</h3>
            <button onclick="closeModal('modalPinjam')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <!-- Info buku -->
        <div class="bg-blue-50 rounded-xl p-4 mb-4 flex gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-book text-blue-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-sm" id="pinjamJudul"></p>
                <p class="text-gray-500 text-xs" id="pinjamPengarang"></p>
            </div>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="id_buku" id="pinjamIdBuku">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kembali</label>
                <input type="date" name="tanggal_kembali" id="pinjamTglKembali" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Maksimal peminjaman 14 hari</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <textarea name="catatan" rows="2"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Catatan untuk admin..."></textarea>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-700">
                <i class="fas fa-info-circle mr-1"></i>
                Permintaan akan diproses oleh admin. Denda keterlambatan <strong>Rp1.000/hari</strong>.
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" name="request_pinjam" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">
                    <i class="fas fa-paper-plane mr-1"></i>Ajukan
                </button>
                <button type="button" onclick="closeModal('modalPinjam')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function openPinjamModal(data) {
    document.getElementById('pinjamIdBuku').value    = data.id;
    document.getElementById('pinjamJudul').textContent    = data.judul;
    document.getElementById('pinjamPengarang').textContent = data.pengarang;
    // Default tanggal kembali = 7 hari dari sekarang
    const d = new Date();
    d.setDate(d.getDate() + 7);
    const maxD = new Date();
    maxD.setDate(maxD.getDate() + 14);
    const fmt = dt => dt.toISOString().split('T')[0];
    const tgl = document.getElementById('pinjamTglKembali');
    tgl.value = fmt(d);
    tgl.min   = fmt(new Date(Date.now() + 86400000));
    tgl.max   = fmt(maxD);
    openModal('modalPinjam');
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>