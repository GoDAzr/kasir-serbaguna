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

    // Konfirmasi sebelum hapus / batal / keluar: kotak dialog Ya / Tidak.
    // Atribut di <form>: data-confirm (pertanyaan), data-confirm-title, data-confirm-yes, data-confirm-no
    function ask(form, submitter) {
        var m = document.getElementById('confirmModal');
        if (!m) {
            m = document.createElement('div');
            m.className = 'modal'; m.id = 'confirmModal'; m.hidden = true;
            m.innerHTML = '<div class="modal-box center" role="alertdialog" aria-modal="true" aria-labelledby="cfTitle">' +
                '<div class="cf-ico">?</div><h3 id="cfTitle"></h3><p id="cfMsg" class="muted" style="margin:0"></p>' +
                '<div class="actions c"><button type="button" class="btn ghost" id="cfNo"></button><button type="button" class="btn primary" id="cfYes"></button></div></div>';
            document.body.appendChild(m);
        }
        var yes = m.querySelector('#cfYes'), no = m.querySelector('#cfNo');
        m.querySelector('#cfTitle').textContent = form.getAttribute('data-confirm-title') || 'Konfirmasi';
        m.querySelector('#cfMsg').textContent = form.getAttribute('data-confirm');
        yes.textContent = form.getAttribute('data-confirm-yes') || 'Ya';
        no.textContent = form.getAttribute('data-confirm-no') || 'Tidak';
        yes.className = 'btn ' + (form.hasAttribute('data-confirm-danger') ? 'danger' : 'primary');

        function close() { m.hidden = true; document.removeEventListener('keydown', onKey); yes.onclick = no.onclick = m.onclick = null; }
        function onKey(e) { if (e.key === 'Escape') { close(); } }
        yes.onclick = function () {
            close();
            form.setAttribute('data-cf-ok', '1');
            if (form.requestSubmit) { form.requestSubmit(submitter || undefined); } else { form.submit(); }
        };
        no.onclick = close;
        m.onclick = function (e) { if (e.target === m) { close(); } };
        document.addEventListener('keydown', onKey);
        m.hidden = false;
        no.focus();   // fokus awal di "Tidak" supaya tidak terpencet tidak sengaja
    }
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f.getAttribute || !f.getAttribute('data-confirm')) { return; }
        if (f.getAttribute('data-cf-ok')) { f.removeAttribute('data-cf-ok'); return; }   // sudah disetujui
        e.preventDefault();
        ask(f, e.submitter);
    });

    // Pesan sukses tampil sebentar sebagai toast
    var ok = document.querySelector('.flash.ok');
    if (ok && window.Dashboard && Dashboard.toast) { Dashboard.toast(ok.textContent.trim()); }
})();
