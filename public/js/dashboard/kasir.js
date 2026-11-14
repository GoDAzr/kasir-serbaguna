/* Tampilan kasir: tombol pengaturan (gerigi) membuka / menutup daftar akun */
(function () {
    var btn = document.getElementById('gearBtn');
    var menu = document.getElementById('gearMenu');
    if (!btn || !menu) { return; }

    function set(open) {
        menu.hidden = !open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function (e) { e.stopPropagation(); set(menu.hidden); });
    document.addEventListener('click', function (e) { if (!menu.hidden && !menu.contains(e.target)) { set(false); } });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); } });
})();
