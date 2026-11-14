/*
 * Input uang otomatis berformat ribuan: ketik 150000 -> tampil 150.000 (koma = desimal).
 * Pakai atribut data-money pada <input type="text">. Saat form dikirim, nilainya diubah
 * kembali ke angka biasa (150000) supaya server menerima angka murni.
 */
(function () {
    var D = window.Dashboard = window.Dashboard || {};

    // teks ketikan -> teks berformat (titik = ribuan, koma = desimal maks 2 digit)
    function format(text) {
        var s = String(text == null ? '' : text).replace(/[^\d,]/g, '');
        var i = s.indexOf(','), dec = null;
        if (i >= 0) { dec = s.slice(i + 1).replace(/,/g, '').slice(0, 2); s = s.slice(0, i); }
        s = s.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return dec === null ? s : s + ',' + dec;
    }

    // teks berformat -> angka (1.500,5 -> 1500.5)
    D.moneyNum = function (v) {
        var n = parseFloat(String(v == null ? '' : v).replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    };
    // angka -> teks berformat (1500.5 -> 1.500,5)
    D.moneyText = function (n) {
        if (n === '' || n == null || isNaN(n)) { return ''; }
        var r = Math.round(Number(n) * 100) / 100, p = String(r).split('.');
        return format(p[0] + (p[1] ? ',' + p[1] : ''));
    };
    D.setMoney = function (el, n) { el.value = D.moneyText(n); };

    // Format saat mengetik; posisi kursor dijaga berdasarkan jumlah angka di sebelah kirinya
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el.matches || !el.matches('input[data-money]')) { return; }
        var before = el.value, pos = el.selectionStart == null ? before.length : el.selectionStart;
        var digits = before.slice(0, pos).replace(/[^\d,]/g, '').length;
        var out = format(before);
        if (out === before) { return; }
        el.value = out;
        var p = 0, seen = 0;
        while (p < out.length && seen < digits) { if (/[\d,]/.test(out.charAt(p))) { seen++; } p++; }
        try { el.setSelectionRange(p, p); } catch (x) { /* abaikan */ }
    }, true);

    // Nilai awal dari server ("15000.00") ditampilkan berformat
    function init(scope) {
        (scope || document).querySelectorAll('input[data-money]:not([data-money-ready])').forEach(function (el) {
            el.setAttribute('data-money-ready', '1');
            if (el.value !== '') { el.value = D.moneyText(parseFloat(el.value)); }
        });
    }
    D.moneyInit = init;
    init();

    // Saat kirim form: ubah ke angka murni, lalu kembalikan tampilannya
    document.addEventListener('submit', function (e) {
        var inputs = e.target.querySelectorAll ? e.target.querySelectorAll('input[data-money]') : [];
        var saved = [];
        inputs.forEach(function (el) {
            saved.push([el, el.value]);
            el.value = el.value === '' ? '' : String(D.moneyNum(el.value));
        });
        if (saved.length) { setTimeout(function () { saved.forEach(function (s) { s[0].value = s[1]; }); }, 0); }
    }, true);
})();
