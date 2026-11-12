/* Kasir / POS: produk, keranjang, pajak, pembayaran. Harga final tetap dihitung ulang oleh server. */
(function () {
    var DATA = JSON.parse(document.getElementById('posData').textContent);
    var $ = function (id) { return document.getElementById(id); };
    var rp = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };
    var cart = [];
    var cat = '';

    // ---------- Produk ----------
    function cats() {
        var set = {};
        DATA.items.forEach(function (p) { if (p.cat) { set[p.cat] = 1; } });
        var html = '<button type="button" class="chip-btn on" data-cat="">Semua</button>';
        Object.keys(set).sort().forEach(function (c) { html += '<button type="button" class="chip-btn" data-cat="' + esc(c) + '">' + esc(c) + '</button>'; });
        $('posCats').innerHTML = html;
    }

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function grid() {
        var q = $('posSearch').value.toLowerCase();
        var html = '';
        DATA.items.forEach(function (p) {
            if (cat && p.cat !== cat) { return; }
            if (q && (p.name + ' ' + (p.sku || '') + ' ' + (p.barcode || '')).toLowerCase().indexOf(q) < 0) { return; }
            var out = p.uses_stock && p.stock <= 0;
            html += '<button type="button" class="pcard' + (out ? ' out' : '') + '" data-id="' + p.id + '"' + (out ? ' disabled' : '') + '>' +
                (p.img ? '<img src="' + esc(p.img) + '" alt="">' : '<span class="ph">' + esc(p.name.charAt(0)) + '</span>') +
                '<b>' + esc(p.name) + '</b><span class="pr">' + rp(p.price) + '</span>' +
                '<small>' + (p.uses_stock ? (out ? 'Stok habis' : 'Stok ' + p.stock) : label(p.type)) + '</small></button>';
        });
        $('posGrid').innerHTML = html || '<p class="muted center">Produk tidak ditemukan.</p>';
    }

    function label(t) { return { jasa: 'Jasa', sewa: 'Sewa', makanan_minuman: 'Makanan & Minuman', digital: 'Digital', paket: 'Paket' }[t] || ''; }

    function find(id) { return DATA.items.filter(function (p) { return p.id === id; })[0]; }

    // ---------- Keranjang ----------
    function add(p, v) {
        var key = p.id + ':' + (v ? v.id : 0);
        var line = cart.filter(function (l) { return l.key === key; })[0];
        if (line) { line.qty++; } else {
            cart.push({ key: key, id: p.id, vid: v ? v.id : '', name: p.name + (v ? ' (' + v.label + ')' : ''), price: p.price + (v ? v.extra : 0),
                qty: 1, disc: 0, note: '', max: p.uses_stock ? p.stock : null });
        }
        draw();
    }

    function pick(id) {
        var p = find(id);
        if (!p) { return; }
        if (p.vars && p.vars.length) {
            $('varTitle').textContent = 'Pilih variasi: ' + p.name;
            $('varList').innerHTML = p.vars.map(function (v, i) {
                return '<button type="button" class="btn ghost" data-v="' + i + '">' + esc(v.label) + (v.extra ? ' (+' + rp(v.extra) + ')' : '') + '</button>';
            }).join('');
            $('varModal').hidden = false;
            $('varModal')._p = p;
            return;
        }
        add(p, null);
    }

    function lineTotal(l) { return Math.max(0, l.price * l.qty - l.disc); }

    function totals() {
        var sub = 0;
        cart.forEach(function (l) { sub += lineTotal(l); });
        var disc = Math.min(parseFloat($('fDisc').value) || 0, sub);
        var base = sub - disc, tax = 0;
        DATA.taxes.forEach(function (t) {
            var part = base;
            if (t.products && t.products.length) {
                var sel = 0;
                cart.forEach(function (l) { if (t.products.map(String).indexOf(String(l.id)) >= 0) { sel += lineTotal(l); } });
                part = sub > 0 ? base * sel / sub : 0;
            }
            tax += Math.round(part * t.pct) / 100;
        });
        return { sub: sub, disc: disc, tax: tax, total: base + tax };
    }

    function draw() {
        var box = $('cartLines');
        if (!cart.length) {
            box.innerHTML = '<p class="muted center">Belum ada produk. Klik produk di sebelah kiri atau scan barcode.</p>';
        } else {
            box.innerHTML = cart.map(function (l, i) {
                return '<div class="cl" data-i="' + i + '"><div class="cl-top"><b>' + esc(l.name) + '</b><button type="button" class="x" data-act="del" aria-label="Hapus">&times;</button></div>' +
                    '<div class="cl-row"><div class="qty"><button type="button" data-act="minus">&minus;</button><input type="number" min="1" value="' + l.qty + '" data-f="qty"><button type="button" data-act="plus">+</button></div>' +
                    '<label>Harga<input type="number" min="0" step="any" value="' + l.price + '" data-f="price"' + (DATA.canPrice ? '' : ' readonly') + '></label>' +
                    '<label>Diskon<input type="number" min="0" step="any" value="' + l.disc + '" data-f="disc"></label>' +
                    '<b class="lt">' + rp(lineTotal(l)) + '</b></div>' +
                    '<input type="text" class="cl-note" placeholder="Catatan produk" value="' + esc(l.note) + '" data-f="note" maxlength="255"></div>';
            }).join('');
        }
        $('cartCount').textContent = cart.reduce(function (a, l) { return a + l.qty; }, 0);
        sums();
    }

    function sums() {
        var t = totals();
        $('tSub').textContent = rp(t.sub);
        $('tTax').textContent = rp(t.tax);
        $('tTotal').textContent = rp(t.total);
        payUi();
    }

    function isCash() { var o = $('fMethod').selectedOptions[0]; return o && o.dataset.type === 'tunai'; }

    function payUi() {
        var t = totals();
        var cash = isCash();
        $('wrapRecv').style.display = cash ? '' : 'none';
        $('wrapRef').style.display = cash ? 'none' : '';
        $('wrapProof').style.display = cash ? 'none' : '';
        var amt = parseFloat($('fAmount').value) || 0;
        var recv = parseFloat($('fRecv').value) || 0;
        $('tChange').textContent = rp(cash ? Math.max(0, recv - amt) : 0);
        $('tStatus').textContent = !cart.length ? '-' : (amt + 0.001 >= t.total ? 'Lunas' : (amt > 0 ? 'Sebagian (jadi piutang)' : 'Belum lunas (jadi piutang)'));
    }

    function fillAmount() {
        var t = totals();
        $('fAmount').value = t.total ? Math.round(t.total * 100) / 100 : '';
        if (isCash() && !$('fRecv').dataset.touched) { $('fRecv').value = $('fAmount').value; }
        payUi();
    }

    // ---------- Event ----------
    $('posCats').addEventListener('click', function (e) {
        var b = e.target.closest('[data-cat]'); if (!b) { return; }
        cat = b.dataset.cat;
        document.querySelectorAll('.chip-btn').forEach(function (x) { x.classList.toggle('on', x === b); });
        grid();
    });
    $('posSearch').addEventListener('input', grid);
    $('posGrid').addEventListener('click', function (e) { var b = e.target.closest('.pcard'); if (b) { pick(parseInt(b.dataset.id, 10)); fillAmount(); } });

    $('posScan').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') { return; }
        e.preventDefault();
        var code = this.value.trim(); if (!code) { return; }
        var hit = DATA.items.filter(function (p) { return p.barcode === code || p.sku === code; })[0];
        if (hit) { add(hit, null); fillAmount(); window.Dashboard && Dashboard.toast(hit.name + ' ditambahkan'); }
        else { window.Dashboard && Dashboard.toast('Barcode ' + code + ' tidak ditemukan'); }
        this.value = '';
    });

    $('varList').addEventListener('click', function (e) {
        var b = e.target.closest('[data-v]'); if (!b) { return; }
        var p = $('varModal')._p;
        add(p, p.vars[parseInt(b.dataset.v, 10)]);
        $('varModal').hidden = true; fillAmount();
    });
    document.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', function () { b.closest('.modal').hidden = true; }); });

    $('cartLines').addEventListener('click', function (e) {
        var act = e.target.closest('[data-act]'); if (!act) { return; }
        var i = parseInt(act.closest('.cl').dataset.i, 10), l = cart[i];
        if (act.dataset.act === 'del') { cart.splice(i, 1); }
        if (act.dataset.act === 'plus') { l.qty++; }
        if (act.dataset.act === 'minus') { l.qty = Math.max(1, l.qty - 1); }
        draw(); fillAmount();
    });
    $('cartLines').addEventListener('change', function (e) {
        var f = e.target.dataset.f; if (!f) { return; }
        var l = cart[parseInt(e.target.closest('.cl').dataset.i, 10)];
        l[f] = f === 'note' ? e.target.value : Math.max(f === 'qty' ? 1 : 0, parseFloat(e.target.value) || 0);
        draw(); fillAmount();
    });

    $('fDisc').addEventListener('input', function () { sums(); fillAmount(); });
    $('fMethod').addEventListener('change', function () { delete $('fRecv').dataset.touched; fillAmount(); });
    $('fAmount').addEventListener('input', function () { if (isCash() && !$('fRecv').dataset.touched) { $('fRecv').value = this.value; } payUi(); });
    $('fRecv').addEventListener('input', function () { this.dataset.touched = '1'; payUi(); });

    // ---------- Bayar ----------
    function err(msgs) {
        var e = $('posErr');
        e.hidden = !msgs.length;
        e.innerHTML = msgs.length ? '<ul>' + msgs.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>' : '';
        if (msgs.length) { e.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    }

    $('btnPay').addEventListener('click', function () {
        var btn = this;
        if (!cart.length) { return err(['Keranjang masih kosong.']); }
        var amt = $('fAmount').value;
        if (amt === '') { return err(['Nominal pembayaran wajib diisi.']); }
        if (isCash() && $('fRecv').value === '') { return err(['Uang diterima wajib diisi untuk pembayaran tunai.']); }

        var fd = new FormData();
        fd.append('branch_id', $('fBranch').value);
        fd.append('shift', $('fShift').value);
        fd.append('type', $('fType').value);
        if ($('fCustomer').value) { fd.append('customer_id', $('fCustomer').value); }
        fd.append('note', $('fNote').value);
        fd.append('discount', $('fDisc').value || 0);
        fd.append('payment_method', $('fMethod').value);
        fd.append('amount', amt);
        if (isCash()) { fd.append('received', $('fRecv').value); }
        fd.append('reference', $('fRef').value);
        fd.append('receipt_note', $('fReceiptNote').value);
        if ($('fProof').files[0]) { fd.append('proof', $('fProof').files[0]); }
        cart.forEach(function (l, i) {
            fd.append('items[' + i + '][product_id]', l.id);
            if (l.vid) { fd.append('items[' + i + '][variation_id]', l.vid); }
            fd.append('items[' + i + '][qty]', l.qty);
            fd.append('items[' + i + '][price]', l.price);
            fd.append('items[' + i + '][discount]', l.disc);
            fd.append('items[' + i + '][note]', l.note);
        });

        btn.disabled = true; err([]);
        fetch(DATA.url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (x) {
                btn.disabled = false;
                if (!x.ok) {
                    var m = [];
                    if (x.j.errors) { Object.keys(x.j.errors).forEach(function (k) { m = m.concat(x.j.errors[k]); }); } else { m.push(x.j.message || 'Gagal menyimpan transaksi.'); }
                    return err(m);
                }
                $('doneInfo').textContent = x.j.invoice + ' - Total ' + rp(x.j.total) + (x.j.change > 0 ? ' - Kembalian ' + rp(x.j.change) : '');
                $('doneStruk').href = x.j.receipt;
                $('doneModal').hidden = false;
            })
            .catch(function () { btn.disabled = false; err(['Koneksi bermasalah. Coba lagi.']); });
    });

    $('doneNew').addEventListener('click', function () { window.location.href = window.location.pathname; });

    cats(); grid(); draw();
    if (new URLSearchParams(location.search).get('scan')) { $('posScan').focus(); }
})();
