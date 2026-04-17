<div class="grid grid-cols-2 gap-3">
    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Buku</label>
        <input type="text" name="judul" required
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Judul buku">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pengarang</label>
        <input type="text" name="pengarang" required
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Nama pengarang">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Penerbit</label>
        <input type="text" name="penerbit"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Nama penerbit">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Terbit</label>
        <input type="number" name="tahun_terbit" min="1900" max="2099"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="2024">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
        <input type="text" name="isbn"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="ISBN">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
        <select name="id_kategori" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">-- Pilih Kategori --</option>
            <?php
            $katList = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");
            while ($k = mysqli_fetch_assoc($katList)):
            ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
        <input type="number" name="stok" min="0" value="1" required
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="3"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
            placeholder="Deskripsi singkat buku"></textarea>
    </div>
</div>
