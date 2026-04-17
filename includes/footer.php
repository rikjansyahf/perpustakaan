</main>
</div>
</div>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
// Close modal on backdrop click
document.querySelectorAll('[data-modal]').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});
</script>
</body>
</html>
