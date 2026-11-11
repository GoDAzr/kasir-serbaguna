/*
 * Navigasi menu bertingkat. Menu kiri (shortcut) dan kanan (kartu) selalu sinkron:
 *   daftar kategori -> isi kategori (Manajemen) -> isi menu (Kasir / POS)
 */
(function () {
    var D = window.Dashboard = window.Dashboard || {};

    var stack = ['root'];
    var wide = document.querySelector('.dd.wide');
    var side = document.querySelector('.dd.side');
    var titles = document.querySelectorAll('.js-menu-title');

    function after() { if (D.afterMenuClick) { D.afterMenuClick(); } }

    // Tampilkan halaman menu sesuai posisi terakhir di `stack`
    function render(dir) {
        var id = stack[stack.length - 1];
        var title = 'Menu';

        if (wide) {
            var cur = wide.querySelector('.dd-view.is-on');
            if (cur) { cur.classList.remove('is-on', 'fwd', 'bck'); }
            var next = wide.querySelector('[data-view="' + id + '"]');
            if (next) {
                next.classList.add('is-on', dir);
                title = next.getAttribute('data-title');
            }
        }
        titles.forEach(function (t) { t.textContent = title; });

        if (side) {
            var m = /^(g\d+)/.exec(id);
            var gid = m ? m[1] : null;
            side.classList.toggle('has-open', !!gid);
            side.querySelectorAll('.mg').forEach(function (mg) {
                var open = mg.getAttribute('data-group') === gid;
                mg.classList.toggle('open', open);
                mg.querySelector('.mg-head').setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            side.querySelectorAll('.mi-head').forEach(function (el) {
                el.classList.toggle('active', el.getAttribute('data-go') === id);
            });
        }
    }

    // Dashboard: kembali ke daftar awal (tanpa muat ulang halaman)
    var dash = document.getElementById('navDashboard');
    if (dash) {
        dash.addEventListener('click', function (e) {
            e.preventDefault();
            stack = ['root'];
            render('bck');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            after();
        });
    }

    document.addEventListener('click', function (e) {
        var go = e.target.closest('[data-go]');

        if (go) {
            var id = go.getAttribute('data-go');
            if (go.closest('.dd.side')) {
                var gid = /^(g\d+)/.exec(id)[1];
                if (e.target.closest('[data-toggle]')) {
                    // Panah >: hanya buka / tutup isi kategori, menu samping tidak menutup
                    stack = (stack[1] === gid) ? ['root'] : ['root', gid];
                    render('fwd');
                    return;
                }
                // Klik nama kategori atau salah satu isinya: pilih, lalu menu menutup (kalau tidak dikunci)
                stack = (id === gid) ? ['root', gid] : ['root', gid, id];
                render('fwd');
                after();
                return;
            }
            stack.push(id);
            render('fwd');
            return;
        }

        if (e.target.closest('[data-back]') && stack.length > 1) {
            stack.pop();
            render('bck');
        }
    });
})();