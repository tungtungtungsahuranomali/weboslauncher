<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📷 Scan QR Registrasi</h2>
        <p class="text-gray-600 mb-4">Arahkan kamera ke QR Code yang tampil di pojok kiri atas layar TV.</p>

        <div id="qr-reader" class="w-full rounded-lg overflow-hidden border mx-auto" style="max-width:360px"></div>

        <div id="result-area" class="mt-6 hidden">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800 font-semibold">✅ Device Terdaftar!</p>
                <p class="text-green-700 mt-1" id="result-device-id"></p>
            </div>
        </div>

        <div id="error-area" class="mt-6 hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-800 font-semibold">❌ Gagal</p>
                <p class="text-red-700 mt-1" id="error-message"></p>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;

function startScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    ).catch(err => {
        document.getElementById('error-area').classList.remove('hidden');
        document.getElementById('error-message').textContent = 'Gagal akses kamera: ' + err;
    });
}

function onScanSuccess(decodedText) {
    html5QrCode.stop().catch(()=>{});
    const code = decodedText.trim().toUpperCase();

    fetch('./api.php?action=registerDeviceByCode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ device_id: code })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('result-area').classList.remove('hidden');
            document.getElementById('result-device-id').textContent = 'ID: ' + data.device_id;
            document.getElementById('error-area').classList.add('hidden');
        } else {
            document.getElementById('error-area').classList.remove('hidden');
            document.getElementById('error-message').textContent = data.message || 'Unknown error';
        }
    })
    .catch(err => {
        document.getElementById('error-area').classList.remove('hidden');
        document.getElementById('error-message').textContent = 'Network error: ' + err.message;
    });
}

document.addEventListener('DOMContentLoaded', startScanner);

// Cleanup when leaving page
window.addEventListener('beforeunload', () => {
    if (html5QrCode) html5QrCode.stop().catch(()=>{});
});
</script>
