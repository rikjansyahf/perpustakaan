<?php
include 'koneksi.php';
requireLogin();
$pageTitle = 'Profil Saya';
$id_user   = (int)$_SESSION['user']['id'];
$msg       = '';

// Ambil data user terbaru
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user WHERE id=$id_user"));

// ── Update info dasar ─────────────────────────────────────
if (isset($_POST['update_info'])) {
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $no_telp  = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
    $alamat   = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    mysqli_query($koneksi, "UPDATE user SET nama='$nama', email='$email', no_telepon='$no_telp', alamat='$alamat' WHERE id=$id_user");

    // Update session
    $_SESSION['user']['nama']       = $nama;
    $_SESSION['user']['email']      = $email;
    $_SESSION['user']['no_telepon'] = $no_telp;
    $_SESSION['user']['alamat']     = $alamat;

    $msg  = 'success|Informasi profil berhasil diperbarui.';
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user WHERE id=$id_user"));
}

// ── Ganti password ────────────────────────────────────────
if (isset($_POST['update_password'])) {
    $pw_lama  = md5(trim($_POST['password_lama']));
    $pw_baru  = trim($_POST['password_baru']);
    $pw_konfirm = trim($_POST['password_konfirm']);

    if ($pw_lama !== $user['password']) {
        $msg = 'error|Password lama tidak sesuai.';
    } elseif (strlen($pw_baru) < 6) {
        $msg = 'error|Password baru minimal 6 karakter.';
    } elseif ($pw_baru !== $pw_konfirm) {
        $msg = 'error|Konfirmasi password tidak cocok.';
    } else {
        $pw_hash = md5($pw_baru);
        mysqli_query($koneksi, "UPDATE user SET password='$pw_hash' WHERE id=$id_user");
        $msg = 'success|Password berhasil diubah.';
    }
}

// ── Upload foto profil ────────────────────────────────────
if (isset($_POST['update_foto']) && !empty($_FILES['foto']['name'])) {
    $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $allowed)) {
        $msg = 'error|Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
    } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        $msg = 'error|Ukuran foto maksimal 2MB.';
    } else {
        // Hapus foto lama jika bukan default
        if (!empty($user['foto']) && file_exists('uploads/foto/' . $user['foto'])) {
            unlink('uploads/foto/' . $user['foto']);
        }

        $filename = 'foto_' . $id_user . '_' . time() . '.' . $ext;
        if (!is_dir('uploads/foto')) mkdir('uploads/foto', 0755, true);

        if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/foto/' . $filename)) {
            mysqli_query($koneksi, "UPDATE user SET foto='$filename' WHERE id=$id_user");
            $_SESSION['user']['foto'] = $filename;
            $msg  = 'success|Foto profil berhasil diperbarui.';
            $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user WHERE id=$id_user"));
        } else {
            $msg = 'error|Gagal mengupload foto. Periksa permission folder.';
        }
    }
}

// Hapus foto (reset ke default)
if (isset($_GET['hapus_foto'])) {
    if (!empty($user['foto']) && file_exists('uploads/foto/' . $user['foto'])) {
        unlink('uploads/foto/' . $user['foto']);
    }
    mysqli_query($koneksi, "UPDATE user SET foto=NULL WHERE id=$id_user");
    $_SESSION['user']['foto'] = null;
    $msg  = 'success|Foto profil dihapus.';
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM user WHERE id=$id_user"));
}

// Helper: URL foto
$fotoUrl = !empty($user['foto']) && file_exists('uploads/foto/' . $user['foto'])
    ? 'uploads/foto/' . $user['foto']
    : null;

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
    <p class="text-gray-500 text-sm mt-0.5">Kelola informasi akun dan keamanan</p>
</div>

<?php if ($msg): [$type, $text] = explode('|', $msg, 2); ?>
<div class="mb-5 px-4 py-3 rounded-lg text-sm <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> flex items-center gap-2">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i><?= htmlspecialchars($text) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <!-- Kolom kiri: Foto profil -->
    <div class="xl:col-span-1 space-y-5">

        <!-- Card foto -->
        <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
            <!-- Avatar -->
            <div class="relative inline-block mb-4">
                <?php if ($fotoUrl): ?>
                <img src="<?= $fotoUrl ?>" alt="Foto Profil"
                    class="w-28 h-28 rounded-full object-cover border-4 border-blue-100 shadow">
                <?php else: ?>
                <div class="w-28 h-28 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center border-4 border-blue-100 shadow mx-auto">
                    <span class="text-white text-4xl font-bold"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                </div>
                <?php endif; ?>
                <!-- Edit overlay -->
                <label for="fotoInput" class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center cursor-pointer shadow transition">
                    <i class="fas fa-camera text-white text-xs"></i>
                </label>
            </div>

            <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($user['nama']) ?></h3>
            <p class="text-gray-500 text-sm"><?= htmlspecialchars($user['email'] ?: '-') ?></p>
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold <?= isAdmin() ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' ?>">
                <?= ucfirst($user['level']) ?>
            </span>

            <!-- Form upload foto -->
            <form method="POST" enctype="multipart/form-data" id="formFoto" class="mt-4">
                <input type="file" name="foto" id="fotoInput" accept=".jpg,.jpeg,.png,.webp" class="hidden"
                    onchange="document.getElementById('formFoto').submit()">
                <button type="submit" name="update_foto" class="hidden"></button>
            </form>

            <?php if ($fotoUrl): ?>
            <a href="?hapus_foto=1" onclick="return confirm('Hapus foto profil?')"
                class="mt-2 inline-block text-xs text-red-500 hover:underline">
                <i class="fas fa-trash mr-1"></i>Hapus foto
            </a>
            <?php endif; ?>

            <p class="text-xs text-gray-400 mt-3">JPG, PNG, WEBP · Maks 2MB</p>
        </div>

        <!-- Info singkat -->
        <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
            <h4 class="font-semibold text-gray-700 text-sm">Informasi Akun</h4>
            <div class="flex items-center gap-3 text-sm">
                <i class="fas fa-user w-4 text-gray-400 text-center"></i>
                <span class="text-gray-600"><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <i class="fas fa-phone w-4 text-gray-400 text-center"></i>
                <span class="text-gray-600"><?= htmlspecialchars($user['no_telepon'] ?: '-') ?></span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <i class="fas fa-map-marker-alt w-4 text-gray-400 text-center"></i>
                <span class="text-gray-600"><?= htmlspecialchars($user['alamat'] ?: '-') ?></span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <i class="fas fa-calendar w-4 text-gray-400 text-center"></i>
                <span class="text-gray-600">Bergabung <?= date('d M Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Kolom kanan: Form edit -->
    <div class="xl:col-span-2 space-y-5">

        <!-- Form info dasar -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-user-edit text-blue-500"></i> Informasi Pribadi
            </h3>
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                        <p class="text-xs text-gray-400 mt-1">Username tidak dapat diubah</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="text" name="no_telepon" value="<?= htmlspecialchars($user['no_telepon'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="alamat" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            placeholder="Alamat lengkap"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="pt-1">
                    <button type="submit" name="update_info"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Form ganti password -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-lock text-blue-500"></i> Ganti Password
            </h3>
            <form method="POST" class="space-y-4" id="formPassword">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                    <div class="relative">
                        <input type="password" name="password_lama" id="pwLama" required
                            class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Masukkan password lama">
                        <button type="button" onclick="togglePw('pwLama','eyeLama')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm" id="eyeLama"></i>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="password_baru" id="pwBaru" required minlength="6"
                                class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Min. 6 karakter" oninput="checkStrength(this.value)">
                            <button type="button" onclick="togglePw('pwBaru','eyeBaru')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm" id="eyeBaru"></i>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div class="mt-1.5 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div id="strengthBar" class="h-full rounded-full transition-all duration-300 w-0"></div>
                        </div>
                        <p id="strengthText" class="text-xs mt-1 text-gray-400"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" name="password_konfirm" id="pwKonfirm" required
                                class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ulangi password baru" oninput="checkMatch()">
                            <button type="button" onclick="togglePw('pwKonfirm','eyeKonfirm')" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm" id="eyeKonfirm"></i>
                            </button>
                        </div>
                        <p id="matchText" class="text-xs mt-1"></p>
                    </div>
                </div>
                <div class="pt-1">
                    <button type="submit" name="update_password"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
                        <i class="fas fa-key mr-2"></i>Ganti Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
// Toggle show/hide password
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Password strength
function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%',  color: 'bg-red-500',    label: 'Sangat lemah' },
        { w: '40%',  color: 'bg-orange-400',  label: 'Lemah' },
        { w: '60%',  color: 'bg-yellow-400',  label: 'Cukup' },
        { w: '80%',  color: 'bg-blue-500',    label: 'Kuat' },
        { w: '100%', color: 'bg-green-500',   label: 'Sangat kuat' },
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.width = val ? lvl.w : '0';
    bar.className   = `h-full rounded-full transition-all duration-300 ${val ? lvl.color : ''}`;
    text.textContent = val ? lvl.label : '';
    text.className   = `text-xs mt-1 ${val ? lvl.color.replace('bg-','text-') : 'text-gray-400'}`;
}

// Cek kecocokan password
function checkMatch() {
    const baru    = document.getElementById('pwBaru').value;
    const konfirm = document.getElementById('pwKonfirm').value;
    const el      = document.getElementById('matchText');
    if (!konfirm) { el.textContent = ''; return; }
    if (baru === konfirm) {
        el.textContent = '✓ Password cocok';
        el.className   = 'text-xs mt-1 text-green-600';
    } else {
        el.textContent = '✗ Password tidak cocok';
        el.className   = 'text-xs mt-1 text-red-500';
    }
}

// Auto submit saat pilih foto
document.getElementById('fotoInput').addEventListener('change', function() {
    if (this.files[0]) {
        // Preview sebelum submit
        const reader = new FileReader();
        reader.onload = e => {
            const imgs = document.querySelectorAll('img[alt="Foto Profil"]');
            imgs.forEach(img => img.src = e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
        // Submit form
        document.querySelector('#formFoto [name=update_foto]').click();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
