/* Mode gelap / terang: tombol cepat di menu pengaturan + ikuti perangkat */
(function () {
    var root = document.documentElement;

    function isDark() { return root.getAttribute('data-theme') === 'dark'; }

    function apply(pref) {
        var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
        root.setAttribute('data-theme', dark ? 'dark' : 'light');
        root.setAttribute('data-theme-pref', pref);
        window.APP_THEME = pref;
        label();
    }
    window.applyTheme = apply;

    function label() {
        var l = document.querySelector('#themeQuick .th-label');
        if (l) { l.textContent = isDark() ? 'Mode Terang' : 'Mode Gelap'; }
    }

    var btn = document.getElementById('themeQuick');
    if (btn) {
        btn.addEventListener('click', function () {
            var next = isDark() ? 'light' : 'dark';
            apply(next);
            if (window.THEME_URL) {
                var fd = new FormData();
                fd.append('theme', next);
                fd.append('_token', window.CSRF || '');
                fetch(window.THEME_URL, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).catch(function () {});
            }
        });
    }
    label();

    if (window.matchMedia) {
        var mq = matchMedia('(prefers-color-scheme: dark)');
        var onChange = function () { if (window.APP_THEME === 'auto') { apply('auto'); } };
        if (mq.addEventListener) { mq.addEventListener('change', onChange); }
    }
})();
