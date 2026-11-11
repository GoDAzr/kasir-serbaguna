/*
 * Menu samping: HP (geser dari kiri), desktop (dikecilkan jadi shortcut ikon),
 * arahkan kursor untuk melebar sementara, dan gembok (kunci / tutup otomatis).
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
    var suppressPeek = false;
    try { locked = localStorage.getItem('menuLocked') === '1'; } catch (e) {}

    function setExpanded(v) { menuBtn.setAttribute('aria-expanded', v ? 'true' : 'false'); }

    // ----- Gembok: terkunci = tidak menutup sendiri, terbuka = menutup otomatis -----
    function paintLock() {
        menuLock.classList.toggle('on', locked);
        menuLock.setAttribute('aria-pressed', locked ? 'true' : 'false');
        menuLock.querySelector('use').setAttribute('href', locked ? '#i-lock' : '#i-unlock');
        menuLock.title = locked
            ? 'Menu terkunci, tidak menutup otomatis (klik untuk buka kunci)'
            : 'Menu menutup otomatis setelah dipilih (klik untuk kunci)';
    }

    menuLock.addEventListener('click', function () {
        locked = !locked;
        try { localStorage.setItem('menuLocked', locked ? '1' : '0'); } catch (e) {}
        paintLock();
        if (locked && desktop.matches) { setCollapsed(false, true); }   // dikunci = menu tampil terus
    });
    paintLock();

    // ----- HP: menu geser dari kiri + latar gelap -----
    function openDrawer() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
        setExpanded(true);
    }

    function closeDrawer() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
        setExpanded(false);
    }

    // ----- Desktop: hamburger mengecilkan / melebarkan menu samping -----
    function setCollapsed(v, persist) {
        app.classList.toggle('collapsed', v);
        if (!v) { app.classList.remove('peek'); }
        setExpanded(!v);
        if (persist) {
            try { localStorage.setItem('menuCollapsed', v ? '1' : '0'); } catch (e) {}
        }
    }

    // Dipanggil setelah sebuah menu dipilih: menutup sendiri kecuali dikunci
    D.afterMenuClick = function () {
        if (locked) { return; }
        if (!desktop.matches) { closeDrawer(); return; }
        if (app.classList.contains('peek')) {
            app.classList.remove('peek');
            suppressPeek = true;
        }
        setCollapsed(true, false);   // menutup otomatis tidak disimpan, hanya hamburger yang disimpan
    };

    menuBtn.addEventListener('click', function () {
        if (desktop.matches) {
            setCollapsed(!app.classList.contains('collapsed') || app.classList.contains('peek'), true);
            return;
        }
        sidebar.classList.contains('open') ? closeDrawer() : openDrawer();
    });

    menuClose.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeDrawer(); } });

    // ----- Desktop: kursor / klik area kosong pada shortcut -> melebar sementara -----
    sidebar.addEventListener('mouseenter', function () {
        if (desktop.matches && app.classList.contains('collapsed') && !suppressPeek) {
            app.classList.add('peek');
        }
    });

    sidebar.addEventListener('mouseleave', function () {
        suppressPeek = false;
        app.classList.remove('peek');
    });

    sidebar.addEventListener('click', function (e) {
        var rail = app.classList.contains('collapsed') && !app.classList.contains('peek');
        if (desktop.matches && rail && !e.target.closest('a, button')) {
            app.classList.add('peek');
        }
    });

    // ----- Sesuaikan dengan ukuran layar -----
    function sync() {
        if (desktop.matches) {
            closeDrawer();
            var saved = null;
            try { saved = localStorage.getItem('menuCollapsed'); } catch (e) {}
            var collapsed = !locked && saved === '1';   // dikunci = selalu tampil
            app.classList.toggle('collapsed', collapsed);
            app.classList.remove('peek');
            setExpanded(!collapsed);
        } else {
            app.classList.remove('collapsed', 'peek');
            setExpanded(sidebar.classList.contains('open'));
        }
    }

    sync();
    (desktop.addEventListener ? desktop.addEventListener('change', sync) : desktop.addListener(sync));
})();