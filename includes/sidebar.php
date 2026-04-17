<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-blue-700 to-indigo-900 text-white z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col">

    <!-- Brand -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-blue-600">
        <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-book-open text-blue-600 text-sm"></i>
        </div>
        <div class="leading-tight">
            <p class="font-bold text-sm">PERPUSTAKAAN</p>
            <p class="text-blue-300 text-xs">SMKPAS2</p>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        <p class="text-blue-300 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Menu Utama</p>

        <!-- Dashboard: semua role -->
        <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-tachometer-alt w-4 text-center"></i>
            <span>Dashboard</span>
        </a>

        <?php if (isAdminOrPetugas()): ?>
        <!-- Kategori: admin & petugas -->
        <a href="kategori.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-tags w-4 text-center"></i>
            <span>Kategori</span>
        </a>

        <!-- Buku: admin & petugas -->
        <a href="tables.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-book w-4 text-center"></i>
            <span>Buku</span>
        </a>

        <!-- Peminjaman: admin & petugas -->
        <a href="peminjaman.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-book-open w-4 text-center"></i>
            <span>Peminjaman</span>
            <?php
            $reqCount = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM request_pinjam WHERE status='menunggu'"))[0];
            if ($reqCount > 0):
            ?>
            <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $reqCount ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if (isPeminjam()): ?>
        <!-- Buku: peminjam (read only) -->
        <a href="tables.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-book w-4 text-center"></i>
            <span>Katalog Buku</span>
        </a>

        <!-- Riwayat peminjaman saya -->
        <a href="riwayat.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-history w-4 text-center"></i>
            <span>Riwayat Saya</span>
        </a>

        <!-- Status request peminjaman -->
        <a href="request.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'request.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-inbox w-4 text-center"></i>
            <span>Permintaan Saya</span>
        </a>
        <?php endif; ?>

        <!-- Ulasan: semua role -->
        <a href="ulasan.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'ulasan.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
            <i class="fas fa-comment w-4 text-center"></i>
            <span>Ulasan</span>
        </a>

        <?php if (isAdmin()): ?>
        <div class="pt-2">
            <p class="text-blue-300 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Admin</p>
            <!-- Kelola User: admin only -->
            <a href="user.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl <?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'bg-white text-blue-700 font-semibold' : 'text-blue-100 hover:bg-blue-600' ?> transition text-sm">
                <i class="fas fa-users w-4 text-center"></i>
                <span>Kelola User</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- User info + Logout -->
    <div class="px-4 py-4 border-t border-blue-600">
        <!-- Role badge -->
        <div class="flex items-center gap-3 px-3 py-2 mb-2">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-xs"></i>
            </div>
            <div class="leading-tight overflow-hidden">
                <p class="text-sm font-medium truncate"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User') ?></p>
                <span class="text-xs px-1.5 py-0.5 rounded-full font-medium
                    <?php
                        $level = $_SESSION['user']['level'] ?? 'peminjam';
                        echo match($level) {
                            'admin'    => 'bg-yellow-400 text-yellow-900',
                            'petugas'  => 'bg-green-400 text-green-900',
                            default    => 'bg-blue-400 text-blue-900',
                        };
                    ?>">
                    <?= ucfirst($level) ?>
                </span>
            </div>
        </div>

        <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-300 hover:bg-red-500 hover:text-white transition text-sm">
            <i class="fas fa-sign-out-alt w-4 text-center"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
