/* Sapaan dan tanggal di dashboard mengikuti jam sebenarnya (zona waktu toko), tanpa perlu reload. */
(function () {
    var box = document.getElementById('greet');
    var g = document.getElementById('greetText');
    var d = document.getElementById('dateText');
    if (!box || !g || !d) { return; }

    var tz = box.getAttribute('data-tz') || 'Asia/Jakarta';
    var DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function now() {
        var p = {};
        try {
            new Intl.DateTimeFormat('en-CA', { timeZone: tz, year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', hourCycle: 'h23' })
                .formatToParts(new Date()).forEach(function (x) { p[x.type] = x.value; });
        } catch (e) { var n = new Date(); p = { year: n.getFullYear(), month: n.getMonth() + 1, day: n.getDate(), hour: n.getHours() }; }
        var y = +p.year, m = +p.month, dd = +p.day, h = (+p.hour) % 24;
        return { y: y, m: m, d: dd, h: h, dow: new Date(Date.UTC(y, m - 1, dd)).getUTCDay() };
    }

    function tick() {
        var t = now();
        g.textContent = t.h >= 4 && t.h < 11 ? 'Selamat Pagi' : t.h >= 11 && t.h < 15 ? 'Selamat Siang' : t.h >= 15 && t.h < 18 ? 'Selamat Sore' : 'Selamat Malam';
        d.textContent = DAYS[t.dow] + ', ' + t.d + ' ' + MONTHS[t.m - 1] + ' ' + t.y;
    }

    tick();
    setInterval(tick, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { tick(); } });
})();
