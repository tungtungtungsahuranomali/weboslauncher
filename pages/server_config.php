<?php
// ======================================================
// PAGE: Konfigurasi Server Remote
// Ubah URL server ke semua TV sekaligus dari admin panel
// ======================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if (!$db) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
    return;
}

// Pastikan tabel system_settings ada
try {
    $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    // Tabel sudah ada, lanjut
}

// Pastikan tabel managed_devices ada
try {
    $db->exec("CREATE TABLE IF NOT EXISTS managed_devices (
        device_id VARCHAR(100) PRIMARY KEY,
        device_name VARCHAR(100),
        room_number VARCHAR(20),
        device_ip VARCHAR(50),
        last_seen DATETIME,
        pending_clear TINYINT(1) DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
}

$success = '';
$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_wa_config') {
        $waEnabled = isset($_POST['wa_enabled']) ? '1' : '0';
        $waToken = trim($_POST['wa_fonnte_token'] ?? '');
        $waRecipientDining = str_replace(' ', '', trim($_POST['wa_recipient_dining'] ?? ''));
        $waRecipientAmenities = str_replace(' ', '', trim($_POST['wa_recipient_amenities'] ?? ''));
        $waRecipientTransport = str_replace(' ', '', trim($_POST['wa_recipient_transportation'] ?? ''));

        try {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmt->execute(['wa_gateway_enabled', $waEnabled]);
            $stmt->execute(['wa_fonnte_token', $waToken]);
            $stmt->execute(['wa_recipient_dining', $waRecipientDining]);
            $stmt->execute(['wa_recipient_amenities', $waRecipientAmenities]);
            $stmt->execute(['wa_recipient_transportation', $waRecipientTransport]);

            $success = '✅ Pengaturan WhatsApp Gateway berhasil disimpan.';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }

    if ($action === 'test_wa') {
        require_once __DIR__ . '/../wa_helper.php';
        $testMsg = "✅ *Test WhatsApp Gateway*\n\nKoneksi berhasil dari panel admin hotel.\nWaktu: " . date('d M Y, H:i:s');
        $sent = sendWhatsAppNotification($db, $testMsg);
        if ($sent) {
            $success = '✅ Pesan test berhasil dikirim! Cek WhatsApp penerima.';
        } else {
            $error = '❌ Gagal mengirim pesan test. Periksa token dan nomor penerima.';
        }
    }

    // --- Scheduled Clear Data Handler ---
    if ($action === 'update_scheduled_clear') {
        $clearEnabled = isset($_POST['scheduled_clear_enabled']) ? '1' : '0';
        $clearTime = trim($_POST['scheduled_clear_time'] ?? '04:00');

        try {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmt->execute(['scheduled_clear_enabled', $clearEnabled]);
            $stmt->execute(['scheduled_clear_time', $clearTime]);

            $success = '✅ Jadwal clear data berhasil disimpan. Waktu: ' . $clearTime;
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Ambil WhatsApp config
$waEnabled = '0';
$waToken = '';
$waRecipientDining = '';
$waRecipientAmenities = '';
$waRecipientTransport = '';
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('wa_gateway_enabled', 'wa_fonnte_token', 'wa_recipient_dining', 'wa_recipient_amenities', 'wa_recipient_transportation')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'wa_gateway_enabled')
            $waEnabled = $row['setting_value'];
        if ($row['setting_key'] === 'wa_fonnte_token')
            $waToken = $row['setting_value'];
        if ($row['setting_key'] === 'wa_recipient_dining')
            $waRecipientDining = $row['setting_value'];
        if ($row['setting_key'] === 'wa_recipient_amenities')
            $waRecipientAmenities = $row['setting_value'];
        if ($row['setting_key'] === 'wa_recipient_transportation')
            $waRecipientTransport = $row['setting_value'];
    }
} catch (Exception $e) {
}

// Ambil Scheduled Clear config
$scheduledClearEnabled = '0';
$scheduledClearTime = '04:00';
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('scheduled_clear_enabled', 'scheduled_clear_time')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'scheduled_clear_enabled')
            $scheduledClearEnabled = $row['setting_value'];
        if ($row['setting_key'] === 'scheduled_clear_time')
            $scheduledClearTime = $row['setting_value'];
    }
} catch (Exception $e) {
}
?>



<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
        <?= $success ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>



<!-- ==================== Scheduled Clear Data Section ==================== -->
<div class="mt-0">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">🕐 Jadwal Clear Data Otomatis</h1>
    <p class="text-gray-500 mb-6">Bersihkan data semua perangkat TV secara otomatis pada jam tertentu setiap hari.
        Berguna untuk memastikan tidak ada sisa data tamu.</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Form Pengaturan -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Pengaturan Jadwal</h3>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_scheduled_clear">

                <!-- Toggle Aktif -->
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status Jadwal</label>
                        <p class="text-xs text-gray-400">Aktifkan untuk clear data otomatis setiap hari</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="scheduled_clear_enabled" class="sr-only peer"
                            <?= $scheduledClearEnabled === '1' ? 'checked' : '' ?>>
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                        </div>
                    </label>
                </div>

                <!-- Waktu Clear -->
                <div>
                    <label class="block text-sm font-medium mb-1">Waktu Clear Harian</label>
                    <input name="scheduled_clear_time" type="time" value="<?= htmlspecialchars($scheduledClearTime) ?>"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 text-lg">
                    <p class="text-xs text-gray-400 mt-1">Semua TV akan menjalankan clear data pada jam ini setiap hari
                    </p>
                </div>

                <button type="submit"
                    class="w-full bg-yellow-400 text-gray-900 py-2 font-semibold rounded hover:bg-yellow-500 transition">
                    💾 Simpan Jadwal
                </button>
            </form>
        </div>

        <!-- Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi</h3>

            <div class="space-y-3">
                <div
                    class="flex items-center gap-3 p-3 rounded-lg <?= $scheduledClearEnabled === '1' ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' ?>">
                    <span class="text-2xl"><?= $scheduledClearEnabled === '1' ? '🟢' : '🔴' ?></span>
                    <div>
                        <p class="text-sm font-medium">
                            <?= $scheduledClearEnabled === '1' ? 'Jadwal Aktif' : 'Jadwal Nonaktif' ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?= $scheduledClearEnabled === '1' ? 'Clear data setiap hari pukul ' . htmlspecialchars($scheduledClearTime) : 'Clear data otomatis tidak dijalankan' ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-700">
                    <strong>💡 Cara kerja:</strong>
                </p>
                <ul class="text-sm text-yellow-600 mt-2 space-y-1 list-disc list-inside">
                    <li>Setiap TV mengecek jadwal dari server secara berkala</li>
                    <li>Saat waktu yang ditentukan tiba, clear data dijalankan otomatis</li>
                    <li>Clear data hanya dijalankan <strong>1x per hari</strong></li>
                    <li>Data yang dibersihkan: YouTube, Netflix, Spotify, dll</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ==================== WhatsApp Gateway Section ==================== -->
<div class="mt-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">📱 WhatsApp Gateway (Fonnte)</h2>
    <p class="text-gray-500 mb-6">Terima notifikasi otomatis via WhatsApp saat ada pesanan dining atau permintaan
        amenities dari tamu.</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Form Pengaturan WA -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Pengaturan WhatsApp</h3>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_wa_config">

                <!-- Toggle Aktif -->
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status Gateway</label>
                        <p class="text-xs text-gray-400">Aktifkan untuk menerima notifikasi WA</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="wa_enabled" class="sr-only peer" <?= $waEnabled === '1' ? 'checked' : '' ?>>
                        <div
                            class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500">
                        </div>
                    </label>
                </div>

                <!-- Token Fonnte -->
                <div>
                    <label class="block text-sm font-medium mb-1">Token Fonnte</label>
                    <input name="wa_fonnte_token" type="text" value="<?= htmlspecialchars($waToken) ?>"
                        placeholder="Paste token dari dashboard fonnte.com"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-400 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Dapatkan token di <a href="https://fonnte.com" target="_blank"
                            class="text-blue-500 underline">fonnte.com</a></p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">🍽 Penerima Notifikasi Dining</label>
                    <input name="wa_recipient_dining" type="text" value="<?= htmlspecialchars($waRecipientDining) ?>"
                        placeholder="62811324288,62812876546"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-400 text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">Nomor penerima pesanan dining. Pisahkan dengan koma untuk
                        banyak nomor.</p>
                </div>

                <!-- Penerima Amenities -->
                <div>
                    <label class="block text-sm font-medium mb-1">🧴 Penerima Notifikasi Amenities</label>
                    <input name="wa_recipient_amenities" type="text"
                        value="<?= htmlspecialchars($waRecipientAmenities) ?>" placeholder="6281176543788"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-400 text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">Nomor penerima permintaan amenities. Pisahkan dengan koma
                        untuk banyak nomor.</p>
                </div>

                <!-- Penerima Transportation -->
                <div>
                    <label class="block text-sm font-medium mb-1">🚐 Penerima Notifikasi Transportasi</label>
                    <input name="wa_recipient_transportation" type="text"
                        value="<?= htmlspecialchars($waRecipientTransport) ?>" placeholder="6281176543788"
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-green-400 text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">Nomor penerima permintaan transportasi. Pisahkan dengan koma
                        untuk banyak nomor.</p>
                </div>

                <button type="submit"
                    class="w-full bg-green-500 text-white py-2 font-semibold rounded hover:bg-green-600 transition">
                    💾 Simpan Pengaturan
                </button>
            </form>

            <!-- Tombol Test -->
            <?php if ($waEnabled === '1' && !empty($waToken) && (!empty($waRecipientDining) || !empty($waRecipientAmenities))): ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="action" value="test_wa">
                    <button type="submit"
                        class="w-full bg-blue-50 text-blue-600 py-2 rounded hover:bg-blue-100 text-sm font-medium transition">
                        📤 Kirim Pesan Test
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Info & Preview -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi</h3>

            <div class="space-y-3">
                <div
                    class="flex items-center gap-3 p-3 rounded-lg <?= $waEnabled === '1' ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' ?>">
                    <span class="text-2xl"><?= $waEnabled === '1' ? '🟢' : '🔴' ?></span>
                    <div>
                        <p class="text-sm font-medium"><?= $waEnabled === '1' ? 'Gateway Aktif' : 'Gateway Nonaktif' ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?= $waEnabled === '1' ? 'Notifikasi WA akan terkirim otomatis' : 'Notifikasi WA tidak terkirim' ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($waRecipientDining)): ?>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700"><strong>🍽 Penerima Dining:</strong><br>
                            <span class="font-mono text-xs"><?= htmlspecialchars($waRecipientDining) ?></span>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($waRecipientAmenities)): ?>
                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm text-purple-700"><strong>🧴 Penerima Amenities:</strong><br>
                            <span class="font-mono text-xs"><?= htmlspecialchars($waRecipientAmenities) ?></span>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-sm text-yellow-700">
                    <strong>💡 Catatan:</strong>
                </p>
                <ul class="text-sm text-yellow-600 mt-2 space-y-1 list-disc list-inside">
                    <li>Pisahkan nomor dengan <strong>koma (,)</strong> untuk banyak penerima</li>
                    <li>Format nomor: <strong>628xxx</strong> (tanpa + atau spasi)</li>
                    <li>Spasi akan otomatis dihilangkan saat disimpan</li>
                </ul>
            </div>

            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-xs font-medium text-gray-500 mb-2">📋 Contoh Pesan:</p>
                <pre class="text-xs text-gray-600 whitespace-pre-wrap">🍽️ *Pesanan Dining Baru*

👤 Tamu: John Doe
🚪 Kamar: 101

📋 Detail Pesanan:
  • Nasi Goreng x2
  • Es Teh x2

💰 Total: Rp 150.000</pre>
            </div>
        </div>
    </div>
</div>

