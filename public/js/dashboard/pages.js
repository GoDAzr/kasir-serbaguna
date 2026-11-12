/* Perilaku halaman dalam: tampil/sembunyi field, baris berulang, konfirmasi, notifikasi flash. */
(function () {
    // Field yang hanya muncul untuk nilai tertentu (mis. Jenis = Sewa -> Lama sewa)
    function applyShow(scope) {
        scope.querySelectorAll('[data-show]').forEach(function (el) {
            var rule; try { rule = JSON.parse(el.getAttribute('data-show')); } catch (e) { return; }
            var ok = Object.keys(rule).every(function (k) {
                var src = scope.querySelector('[name="' + k + '"]');
                if (!src) { return true; }
                var want = [].concat(rule[k]).map(String);
                return want.indexOf(String(src.value)) >= 0;
            });
            el.hidden = !ok;
            el.querySelectorAll('input,select,textarea').forEach(function (i) { i.disabled = !ok; });
        });
    }
    document.querySelectorAll('form.form').forEach(function (form) {
        applyShow(form);
        form.addEventListener('change', function () { applyShow(form); });
    });

    // Baris berulang (produk dalam paket, rincian pembelian, dll)
    document.querySelectorAll('[data-rep]').forEach(function (rep) {
        var rows = rep.querySelector('[data-rows]');
        var tpl = rep.querySelector('[data-tpl]');
        var n = rows.children.length + 100;
        rep.querySelector('[data-add]').addEventListener('click', function () {
            var html = tpl.innerHTML.replace(/__i__/g, n++);
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            rows.appendChild(wrap.firstElementChild);
        });
        rep.addEventListener('click', function (e) {
            if (!e.target.closest('[data-del]')) { return; }
            var row = e.target.closest('[data-row]');
            if (rows.children.length > 1) { row.remove(); }
            else { row.querySelectorAll('input,select,textarea').forEach(function (i) { i.value = ''; }); }
        });
    });

    // Konfirmasi sebelum hapus / batal
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) { e.preventDefault(); }
    });

    // Pesan sukses tampil sebentar sebagai toast
    var ok = document.querySelector('.flash.ok');
    if (ok && window.Dashboard && Dashboard.toast) { Dashboard.toast(ok.textContent.trim()); }
})();
