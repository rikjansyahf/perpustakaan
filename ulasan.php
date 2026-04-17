<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Ulasan Buku';
$msg = '';
$id_user = (int)$_SESSION['user']['id'];

// Tambah ulasan
if (isset($_POST['tambah'])) {
    $id_buku  = (int)$_POST['id_buku'];
    $rating   = (int)$_POST['rating'];
    $komentar = mysqli_real_escape_string($koneksi, trim($_POST['komentar']));

    // Cek sudah pernah ulasan buku ini
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM ulasan WHERE id_user=$id_user AND id_buku=$id_buku"))[0];
    if ($cek > 0) {
        $msg = 'error|Anda sudah memberikan ulasan untuk buku ini.';
    } else {
        mysqli_query($koneksi, "INSERT INTO ulasan (id_user,id_buku,rating,komentar) VALUES ($id_user,$id_buku,$rating,'$komentar')");
        $msg = 'success|Ulasan berhasil ditambahkan.';
    }
}

// Hapus ulasan (milik sendiri atau admin)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $cond = isAdmin() ? "id=$id" : "id=$id AND id_user=$id_user";
    mysqli_query($koneksi, "DELETE FROM ulasan WHERE $cond");
    $msg = 'success|Ulasan dihapus.';
}

$search = mysqli_real_escape_string($koneksi, trim($_GET['q'] ?? ''));
$where  = $search ? "WHERE b.judul LIKE '%$search%' OR u.nama LIKE '%$search%'" : '';

$data = mysqli_query($koneksi, "
    SELECT ul.*, u.nama as nama_user, b.judul as judul_buku
    FROM ulasan ul
    JOIN user u ON ul.id_user=u.id
    JOIN buku b ON ul.id_buku=b.id
    $where ORDER BY ul.id DESC
");

$bukuList = mysqli_query($koneksi, "SELECT id, judul FROM buku ORDER BY judul");

include 'includes/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Ulasan Buku</h1>
        <p class="text-gray-500 text-sm mt-0.5">Pendapat pembaca tentang koleksi perpustakaan</p>
    </div>
    <button onclick="openModal('modalTambah')" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <i class="fas fa-star text-xs"></i> Tulis Ulasan
    </button>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg); ?>
<div class="mb-4 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<!-- Search -->
<form method="GET" class="mb-4 flex gap-2">
    <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400"><i class="fas fa-search text-sm"></i></span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari buku atau nama..."
            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
    </div>
    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">Cari</button>
    <?php if ($search): ?><a href="ulasan.php" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">Reset</a><?php endif; ?>
</form>

<!-- Ulasan Cards -->
<div class="space-y-4">
    <?php while ($row = mysqli_fetch_assoc($data)): ?>
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-blue-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($row['nama_user']) ?></p>
                        <span class="text-gray-400 text-xs">·</span>
                        <p class="text-blue-600 text-sm font-medium"><?= htmlspecialchars($row['judul_buku']) ?></p>
                    </div>
                    <!-- Stars -->
                    <div class="flex gap-0.5 my-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star text-xs <?= $i <= $row['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>"></i>
                        <?php endfor; ?>
                        <span class="text-xs text-gray-400 ml-1"><?= $row['rating'] ?>/5</span>
                    </div>
                    <p class="text-gray-600 text-sm mt-1"><?= nl2br(htmlspecialchars($row['komentar'])) ?></p>
                    <p class="text-xs text-gray-400 mt-2"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></p>
                </div>
            </div>
            <?php if ($row['id_user'] == $id_user || isAdmin()): ?>
            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus ulasan ini?')"
                class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition flex-shrink-0">
                <i class="fas fa-trash text-sm"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Modal Tambah Ulasan -->
<div id="modalTambah" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center modal-backdrop" data-modal>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Tulis Ulasan</h3>
            <button onclick="closeModal('modalTambah')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Buku</label>
                <select name="id_buku" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Buku --</option>
                    <?php while ($b = mysqli_fetch_assoc($bukuList)): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['judul']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <div class="flex gap-2" id="starRating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" onclick="setRating(<?= $i ?>)"
                        class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition" data-val="<?= $i ?>">
                        <i class="fas fa-star"></i>
                    </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                <textarea name="komentar" rows="4" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    placeholder="Tulis pendapat anda tentang buku ini..."></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" name="tambah" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg text-sm transition">Kirim Ulasan</button>
                <button type="button" onclick="closeModal('modalTambah')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.classList.toggle('text-yellow-400', parseInt(btn.dataset.val) <= val);
        btn.classList.toggle('text-gray-300',   parseInt(btn.dataset.val) >  val);
    });
}
setRating(5); // default
</script>

<?php include 'includes/footer.php'; ?>
