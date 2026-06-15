<?php
// ======================================================
// PAGE: Check-In & Check-Out Tamu
// ======================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../checkout_clear_helper.php';
require_once __DIR__ . '/../api/adb_helper.php';

/**
 * Cek apakah IP perangkat bisa di-ping (artinya perangkat hidup/online).
 */
function is_device_reachable(string $ip, int $timeoutSeconds = 1): bool
{
  $os = strtoupper(substr(PHP_OS, 0, 3));
  if ($os === 'WIN') {
    // -n 1 = sekali ping, -w = timeout ms
    $cmd = sprintf('ping -n 1 -w %d %s', $timeoutSeconds * 1000, escapeshellarg($ip));
  } else {
    // -c 1 = sekali ping, -W = timeout detik
    $cmd = sprintf('ping -c 1 -W %d %s', $timeoutSeconds, escapeshellarg($ip));
  }

  exec($cmd, $output, $status);
  return $status === 0;
}

$db = init_db_connection();
if ($db === null) {
  echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
  return;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // --- Aksi Check-In ---
  if ($action === 'check_in') {
    $room_number = trim($_POST['room_number'] ?? '');
    $guest_name = trim($_POST['guest_name'] ?? 'Tamu Yth');

    if (empty($room_number)) {
      flash('error', 'Nomor kamar wajib diisi.');
    } else {
      try {
        // 1. Set semua tamu di kamar itu (jika ada) ke status checked_out
        $stmt_clear = $db->prepare("UPDATE guest_checkin SET status = 'checked_out' WHERE room_number = ? AND status = 'checked_in'");
        $stmt_clear->execute([$room_number]);

        // 2. Masukkan tamu baru
        $stmt_insert = $db->prepare("
                    INSERT INTO guest_checkin (room_number, guest_name, checkin_time, status)
                    VALUES (?, ?, NOW(), 'checked_in')
                ");
        $stmt_insert->execute([$room_number, $guest_name]);

        // 3. Cari perangkat di kamar ini
        $stmtDev = $db->prepare("SELECT id, device_ip FROM managed_devices WHERE room_number = ? AND device_ip IS NOT NULL AND device_ip != ''");
        $stmtDev->execute([$room_number]);
        $devices = $stmtDev->fetchAll(PDO::FETCH_ASSOC);

        foreach ($devices as $dev) {
          $ip = $dev['device_ip'];
          $id = (int)$dev['id'];

          // Default: tandai pending_start_launcher = 1
          $db->prepare("UPDATE managed_devices SET pending_start_launcher = 1 WHERE id = ?")->execute([$id]);

          // Coba ping sekali saat check-in; jika hidup, langsung kirim perintah start launcher dan clear pending
          if ($ip && is_device_reachable($ip)) {
            adbConnect($ip);
            adbStartLauncher($ip);
            adbDisconnect($ip);
            $db->prepare("UPDATE managed_devices SET pending_start_launcher = 0 WHERE id = ?")->execute([$id]);
          }
        }

        flash('success', "✅ Tamu '{$guest_name}' berhasil Check-In ke kamar {$room_number}.");

      } catch (PDOException $e) {
        flash('error', 'Database Error: ' . $e->getMessage());
      }
    }
    header('Location: ?page=checkin');
    exit;
  }

  // --- Aksi Check-Out ---
  if ($action === 'check_out') {
    $checkin_id = (int) ($_POST['checkin_id'] ?? 0);
    $room_number = trim($_POST['room_number'] ?? '');

    if ($checkin_id > 0 && !empty($room_number)) {
      try {
        $db->beginTransaction();

        // 1. Update status tamu
        $stmt_guest = $db->prepare("UPDATE guest_checkin SET status = 'checked_out', checkout_time = NOW() WHERE id = ?");
        $stmt_guest->execute([$checkin_id]);

        // 2. Hapus data pesanan dining dari kamar tsb
        $stmt_dining = $db->prepare("DELETE FROM hotel_orders WHERE room_number = ?");
        $stmt_dining->execute([$room_number]);

        // 3. Hapus data permintaan amenities dari kamar tsb
        $stmt_amenity = $db->prepare("DELETE FROM amenity_requests WHERE room_number = ?");
        $stmt_amenity->execute([$room_number]);

        $db->commit();

        // 4. AUTO CLEAR: Kirim perintah ADB clear ke TV di kamar ini
        try {
          $clearResult = clearTVDataByRoom($db, $room_number);
        } catch (Throwable $e) {
          $clearResult = ['status' => 'error', 'message' => 'ADB error (silent)'];
        }
        $clearMsg = ($clearResult['status'] ?? '') === 'success'
          ? " Data TV berhasil dibersihkan."
          : "";

        flash('success', "✅ Kamar {$room_number} berhasil Check-Out. Data pesanan telah dibersihkan.{$clearMsg}");

      } catch (PDOException $e) {
        $db->rollBack();
        flash('error', 'Database Error: ' . $e->getMessage());
      }
    }
    header('Location: ?page=checkin');
    exit;
  }
}


$stmt_devices = $db->query("
    SELECT 
        m.room_number, 
        g.id AS checkin_id, 
        g.guest_name, 
        g.checkin_time
    FROM managed_devices m
    LEFT JOIN guest_checkin g ON m.room_number = g.room_number AND g.status = 'checked_in'
    ORDER BY m.room_number ASC
");
$rooms = $stmt_devices->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Check-In / Check-Out</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

  <!-- Form Check-In -->
  <div class="lg:col-span-1 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Check-In Tamu Baru</h2>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="action" value="check_in">

      <div>
        <label class="block text-sm font-medium mb-1">Nomor Kamar</label>
        <input name="room_number" required placeholder="Contoh: 101"
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nama Tamu</label>
        <input name="guest_name" required placeholder="Contoh: Bapak Rizal"
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
      </div>

      <button type="submit" class="w-full bg-yellow-400 text-gray-900 py-2 font-semibold rounded hover:bg-yellow-500">
        Check-In Tamu
      </button>
    </form>
  </div>

  <!-- Daftar Kamar yang Sedang Check-In -->
  <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Status Kamar Saat Ini</h2>

    <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
      <table class="min-w-full border border-gray-200 text-sm">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="border px-3 py-2 text-left">Nomor Kamar</th>
            <th class="border px-3 py-2 text-left">Nama Tamu</th>
            <th class="border px-3 py-2 text-left">Waktu Check-In</th>
            <th class="border px-3 py-2 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rooms)): ?>
            <tr>
              <td colspan="4" class="text-center p-4 text-gray-500">Belum ada perangkat terdaftar di halaman 'Perangkat'.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rooms as $room): ?>
              <tr class="hover:bg-gray-50">
                <td class="border px-3 py-2 font-bold"><?= htmlspecialchars($room['room_number']) ?></td>

                <?php if ($room['checkin_id']): ?>
                  <!-- Status Terisi -->
                  <td class="border px-3 py-2 text-green-700"><?= htmlspecialchars($room['guest_name']) ?></td>
                  <td class="border px-3 py-2 text-gray-600"><?= htmlspecialchars($room['checkin_time']) ?></td>
                  <td class="border px-3 py-2 text-center">
                    <form method="POST"
                      onsubmit="return confirm('Yakin ingin Check-Out kamar <?= htmlspecialchars($room['room_number']) ?>? Semua data pesanan di kamar ini akan dihapus.')">
                      <input type="hidden" name="action" value="check_out">
                      <input type="hidden" name="checkin_id" value="<?= $room['checkin_id'] ?>">
                      <input type="hidden" name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>">
                      <button type="submit" class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600">
                        Check-Out
                      </button>
                    </form>
                  </td>
                <?php else: ?>
                  <!-- Status Kosong -->
                  <td class="border px-3 py-2 text-gray-400 italic">-- Kosong --</td>
                  <td class="border px-3 py-2 text-gray-400 italic">--</td>
                  <td class="border px-3 py-2 text-center">
                    <span class="text-xs text-gray-400">N/A</span>
                  </td>
                <?php endif; ?>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>