<?php
include 'koneksi.php';
requireRole('admin');
$pageTitle = 'Kelola User';
$msg = '';

// Tambah user
if (isset($_POST['tambah'])) {
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $username  = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password  = md5(trim($_POST['password']));
    $email     = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $no_telp   = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat    = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $level     = in_array($_POST['level'], ['admin','petugas','peminjam']) ? $_POST['level'] : 'peminjam';

    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM user WHERE username='$username'"))[0];
    if ($cek > 0) {
        $msg = 'error|Username sudah digunakan.';
    } else {
        mysqli_query($koneksi, "INSERT INTO user (nama,username,password,email,no_telepon,alamat,level)
            VALUES ('$nama','$username','$password','$email','$no_telp','$alamat','$level')");
        $msg = 'success|User berhasil ditambahkan.';
    }
}

// Edit user
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $nama  = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $no_telp = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat  = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $level   = in_array($_POST['level'], ['admin','petugas','peminjam']) ? $_POST['level'] : 'peminjam';

    $sql = "UPDATE user SET nama='$nama',email='$email',no_telepon='$no_telp',alamat='$alamat',level='$level'";
    if (!empty($_POST['password'])) {
        $pw = md5(trim($_POST['password']));
        $sql .= ",password='$pw'";
    }
    mysqli_query($koneksi, "$sql WHERE id=$id");
    $msg = 'success|User berhasil diperbarui.';
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id == $_SESSION['user']['id']) {
        $msg = 'error|Tidak bisa menghapus akun sendiri.';
    } else {
        mysqli_query($koneksi, "DELETE FROM user WHERE id=$id");
        $msg = 'success|User berhasil dihapus.';
    }
}

$search      = mysqli_real_escape_string($koneksi, trim($_GET['q'] ?? ''));
$levelFilter = mysqli_real_escape_string($koneksi, $_GET['level'] ?? '');
$where = "WHERE 1=1";
if ($search)      $where .= " AND (nama LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%')";
if ($levelFilter) $where .= " AND level='$levelFilter'";

$data = mysqli_query($koneksi, "SELECT * FROM user $where ORDER BY level, nama");

include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kelola User</h1>
        <p class="text-gray-500 text-sm mt-0.5">Manajemen akun pengguna sistem</p>
    </div>
    <button onclick="openModal('modalTambah')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fas fa-user-plus text-xs"></i> Tambah User
    </button>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Filter -->
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama, username, email..."
            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
    </div>
    <select name="level" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Semua Role</option>
        <option value="admin"    <?= $levelFilter === 'admin'    ? 'selected' : '' ?>>Admin</option>
        <option value="petugas"  <?= $levelFilter === 'petugas'  ? 'selected' : '' ?>>Petugas</option>
        <option value="peminjam" <?= $levelFilter === 'peminjam' ? 'selected' : '' ?>>Peminjam</option>
    </select>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Filter</button>
    <?php if ($search || $levelFilter): ?><a href="user.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">No. Telepon</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-400"><?= $no++ ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold
                                <?= match($row['level']) { 'admin' => 'bg-yellow-500', 'petugas' => 'bg-green-500', default => 'bg-blue-500' } ?>">
                                <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                            </div>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($row['nama']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($row['username']) ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($row['email'] ?: '-') ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($row['no_telepon'] ?: '-') ?></td>
                    <td class="px-6 py-4">
                        <?php $badgeClass = match($row['level']) {
                            'admin'   => 'bg-yellow-100 text-yellow-700',
                            'petugas' => 'bg-green-100 text-green-700',
                            default   => 'bg-blue-100 text-blue-700',
                        }; ?>
                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>"><?= ucfirst($row['level']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button onclick='openEditModal(<?= json_encode($row) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"><i class="fas fa-edit text-sm"></i></button>
                            <?php if ($row['id'] != $_SESSION['user']['id']): ?>
                            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus user ini?')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition"><i class="fas fa-trash text-sm"></i></a>
                            <?php endif; ?>
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tambah User</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-3">
            <?php include 'includes/form_user.php'; ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Password">
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
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Edit User</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEdit" class="space-y-3">
            <input type="hidden" name="id" id="editId">
            <?php include 'includes/form_user.php'; ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Password baru">
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
    const f = document.getElementById('formEdit');
    document.getElementById('editId').value = data.id;
    f.querySelector('[name=nama]').value       = data.nama;
    f.querySelector('[name=username]').value   = data.username;
    f.querySelector('[name=email]').value      = data.email || '';
    f.querySelector('[name=no_telepon]').value = data.no_telepon || '';
    f.querySelector('[name=alamat]').value     = data.alamat || '';
    f.querySelector('[name=level]').value      = data.level;
    openModal('modalEdit');
}
</script>

<?php include 'includes/footer.php'; ?>
