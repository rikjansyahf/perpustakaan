<!-- Topbar -->
<header class="bg-white shadow-sm px-4 py-3 flex items-center justify-between sticky top-0 z-10">
    <!-- Kiri: Hamburger + Page title -->
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700 p-1">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <span class="font-semibold text-gray-700 text-sm lg:text-base"><?= $pageTitle ?? 'Dashboard' ?></span>
    </div>

    <!-- Tengah: Logo + Search (desktop) -->
    <div class="hidden lg:flex items-center gap-3">
        <img src="<?= htmlspecialchars($cfg['app_logo']) ?>" alt="Logo" class="w-7 h-7 object-contain opacity-70">
        <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-64">
            <i class="fas fa-search text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari sesuatu..." class="bg-transparent text-sm outline-none w-full text-gray-600">
        </div>
    </div>

    <!-- Kanan: Notif + User -->
    <div class="flex items-center gap-3">
        <button class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
            <i class="fas fa-bell text-sm"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <a href="profile.php" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg px-2 py-1 transition">
            <?php
            $fotoSesi = $_SESSION['user']['foto'] ?? null;
            $fotoPath = $fotoSesi && file_exists('uploads/foto/' . $fotoSesi) ? 'uploads/foto/' . $fotoSesi : null;
            ?>
            <?php if ($fotoPath): ?>
            <img src="<?= $fotoPath ?>" alt="Foto" class="w-8 h-8 rounded-full object-cover border-2 border-blue-200">
            <?php else: ?>
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-bold"><?= strtoupper(substr($_SESSION['user']['nama'] ?? 'U', 0, 1)) ?></span>
            </div>
            <?php endif; ?>
            <span class="hidden md:block text-sm font-medium text-gray-700">
                <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User') ?>
            </span>
        </a>
    </div>
</header>
