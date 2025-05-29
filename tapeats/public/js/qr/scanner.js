window.addEventListener('load', function () {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
            const html5QrCodeScanner = new Html5QrcodeScanner(
                "reader",
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: /Mobi/i.test(window.navigator.userAgent) ? 16 / 9 : 9 / 16,
                    facingMode: "environment"
                }
            );

            function onScanSuccess(decodedText, decodedResult) {
                fetch('/store-qr-result', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        table_number: decodedText.split('/').pop() // ambil bagian terakhir dari URL
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert("QR berhasil, tapi tidak dapat redirect.");
                    }

                    html5QrCodeScanner.clear();
                    stream.getTracks().forEach(track => track.stop());
                })
                .catch(err => {
                    alert("QR gagal diproses.");
                    console.error(err);
                });
            }

            html5QrCodeScanner.render(onScanSuccess);
        })
        .catch(function (err) {
            alert("Izin kamera ditolak.");
            console.error(err);
        });
});
