/*
 * Bar atas (hamburger, sapaan, tombol pengaturan / keluar) ikut turun saat halaman di-scroll.
 * Hanya bagian ini yang ikut, kartu pendapatan tetap ikut scroll biasa.
 */
(function () {
    var holder = document.getElementById('topbarHolder');
    var slot = document.getElementById('topbarSlot');
    var ticking = false;

    function update() {
        ticking = false;
        var fixed = slot.classList.contains('is-fixed');
        var top = holder.getBoundingClientRect().top;

        if (!fixed && top <= 10) {
            holder.style.height = slot.offsetHeight + 'px';   // jaga tinggi supaya isi tidak melompat
            slot.classList.add('is-fixed');
        } else if (fixed && top > 10) {
            slot.classList.remove('is-fixed');
            holder.style.height = '';
        }
    }

    window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });

    window.addEventListener('resize', function () {
        slot.classList.remove('is-fixed');
        holder.style.height = '';
        update();
    });

    update();
})();

/* Tombol pengaturan: buka / tutup daftar Akun & Pengguna */
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
