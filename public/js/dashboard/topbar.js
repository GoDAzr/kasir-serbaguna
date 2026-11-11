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
