/*
 * Dashboard: kartu yang bisa dibuka / ditutup (>), grafik penjualan (batang / garis / area / donat)
 * dan pencarian + filter tanggal untuk aktivitas terbaru. Semua digambar sendiri (SVG), tanpa library.
 */
(function () {
    var NS = 'http://www.w3.org/2000/svg';
    var COLORS = ['#16B364', '#2563EB', '#FFC700', '#F97316', '#E11D48', '#0EA5C6', '#0B1220', '#64748B'];
    var rp = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };
    var short = function (n) {
        if (n >= 1e9) { return (n / 1e9).toFixed(1).replace('.0', '') + ' M'; }
        if (n >= 1e6) { return (n / 1e6).toFixed(1).replace('.0', '') + ' jt'; }
        if (n >= 1e3) { return Math.round(n / 1e3) + ' rb'; }
        return String(Math.round(n));
    };

    function el(name, attrs, parent) {
        var e = document.createElementNS(NS, name);
        Object.keys(attrs || {}).forEach(function (k) { e.setAttribute(k, attrs[k]); });
        if (parent) { parent.appendChild(e); }
        return e;
    }

    function text(parent, x, y, str, cls, anchor) {
        var t = el('text', { x: x, y: y, class: cls, 'text-anchor': anchor || 'middle' }, parent);
        t.textContent = str;
        return t;
    }

    // ---------- Buka / tutup kartu (ingat pilihan terakhir) ----------
    document.querySelectorAll('[data-fold]').forEach(function (card) {
        var key = 'fold:' + card.getAttribute('data-fold');
        var btn = card.querySelector('.fold-btn');
        var body = card.querySelector('.fold-body');

        function set(open, save) {
            card.classList.toggle('is-open', open);
            body.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (save) { try { localStorage.setItem(key, open ? '1' : '0'); } catch (e) {} }
            if (open && card._opened) { card._opened(); }
        }

        var saved = null;
        try { saved = localStorage.getItem(key); } catch (e) {}
        set(saved !== '0', false);
        btn.addEventListener('click', function () { set(body.hidden, true); });
    });

    // ---------- Grafik penjualan ----------
    var card = document.getElementById('grafik');
    var host = card && card.querySelector('.chart-host');

    if (host) {
        var data = [];
        try { data = JSON.parse(card.getAttribute('data-chart') || '[]'); } catch (e) {}
        var type = 'bar';
        try { type = localStorage.getItem('chartType') || 'bar'; } catch (e) {}

        var cartesian = function () {
            var n = data.length, H = 230, padL = 46, padR = 12, padT = 24, padB = 28;
            var slot = 48;
            var W = Math.max(host.clientWidth - 2, n * slot + padL + padR);
            var plotW = W - padL - padR, plotH = H - padT - padB;
            slot = plotW / n;

            var max = 0;
            data.forEach(function (d) { max = Math.max(max, d[1]); });
            var mag = Math.pow(10, Math.floor(Math.log(Math.max(max, 1)) / Math.LN10));
            var top = Math.ceil(max / mag * 2) / 2 * mag || 1;   // batas atas yang rapi
            var X = function (i) { return padL + slot * (i + 0.5); };
            var Y = function (v) { return padT + plotH * (1 - v / top); };

            var svg = el('svg', { width: W, height: H, viewBox: '0 0 ' + W + ' ' + H, class: 'ch' });
            for (var g = 0; g <= 4; g++) {
                var gy = padT + plotH * g / 4;
                el('line', { x1: padL, x2: W - padR, y1: gy, y2: gy, class: 'ch-grid' }, svg);
                text(svg, padL - 6, gy + 3, short(top * (1 - g / 4)), 'ch-y', 'end');
            }

            if (type === 'bar') {
                var bw = Math.min(34, slot * 0.6);
                data.forEach(function (d, i) {
                    var h = Math.max(3, plotH * d[1] / top);
                    var r = el('rect', { x: X(i) - bw / 2, y: padT + plotH - h, width: bw, height: h, rx: 6, class: 'ch-bar' }, svg);
                    el('title', {}, r).textContent = d[0] + ': ' + rp(d[1]);
                });
            } else {
                var pts = data.map(function (d, i) { return X(i) + ',' + Y(d[1]); });
                if (type === 'area') {
                    el('polygon', { points: X(0) + ',' + (padT + plotH) + ' ' + pts.join(' ') + ' ' + X(n - 1) + ',' + (padT + plotH), class: 'ch-area' }, svg);
                }
                el('polyline', { points: pts.join(' '), class: 'ch-line' }, svg);
                data.forEach(function (d, i) {
                    var c = el('circle', { cx: X(i), cy: Y(d[1]), r: 4, class: 'ch-dot' }, svg);
                    el('title', {}, c).textContent = d[0] + ': ' + rp(d[1]);
                });
            }

            data.forEach(function (d, i) {
                text(svg, X(i), H - 9, d[0], 'ch-x');
                if (n <= 12) {
                    var vy = type === 'bar' ? padT + plotH - Math.max(3, plotH * d[1] / top) - 5 : Y(d[1]) - 9;
                    text(svg, X(i), vy, short(d[1]), 'ch-v');
                }
            });
            host.appendChild(svg);
        };

        var donut = function () {
            var items = data.slice().sort(function (a, b) { return b[1] - a[1]; });
            if (items.length > 8) {
                var rest = items.slice(7).reduce(function (a, d) { return a + d[1]; }, 0);
                items = items.slice(0, 7).concat([['Lainnya', rest]]);
            }
            var total = items.reduce(function (a, d) { return a + d[1]; }, 0) || 1;
            var R = 70, C = 2 * Math.PI * R, off = 0;

            var wrap = document.createElement('div');
            wrap.className = 'donut';
            var svg = el('svg', { width: 190, height: 190, viewBox: '0 0 190 190', class: 'ch' });
            items.forEach(function (d, i) {
                var len = d[1] / total * C;
                var c = el('circle', {
                    cx: 95, cy: 95, r: R, fill: 'none', stroke: COLORS[i % COLORS.length], 'stroke-width': 30,
                    'stroke-dasharray': len + ' ' + (C - len), 'stroke-dashoffset': -off, transform: 'rotate(-90 95 95)'
                }, svg);
                el('title', {}, c).textContent = d[0] + ': ' + rp(d[1]);
                off += len;
            });
            text(svg, 95, 92, short(total), 'ch-big');
            text(svg, 95, 110, 'total', 'ch-x');
            wrap.appendChild(svg);

            var list = document.createElement('div');
            list.className = 'legend';
            items.forEach(function (d, i) {
                var row = document.createElement('div');
                row.innerHTML = '<i style="background:' + COLORS[i % COLORS.length] + '"></i><span></span><b></b><small></small>';
                row.children[1].textContent = d[0];
                row.children[2].textContent = rp(d[1]);
                row.children[3].textContent = Math.round(d[1] / total * 100) + '%';
                list.appendChild(row);
            });
            wrap.appendChild(list);
            host.appendChild(wrap);
        };

        var draw = function () {
            host.innerHTML = '';
            if (!data.length || !host.clientWidth) { return; }
            host.classList.toggle('is-donut', type === 'donut');
            (type === 'donut' ? donut : cartesian)();
        };

        card.querySelectorAll('[data-type]').forEach(function (b) {
            b.classList.toggle('on', b.getAttribute('data-type') === type);
            b.addEventListener('click', function () {
                type = b.getAttribute('data-type');
                try { localStorage.setItem('chartType', type); } catch (e) {}
                card.querySelectorAll('[data-type]').forEach(function (x) { x.classList.toggle('on', x === b); });
                draw();
            });
        });

        card._opened = draw;
        window.addEventListener('resize', draw);
        draw();
    }

    // ---------- Aktivitas terbaru: cari + tanggal ----------
    var act = document.getElementById('aktivitas');
    if (act) {
        var rows = [].slice.call(act.querySelectorAll('.nrow[data-date]'));
        var q = document.getElementById('actQ'), from = document.getElementById('actFrom'), to = document.getElementById('actTo');
        var empty = document.getElementById('actEmpty'), count = document.getElementById('actCount');

        var apply = function () {
            var word = q.value.trim().toLowerCase(), f = from.value, t = to.value, shown = 0;
            rows.forEach(function (r) {
                var d = r.getAttribute('data-date');
                var ok = (!word || r.getAttribute('data-text').indexOf(word) >= 0) && (!f || d >= f) && (!t || d <= t);
                r.hidden = !ok;
                if (ok) { shown++; }
            });
            empty.hidden = shown > 0 || !rows.length;
            count.textContent = rows.length ? shown + ' dari ' + rows.length : '';
        };

        [q, from, to].forEach(function (i) { i.addEventListener('input', apply); });
        document.getElementById('actReset').addEventListener('click', function () { q.value = from.value = to.value = ''; apply(); });
        apply();
    }
})();
