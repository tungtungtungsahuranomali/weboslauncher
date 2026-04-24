<?php


if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Inisialisasi koneksi database
$db = init_db_connection();
if (!$db) {
    echo "<h2 style='color:red;text-align:center;margin-top:20vh;'>❌ Database tidak dapat terhubung.<br>Periksa file config.php</h2>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_launcher') {
    $stmt_check = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'launcher_enabled'");
    $stmt_check->execute();
    $current_status = (int) $stmt_check->fetchColumn();
    $new_status = $current_status ? 0 : 1; // Toggle (0 jadi 1, 1 jadi 0)

    $stmt_update = $db->prepare("
        INSERT INTO global_settings (setting_key, setting_value)
        VALUES ('launcher_enabled', ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt_update->execute([$new_status]);

    flash('success', 'Status Launcher berhasil diubah menjadi ' . ($new_status ? 'AKTIF' : 'NONAKTIF'));
    header('Location: admin.php?page=dashboard');
    exit;
}

try {
    $stmt = $db->query("SELECT COUNT(*) AS total_devices FROM managed_devices");
    $total_devices = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $total_devices = 0;
    error_log("Dashboard Device Count Error: " . $e->getMessage());
}

// Ambil status launcher
try {
    $stmt2 = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'launcher_enabled'");
    $stmt2->execute();
    $is_launcher_enabled = (bool) $stmt2->fetchColumn();
} catch (Exception $e) {
    $is_launcher_enabled = false;
    error_log("Dashboard Launcher Status Error: " . $e->getMessage());
}

// Ambil running text (marquee)
try {
    $stmt3 = $db->query("SELECT content FROM system_marquee WHERE id = 1");
    $marquee = $stmt3->fetchColumn() ?: "Selamat datang di TakeOff IPTV Hotel System.";
} catch (Exception $e) {
    $marquee = "Selamat datang di TakeOff IPTV Hotel System.";
}
?>

<div class="bg-gray-100 min-h-screen">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- STATUS LAUNCHER -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Status Launcher</h2>
      <p class="text-gray-500 mb-4">Kondisi global aplikasi TV di seluruh perangkat.</p>
      <div class="flex items-center justify-between">
        <span class="text-lg font-bold <?= $is_launcher_enabled ? 'text-green-600' : 'text-red-500' ?>">
          <?= $is_launcher_enabled ? 'Aktif' : 'Nonaktif' ?>
        </span>
        <!-- INI BAGIAN TOMBOL YANG DIPERBAIKI -->
        <form method="POST" action="admin.php?page=dashboard">
          <input type="hidden" name="action" value="toggle_launcher">
          <button type="submit" class="px-4 py-2 <?= $is_launcher_enabled ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?> text-white text-sm rounded-lg font-semibold">
            <?= $is_launcher_enabled ? 'Matikan' : 'Aktifkan' ?>
          </button>
        </form>
      </div>
    </div>

    <!-- TOTAL PERANGKAT -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Perangkat Terdaftar</h2>
      <p class="text-gray-500 mb-4">Total perangkat aktif dalam sistem ini.</p>
      <p class="text-5xl font-bold text-gray-900"><?= $total_devices ?></p>
    </div>

    <!-- RUNNING TEXT -->
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Teks Berjalan (Marquee)</h2>
      <p class="text-gray-500 mb-4">Pesan utama yang tampil di bawah layar TV.</p>
      <p class="italic text-gray-800 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
        “<?= htmlspecialchars($marquee) ?>”
      </p>
    </div>
  </div>

  <!-- Bagian bawah: Info Sistem -->
  <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Informasi Server</h2>
      <ul class="text-gray-600 text-sm space-y-1">
        <li>📦 <b>Database:</b> <?= DB_NAME ?></li>
        <li>👤 <b>Admin Login:</b> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Unknown') ?></li>
        <li>🌐 <b>Server IP:</b> <?= $_SERVER['SERVER_ADDR'] ?? 'N/A' ?></li>
        <li>🕒 <b>Waktu Server:</b> <?= date('d M Y, H:i:s') ?></li>
      </ul>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Panduan Cepat</h2>
      <ul class="text-gray-600 text-sm space-y-1">
        <li>✅ Gunakan menu <b>Devices</b> untuk menambah TV atau kamar.</li>
        <li>🎬 Atur aplikasi tampil di menu <b>App Control</b>.</li>
        <li>📝 Edit teks bawah layar di <b>Running Text</b>.</li>
        <li>🍽️ Tambah menu makanan di <b>Dining Room</b>.</li>
      </ul>
    </div>
  </div>
</div>