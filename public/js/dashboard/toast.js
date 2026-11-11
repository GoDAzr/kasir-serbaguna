/* Menu yang belum dibuat -> tampilkan pemberitahuan kecil di bawah layar */
(function () {
    var D = window.Dashboard = window.Dashboard || {};
    var toast = document.getElementById('toast');
    var timer;

    D.toast = function (text) {
        toast.textContent = text;
        toast.classList.add('show');
        clearTimeout(timer);
        timer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
    };

    document.querySelectorAll('[data-soon]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            D.toast('Menu ' + el.getAttribute('data-soon') + ' dibuat di tahap berikutnya');
            if (el.closest('.sidebar') && D.afterMenuClick) { D.afterMenuClick(); }
        });
    });
})();
