<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
$units = [];
if ($db) {
    $units = $db->query("SELECT id, unit_name FROM device_units ORDER BY unit_name ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">📷 Scan QR Registrasi</h2>
        <p class="text-gray-600 mb-4">Arahkan kamera ke QR Code yang tampil di pojok kiri atas layar TV.</p>

        <!-- Scanner -->
        <div id="qr-reader" class="w-full rounded-lg overflow-hidden border mx-auto" style="max-width:360px"></div>

        <!-- Error kamera -->
        <div id="error-area" class="mt-6 hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-800 font-semibold">❌ Gagal</p>
                <p class="text-red-700 mt-1" id="error-message"></p>
            </div>
        </div>

        <!-- Form setelah scan -->
        <div id="form-area" class="mt-6 hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-blue-800 font-semibold">✅ QR Terbaca!</p>
                <p class="text-blue-700 mt-1">Device ID: <strong id="form-device-id"></strong></p>
            </div>

            <form id="device-form" class="mt-4 space-y-4">
                <input type="hidden" name="device_id" id="input-device-id">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Perangkat</label>
                    <input type="text" name="device_name" id="input-device-name" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: TV Kamar 201">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Kamar</label>
                    <input type="text" name="room_number" id="input-room-number" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: 201">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Unit Launcher</label>
                    <select name="unit_id" id="input-unit-id" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Unit --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['unit_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    💾 Simpan Perangkat
                </button>
            </form>

            <div id="form-feedback" class="mt-4 hidden"></div>
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
        showError('Gagal akses kamera: ' + err);
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
            document.getElementById('form-device-id').textContent = data.device_id;
            document.getElementById('input-device-id').value = data.device_id;
            document.getElementById('input-device-name').value = data.device_id;
            document.getElementById('form-area').classList.remove('hidden');
            document.getElementById('error-area').classList.add('hidden');
            document.getElementById('qr-reader').classList.add('hidden');
        } else {
            showError(data.message || 'Unknown error');
        }
    })
    .catch(err => {
        showError('Network error: ' + err.message);
    });
}

document.getElementById('device-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const data = {
        device_id: document.getElementById('input-device-id').value,
        device_name: document.getElementById('input-device-name').value,
        room_number: document.getElementById('input-room-number').value,
        unit_id: document.getElementById('input-unit-id').value
    };

    fetch('./api.php?action=updateDeviceInfo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        const fb = document.getElementById('form-feedback');
        fb.classList.remove('hidden');
        if (res.status === 'success') {
            fb.className = 'mt-4 bg-green-50 border border-green-200 rounded-lg p-4';
            fb.innerHTML = '<p class="text-green-800 font-semibold">✅ Perangkat berhasil disimpan!</p>';
            // Reset form
            document.getElementById('device-form').reset();
            // Siapkan scan lagi setelah 2 detik
            setTimeout(() => {
                fb.classList.add('hidden');
                document.getElementById('qr-reader').classList.remove('hidden');
                document.getElementById('form-area').classList.add('hidden');
                startScanner();
            }, 2000);
        } else {
            fb.className = 'mt-4 bg-red-50 border border-red-200 rounded-lg p-4';
            fb.innerHTML = '<p class="text-red-800 font-semibold">❌ Gagal: ' + (res.message || 'Unknown error') + '</p>';
        }
        btn.disabled = false;
        btn.textContent = '💾 Simpan Perangkat';
    })
    .catch(err => {
        const fb = document.getElementById('form-feedback');
        fb.classList.remove('hidden');
        fb.className = 'mt-4 bg-red-50 border border-red-200 rounded-lg p-4';
        fb.innerHTML = '<p class="text-red-800 font-semibold">❌ Error: ' + err.message + '</p>';
        btn.disabled = false;
        btn.textContent = '💾 Simpan Perangkat';
    });
});

function showError(msg) {
    document.getElementById('error-area').classList.remove('hidden');
    document.getElementById('error-message').textContent = msg;
}

document.addEventListener('DOMContentLoaded', startScanner);

window.addEventListener('beforeunload', () => {
    if (html5QrCode) html5QrCode.stop().catch(()=>{});
});
</script>
