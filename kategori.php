<?php
include 'koneksi.php';
requireAdminOrPetugas();
$pageTitle = 'Kategori';

$msg = '';

// Tambah
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $desk = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama','$desk')");
    $msg = 'success|Kategori berhasil ditambahkan.';
}

// Edit
if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $desk = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama', deskripsi='$desk' WHERE id=$id");
    $msg = 'success|Kategori berhasil diperbarui.';
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id=$id");
    $msg = 'success|Kategori berhasil dihapus.';
}

$search = mysqli_real_escape_string($koneksi, trim($_GET['q'] ?? ''));
$where  = $search ? "WHERE nama_kategori LIKE '%$search%'" : '';
$data   = mysqli_query($koneksi, "SELECT k.*, COUNT(b.id) as total_buku FROM kategori k LEFT JOIN buku b ON b.id_kategori=k.id $where GROUP BY k.id ORDER BY k.id DESC");

include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kategori Buku</h1>
        <p class="text-gray-500 text-sm mt-0.5">Kelola kategori koleksi perpustakaan</p>
    </div>
    <button onclick="openModal('modalTambah')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Tambah Kategori
    </button>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Search -->
<form method="GET" class="mb-4 flex gap-2">
    <div class="relative flex-1 max-w-xs">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari kategori..."
            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Cari</button>
    <?php if ($search): ?><a href="kategori.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Nama Kategori</th>
                    <th class="px-6 py-4">Deskripsi</th>
                    <th class="px-6 py-4">Total Buku</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-400"><?= $no++ ?></td>
                    <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($row['deskripsi'] ?: '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium"><?= $row['total_buku'] ?> buku</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button onclick='openEditModal(<?= json_encode($row) ?>)'
                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus kategori ini?')"
                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                <i class="fas fa-trash text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tambah Kategori</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="nama_kategori" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nama kategori">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Deskripsi kategori"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="tambah" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Simpan</button>
                <button type="button" onclick="closeModal('modalTambah')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Edit Kategori</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="nama_kategori" id="editNama" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="editDesk" rows="3"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="edit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Update</button>
                <button type="button" onclick="closeModal('modalEdit')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('editId').value   = data.id;
    document.getElementById('editNama').value = data.nama_kategori;
    document.getElementById('editDesk').value = data.deskripsi || '';
    openModal('modalEdit');
}
</script>

<?php include 'includes/footer.php'; ?>
