<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
    <input type="text" name="nama" required
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Nama lengkap">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
    <input type="text" name="username" required
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Username">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
    <input type="email" name="email"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Email">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
    <input type="text" name="no_telepon"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="No. telepon">
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
    <textarea name="alamat" rows="2"
        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        placeholder="Alamat"></textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
    <select name="level" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="peminjam">Peminjam</option>
        <option value="petugas">Petugas</option>
        <option value="admin">Admin</option>
    </select>
</div>
