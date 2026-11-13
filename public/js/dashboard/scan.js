/*
 * Scan barcode pakai kamera. Dipakai halaman Kasir / POS lewat Dashboard.scanCamera().
 * Memakai BarcodeDetector bawaan browser bila ada, kalau tidak ada memakai ZXing (file lokal, tanpa internet).
 * Kamera hanya jalan di HTTPS atau localhost / 127.0.0.1.
 */
(function () {
    var D = window.Dashboard = window.Dashboard || {};
    var FORMATS = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'qr_code'];
    var stream = null, timer = null, running = false, last = '', lastAt = 0;

    function loadZXing(done) {
        if (window.ZXing) { done(); return; }
        var s = document.createElement('script');
        s.src = (window.ZXING_URL || '/js/vendor/zxing.min.js');
        s.onload = done;
        s.onerror = function () { done('Pustaka scan gagal dimuat.'); };
        document.head.appendChild(s);
    }

    function makeDetector(done) {
        if ('BarcodeDetector' in window) {
            var bd = new window.BarcodeDetector({ formats: FORMATS });
            done(null, function (video) { return bd.detect(video).then(function (r) { return r.length ? r[0].rawValue : null; }); });
            return;
        }
        loadZXing(function (err) {
            if (err) { done(err); return; }
            var hints = new Map();
            hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                ZXing.BarcodeFormat.EAN_13, ZXing.BarcodeFormat.EAN_8, ZXing.BarcodeFormat.UPC_A,
                ZXing.BarcodeFormat.UPC_E, ZXing.BarcodeFormat.CODE_128, ZXing.BarcodeFormat.CODE_39, ZXing.BarcodeFormat.QR_CODE
            ]);
            hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
            var reader = new ZXing.MultiFormatReader();
            reader.setHints(hints);
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d', { willReadFrequently: true });
            done(null, function (video) {
                var w = video.videoWidth, h = video.videoHeight;
                if (!w || !h) { return Promise.resolve(null); }
                canvas.width = w; canvas.height = h;
                ctx.drawImage(video, 0, 0, w, h);
                try {
                    var bmp = new ZXing.BinaryBitmap(new ZXing.HybridBinarizer(new ZXing.HTMLCanvasElementLuminanceSource(canvas)));
                    return Promise.resolve(reader.decode(bmp).getText());
                } catch (e) { return Promise.resolve(null); }   // belum ada barcode di gambar
            });
        });
    }

    D.stopCamera = function () {
        running = false;
        clearTimeout(timer);
        if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    };

    /* video: elemen <video>; onCode(kode): dipanggil tiap barcode terbaca; onState(teks): pesan status */
    D.scanCamera = function (video, onCode, onState) {
        D.stopCamera();
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            onState('Kamera hanya bisa dipakai lewat HTTPS atau localhost.');
            return;
        }
        onState('Membuka kamera...');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false })
            .then(function (s) {
                stream = s;
                video.srcObject = s;
                return video.play();
            })
            .then(function () {
                makeDetector(function (err, detect) {
                    if (err) { onState(err); D.stopCamera(); return; }
                    running = true;
                    onState('Arahkan kamera ke barcode');
                    (function loop() {
                        if (!running) { return; }
                        detect(video).then(function (code) {
                            var t = Date.now();
                            if (code && (code !== last || t - lastAt > 2000)) {
                                last = code; lastAt = t;
                                if (navigator.vibrate) { navigator.vibrate(60); }
                                onCode(code);
                            }
                        }).catch(function () {}).then(function () { if (running) { timer = setTimeout(loop, 180); } });
                    })();
                });
            })
            .catch(function (e) {
                D.stopCamera();
                onState(e && e.name === 'NotAllowedError' ? 'Izin kamera ditolak. Izinkan kamera di pengaturan browser.' :
                        e && e.name === 'NotFoundError' ? 'Kamera tidak ditemukan di perangkat ini.' : 'Kamera tidak bisa dibuka.');
            });
    };
})();
