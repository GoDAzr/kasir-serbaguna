/*
 * Menu samping: HP (geser dari kiri), desktop (dikecilkan jadi shortcut ikon), dan gembok.
 *   - Gembok TERBUKA : menu menutup sendiri setelah salah satu menu diklik.
 *   - Gembok TERKUNCI: menu tetap terbuka.
 *   - Menu yang dikecilkan hanya melebar kalau hamburger ditekan (kursor di atasnya tidak melebarkan),
 *     ikon-ikonnya tetap bisa diklik.
 * Dipakai bersama file lain lewat window.Dashboard.
 */
(function () {
    var D = window.Dashboard = window.Dashboard || {};

    var app = document.getElementById('app');
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('backdrop');
    var menuBtn = document.getElementById('menuBtn');
    var menuClose = document.getElementById('menuClose');
    var menuLock = document.getElementById('menuLock');
    var desktop = window.matchMedia('(min-width: 992px)');

    var locked = false;
    try { locked = localStorage.getItem('menuLocked') === '1'; } catch (e) {}

    function setExpanded(v) { menuBtn.setAttribute('aria-expanded', v ? 'true' : 'false'); }

    // ----- Gembok -----
    function paintLock() {
        menuLock.classList.toggle('on', locked);
        menuLock.setAttribute('aria-pressed', locked ? 'true' : 'false');
        menuLock.querySelector('use').setAttribute('href', locked ? '#i-lock' : '#i-unlock');
        menuLock.title = locked
            ? 'Menu terkunci, tetap terbuka (klik untuk buka kunci)'
            : 'Menu menutup otomatis setelah dipilih (klik untuk kunci)';
    }

    function setLocked(v) {
        locked = v;
        try { localStorage.setItem('menuLocked', locked ? '1' : '0'); } catch (e) {}
        paintLock();
    }

    // ----- HP: menu geser dari kiri + latar gelap -----
    function openDrawer() { sidebar.classList.add('open'); backdrop.classList.add('show'); setExpanded(true); }
    function closeDrawer() { sidebar.classList.remove('open'); backdrop.classList.remove('show'); setExpanded(false); }

    // ----- Desktop: melebar / mengecil, selalu disimpan supaya halaman lain ikut -----
    function setCollapsed(v) {
        app.classList.toggle('collapsed', v);
        setExpanded(!v);
        try { localStorage.setItem('menuCollapsed', v ? '1' : '0'); } catch (e) {}
    }

    menuLock.addEventListener('click', function () {
        setLocked(!locked);
        if (locked && desktop.matches) { setCollapsed(false); }   // dikunci = menu terbuka terus
    });

    // Dipanggil setelah sebuah menu diklik: menutup sendiri kecuali dikunci
    D.afterMenuClick = function () {
        if (locked) { return; }
        if (!desktop.matches) { closeDrawer(); return; }
        setCollapsed(true);
    };

    menuBtn.addEventListener('click', function () {
        if (desktop.matches) {
            var collapse = !app.classList.contains('collapsed');
            if (collapse && locked) { setLocked(false); }   // hamburger menutup = tidak lagi terkunci
            setCollapsed(collapse);
            return;
        }
        sidebar.classList.contains('open') ? closeDrawer() : openDrawer();
    });

    menuClose.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeDrawer(); } });

    // Menu berupa link (pindah halaman): tutup dulu supaya halaman berikutnya ikut mengecil
    sidebar.addEventListener('click', function (e) {
        var a = e.target.closest('a[href]');
        if (!a || a.hasAttribute('data-soon') || a.getAttribute('href') === '#') { return; }
        D.afterMenuClick();
    });

    // ----- Sesuaikan dengan ukuran layar -----
    function sync() {
        if (desktop.matches) {
            closeDrawer();
            var saved = null;
            try { saved = localStorage.getItem('menuCollapsed'); } catch (e) {}
            var collapsed = !locked && saved === '1';   // dikunci = selalu terbuka
            app.classList.toggle('collapsed', collapsed);
            setExpanded(!collapsed);
        } else {
            app.classList.remove('collapsed');
            setExpanded(sidebar.classList.contains('open'));
        }
    }

    paintLock();
    sync();
    (desktop.addEventListener ? desktop.addEventListener('change', sync) : desktop.addListener(sync));
})();
