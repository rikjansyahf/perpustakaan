<?php
include 'koneksi.php';
requireRole('admin');
$pageTitle = 'Pengaturan';
$msg = '';

// Simpan settings
if (isset($_POST['simpan'])) {
    $fields = ['app_name', 'app_subtitle', 'denda_per_hari', 'maks_hari_pinjam', 'footer_text'];
    foreach ($fields as $key) {
        $val = mysqli_real_escape_string($koneksi, trim($_POST[$key] ?? ''));
        mysqli_query($koneksi, "INSERT INTO settings (setting_key, setting_value)
            VALUES ('$key','$val')
            ON DUPLICATE KEY UPDATE setting_value='$val'");
    }

    // Upload logo
    if (!empty($_FILES['app_logo']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION));
        $allowed  = ['png','jpg','jpeg','svg','webp'];
        if (!in_array($ext, $allowed)) {
            $msg = 'error|Format logo tidak didukung. Gunakan PNG, JPG, SVG, atau WEBP.';
        } else {
            $filename = 'logo_app.' . $ext;
            if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $filename)) {
                mysqli_query($koneksi, "INSERT INTO settings (setting_key, setting_value)
                    VALUES ('app_logo','$filename')
                    ON DUPLICATE KEY UPDATE setting_value='$filename'");
            } else {
                $msg = 'error|Gagal mengupload logo. Periksa permission folder.';
            }
        }
    }

    if (!$msg) $msg = 'success|Pengaturan berhasil disimpan.';
}

include 'includes/header.php';
// Reload cfg setelah simpan
$cfg = getAllSettings();
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Pengaturan Aplikasi</h1>
    <p class="text-gray-500 text-sm mt-0.5">Konfigurasi tampilan dan sistem perpustakaan</p>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg, 2); ?>
<div class="mb-5 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2"></i><?= $text ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-5 max-w-2xl">

    <!-- Identitas Aplikasi -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-id-card text-blue-500"></i> Identitas Aplikasi
        </h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aplikasi</label>
                <input type="text" name="app_name" value="<?= htmlspecialchars($cfg['app_name']) ?>" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Perpustakaan Digital">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle / Nama Sekolah</label>
                <input type="text" name="app_subtitle" value="<?= htmlspecialchars($cfg['app_subtitle']) ?>"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="SMKPAS2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teks Footer</label>
                <input type="text" name="footer_text" value="<?= htmlspecialchars($cfg['footer_text']) ?>"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="© 2026 Perpustakaan Digital">
            </div>
        </div>
    </div>

    <!-- Logo -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-image text-blue-500"></i> Logo Aplikasi
        </h3>
        <div class="flex items-center gap-6">
            <!-- Preview -->
            <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center flex-shrink-0 overflow-hidden border border-gray-200">
                <img id="logoPreview" src="<?= htmlspecialchars($cfg['app_logo']) ?>" alt="Logo"
                    class="w-full h-full object-contain p-1">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Logo Baru</label>
                <input type="file" name="app_logo" id="logoInput" accept=".png,.jpg,.jpeg,.svg,.webp"
                    onchange="previewLogo(this)"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">Format: PNG, JPG, SVG, WEBP. Maks 2MB.</p>
                <p class="text-xs text-gray-400">File saat ini: <span class="font-medium text-gray-600"><?= htmlspecialchars($cfg['app_logo']) ?></span></p>
            </div>
        </div>
    </div>

    <!-- Aturan Peminjaman -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-book-open text-blue-500"></i> Aturan Peminjaman
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Denda per Hari (Rp)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm">Rp</span>
                    <input type="number" name="denda_per_hari" value="<?= htmlspecialchars($cfg['denda_per_hari']) ?>" min="0" required
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maks. Hari Peminjaman</label>
                <div class="relative">
                    <input type="number" name="maks_hari_pinjam" value="<?= htmlspecialchars($cfg['maks_hari_pinjam']) ?>" min="1" max="60" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 text-sm">hari</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-eye text-blue-500"></i> Preview Sidebar
        </h3>
        <div class="bg-gradient-to-b from-blue-700 to-indigo-900 rounded-xl p-4 w-56">
            <div class="flex items-center gap-3">
                <img id="sidebarLogoPreview" src="<?= htmlspecialchars($cfg['app_logo']) ?>" alt="Logo"
                    class="w-9 h-9 rounded-xl object-contain bg-white p-0.5 flex-shrink-0">
                <div>
                    <p class="text-white font-bold text-sm" id="previewName"><?= htmlspecialchars($cfg['app_name']) ?></p>
                    <p class="text-blue-300 text-xs" id="previewSubtitle"><?= htmlspecialchars($cfg['app_subtitle']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" name="simpan"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
            <i class="fas fa-save mr-2"></i>Simpan Pengaturan
        </button>
        <a href="index.php" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm transition">
            Batal
        </a>
    </div>
</form>

<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('sidebarLogoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Live preview nama & subtitle
document.querySelector('[name=app_name]').addEventListener('input', function() {
    document.getElementById('previewName').textContent = this.value || 'Nama Aplikasi';
});
document.querySelector('[name=app_subtitle]').addEventListener('input', function() {
    document.getElementById('previewSubtitle').textContent = this.value || 'Subtitle';
});
</script>

<?php include 'includes/footer.php'; ?>
