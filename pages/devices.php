<?php


if (session_status() === PHP_SESSION_NONE)
  session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if (!$db) {
  echo "<h2 style='color:red;text-align:center;margin-top:20vh;'>❌ Database tidak dapat terhubung.<br>Periksa file config.php</h2>";
  exit;
}

$success = '';
$error = '';
$adb_log = '';

// Fungsi helper: cari path ADB
function get_adb_path() {
  $adb = 'adb';
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $winPaths = ['C:\\adb\\platform-tools\\adb.exe', 'C:\\Android\\platform-tools\\adb.exe'];
    foreach ($winPaths as $p) {
      if (file_exists($p)) { $adb = "\"$p\""; break; }
    }
  } else {
    $linuxPaths = ['/usr/bin/adb', '/usr/local/bin/adb', '/opt/adb/platform-tools/adb'];
    foreach ($linuxPaths as $p) {
      if (file_exists($p)) { $adb = $p; break; }
    }
  }
  return $adb;
}

// Fungsi helper: jalankan ADB command dan catat log
function run_adb($adb, $device, $cmd) {
  $full = "$adb -s $device $cmd 2>&1";
  $output = trim(shell_exec($full) ?? '');
  return $output;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  try {
    if ($action === 'add_device') {
      $device_id = trim($_POST['device_id'] ?? '');
      $device_name = trim($_POST['device_name'] ?? '');
      $room_number = trim($_POST['room_number'] ?? '');
      $device_ip = trim($_POST['device_ip'] ?? '');
      $unit_id = (int) ($_POST['unit_id'] ?? 0);

      if ($device_id === '' || $device_name === '' || $room_number === '' || $device_ip === '' || $unit_id <= 0) {
        $error = 'Semua field wajib diisi (Device ID, Nama, Nomor Kamar, IP, Unit).';
      } else {
        $stmt = $db->prepare("
          INSERT INTO managed_devices (device_id, device_name, room_number, device_ip, unit_id, registered_at, last_seen)
          VALUES (?, ?, ?, ?, ?, NOW(), NOW())
          ON DUPLICATE KEY UPDATE
            device_name = VALUES(device_name),
            room_number = VALUES(room_number),
            device_ip = VALUES(device_ip),
            unit_id = VALUES(unit_id),
            registered_at = NOW(),
            last_seen = NOW()
        ");
        $stmt->execute([$device_id, $device_name, $room_number, $device_ip, $unit_id]);
        $success = 'Perangkat berhasil disimpan.';
      }
    }

    if ($action === 'update_ip') {
      $id = (int) ($_POST['id'] ?? 0);
      $device_ip = trim($_POST['device_ip'] ?? '');
      if ($id > 0) {
        $stmt = $db->prepare("UPDATE managed_devices SET device_ip = ? WHERE id = ?");
        $stmt->execute([$device_ip ?: null, $id]);
        $success = 'IP perangkat berhasil diperbarui.';
      }
    }

    if ($action === 'update_unit') {
      $id = (int) ($_POST['id'] ?? 0);
      $unit_id = (int) ($_POST['unit_id'] ?? 0);
      if ($id > 0) {
        $stmt = $db->prepare("UPDATE managed_devices SET unit_id = ? WHERE id = ?");
        $stmt->execute([$unit_id ?: null, $id]);
        $success = 'Unit perangkat berhasil diperbarui.';
      }
    }

    if ($action === 'update_device_info') {
      $id = (int) ($_POST['id'] ?? 0);
      $device_name = trim($_POST['device_name'] ?? '');
      $room_number = trim($_POST['room_number'] ?? '');
      if ($id > 0 && $device_name !== '' && $room_number !== '') {
        $stmt = $db->prepare("UPDATE managed_devices SET device_name = ?, room_number = ? WHERE id = ?");
        $stmt->execute([$device_name, $room_number, $id]);
        $success = 'Data perangkat berhasil diperbarui.';
      } else {
        $error = 'Nama dan nomor kamar wajib diisi.';
      }
    }

    if ($action === 'delete_device') {
      $id = (int) ($_POST['id'] ?? 0);
      if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM managed_devices WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Perangkat berhasil dihapus.';
      }
    }

    // === SET TAKEOFF LAUNCHER VIA ADB ===
    if ($action === 'set_launcher') {
      $device_db_id = (int) ($_POST['device_db_id'] ?? 0);
      if ($device_db_id <= 0) {
        $error = 'Perangkat tidak valid.';
      } else {
        // Ambil IP dan script launcher berdasarkan unit
        $stmt = $db->prepare("
          SELECT md.device_ip, du.launcher_script, du.unit_name
          FROM managed_devices md
          LEFT JOIN device_units du ON md.unit_id = du.id
          WHERE md.id = ?
        ");
        $stmt->execute([$device_db_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['device_ip'])) {
          $error = 'IP perangkat belum diisi.';
        } elseif (empty($row['launcher_script'])) {
          $error = 'Unit / script launcher belum diset untuk perangkat ini.';
        } else {
          $device_ip = $row['device_ip'];
          $adb = get_adb_path();
          $device = "$device_ip:5555";
          $logs = [];

          $logs[] = "▶ Connecting to $device...";
          $connectOut = trim(shell_exec("$adb connect $device 2>&1") ?? '');
          $logs[] = $connectOut;
          usleep(1000000); // 1 detik

          $logs[] = "\n▶ Menjalankan script Set Launcher untuk unit: " . ($row['unit_name'] ?? '-');
          $scriptLines = preg_split('/\r\n|\r|\n/', $row['launcher_script']);
          foreach ($scriptLines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $logs[] = "  $line";
            $out = run_adb($adb, $device, $line);
            if (!empty($out)) $logs[] = "    → $out";
          }

          shell_exec("$adb disconnect $device 2>&1");
          $logs[] = "\n✅ Selesai Set Launcher!";

          $adb_log = implode("\n", $logs);
          $success = "Launcher berhasil diset untuk $device_ip";
        }
      }
    }

    // === RESTORE LAUNCHER VIA ADB (BERDASARKAN UNIT) ===
    if ($action === 'restore_launcher') {
      $device_db_id = (int) ($_POST['device_db_id'] ?? 0);
      if ($device_db_id <= 0) {
        $error = 'Perangkat tidak valid.';
      } else {
        // Ambil IP dan script restore berdasarkan unit
        $stmt = $db->prepare("
          SELECT md.device_ip, du.restore_script, du.unit_name
          FROM managed_devices md
          LEFT JOIN device_units du ON md.unit_id = du.id
          WHERE md.id = ?
        ");
        $stmt->execute([$device_db_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['device_ip'])) {
          $error = 'IP perangkat belum diisi.';
        } elseif (empty($row['restore_script'])) {
          $error = 'Unit / script restore belum diset untuk perangkat ini.';
        } else {
          $device_ip = $row['device_ip'];
          $adb = get_adb_path();
          $device = "$device_ip:5555";
          $logs = [];

          $logs[] = "▶ Connecting to $device...";
          $connectOut = trim(shell_exec("$adb connect $device 2>&1") ?? '');
          $logs[] = $connectOut;
          usleep(1000000); // 1 detik

          $logs[] = "\n▶ Menjalankan script Restore untuk unit: " . ($row['unit_name'] ?? '-');
          $scriptLines = preg_split('/\r\n|\r|\n/', $row['restore_script']);
          foreach ($scriptLines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $logs[] = "  $line";
            $out = run_adb($adb, $device, $line);
            if (!empty($out)) $logs[] = "    → $out";
          }

          shell_exec("$adb disconnect $device 2>&1");
          $logs[] = "\n✅ Selesai Restore!";

          $adb_log = implode("\n", $logs);
          $success = "Launcher berhasil di-restore untuk $device_ip";
        }
      }
    }

  } catch (Exception $e) {
    $error = 'Terjadi kesalahan: ' . $e->getMessage();
  }
}


try {
  $stmt = $db->query("SELECT md.*, du.unit_name, du.launcher_script, du.restore_script FROM managed_devices md LEFT JOIN device_units du ON md.unit_id = du.id ORDER BY md.room_number ASC");
  $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $devices = [];
  error_log("Fetch Devices Error: " . $e->getMessage());
}

// Ambil daftar unit untuk dropdown
try {
  $stmtUnits = $db->query("SELECT * FROM device_units ORDER BY unit_name ASC");
  $units = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $units = [];
  error_log("Fetch Units Error: " . $e->getMessage());
}
?>

<div class="bg-gray-100 min-h-screen">
  <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Perangkat</h1>
    <p class="text-gray-500 mb-6">
      Tambahkan, ubah, atau hapus perangkat TV yang terdaftar di sistem hotel. Setiap perangkat wajib memiliki Device ID
      unik dan nomor kamar.
    </p>

    <!-- ALERTS -->
    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" style="white-space:pre-line;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- ADB LOG OUTPUT -->
    <?php if ($adb_log): ?>
      <div class="bg-gray-900 text-green-400 p-4 rounded-lg mb-4 font-mono text-xs overflow-x-auto" style="white-space:pre-line; max-height:300px; overflow-y:auto;">
        <?= htmlspecialchars($adb_log) ?>
      </div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-6">
      <input type="hidden" name="action" value="add_device">

      <div>
        <label class="block text-sm font-medium text-gray-700">Device ID <span class="text-red-500">*</span></label>
        <input type="text" name="device_id" placeholder="Contoh: TV-101-A" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-400 focus:border-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Nama Perangkat <span class="text-red-500">*</span></label>
        <input type="text" name="device_name" placeholder="Contoh: Smart TV Lobby" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-400 focus:border-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Nomor Kamar <span class="text-red-500">*</span></label>
        <input type="text" name="room_number" placeholder="Contoh: 101" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-400 focus:border-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Device IP <span class="text-red-500">*</span></label>
        <input type="text" name="device_ip" placeholder="Contoh: 10.16.96.147" required
          pattern="^(\d{1,3}\.){3}\d{1,3}$" title="Format IP: xxx.xxx.xxx.xxx"
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-400 focus:border-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Unit <span class="text-red-500">*</span></label>
        <select name="unit_id" required
          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-yellow-400 focus:border-yellow-400">
          <option value="">-- Pilih Unit --</option>
          <?php foreach ($units as $u): ?>
            <option value="<?= htmlspecialchars($u['id']) ?>">
              <?= htmlspecialchars($u['unit_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex items-center justify-start md:col-span-5">
        <button type="submit"
          class="px-6 py-2 bg-yellow-400 text-gray-900 font-semibold rounded-lg shadow-md hover:bg-yellow-500 focus:ring-2 focus:ring-yellow-300">
          Simpan
        </button>
      </div>
    </form>

    <hr class="my-8">

    <!-- TABEL PERANGKAT -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Device ID</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Perangkat</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kamar</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Device IP</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($devices)): ?>
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada perangkat terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($devices as $d): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($d['id']) ?></td>
                <td class="px-4 py-3 text-sm font-mono text-gray-800"><?= htmlspecialchars($d['device_id']) ?></td>
                <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($d['device_name']) ?></td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-800"><?= htmlspecialchars($d['room_number']) ?></td>
                <td class="px-4 py-3 text-sm">
                  <form method="POST" class="flex items-center gap-2" id="ipForm<?= $d['id'] ?>">
                    <input type="hidden" name="action" value="update_ip">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($d['id']) ?>">
                    <input type="text" name="device_ip" value="<?= htmlspecialchars($d['device_ip'] ?? '') ?>"
                      placeholder="belum diisi"
                      class="w-36 px-2 py-1 text-sm font-mono border border-gray-300 rounded focus:ring-yellow-400 focus:border-yellow-400"
                      onchange="this.form.submit()">
                  </form>
                </td>
                <td class="px-4 py-3 text-sm">
                  <form method="POST" class="flex items-center gap-2" id="unitForm<?= $d['id'] ?>">
                    <input type="hidden" name="action" value="update_unit">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($d['id']) ?>">
                    <select name="unit_id"
                      class="w-40 px-2 py-1 text-sm border border-gray-300 rounded bg-white focus:ring-yellow-400 focus:border-yellow-400"
                      onchange="this.form.submit()">
                      <option value="">-- Pilih Unit --</option>
                      <?php foreach ($units as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>" <?= (int)($d['unit_id'] ?? 0) === (int)$u['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($u['unit_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td class="px-2 py-3">
                  <div class="flex flex-col items-center gap-1" style="min-width:130px;">
                    <?php if (!empty($d['device_ip']) && !empty($d['unit_id'])): ?>
                      <?php if (!empty($d['launcher_script'])): ?>
                        <form method="POST" class="w-full" onsubmit="return confirm('Jalankan Set Launcher untuk perangkat di <?= htmlspecialchars($d['device_ip']) ?> (Unit: <?= htmlspecialchars($d['unit_name'] ?? '-') ?>)?')">
                          <input type="hidden" name="action" value="set_launcher">
                          <input type="hidden" name="device_db_id" value="<?= htmlspecialchars($d['id']) ?>">
                          <button type="submit" class="w-full px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600">
                            📺 Set Launcher
                          </button>
                        </form>
                      <?php endif; ?>

                      <?php if (!empty($d['restore_script'])): ?>
                        <form method="POST" class="w-full" onsubmit="return confirm('Jalankan Restore Launcher untuk perangkat di <?= htmlspecialchars($d['device_ip']) ?> (Unit: <?= htmlspecialchars($d['unit_name'] ?? '-') ?>)?')">
                          <input type="hidden" name="action" value="restore_launcher">
                          <input type="hidden" name="device_db_id" value="<?= htmlspecialchars($d['id']) ?>">
                          <button type="submit" class="w-full px-3 py-1.5 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600">
                            🔄 Restore
                          </button>
                        </form>
                      <?php endif; ?>

                    <?php elseif (!empty($d['device_ip'])): ?>
                      <span class="text-xs text-red-500 text-center">Pilih Unit terlebih dahulu sebelum menjalankan script.</span>
                    <?php endif; ?>
                    <form method="POST" class="w-full" onsubmit="return confirm('Hapus perangkat ini?')">
                      <input type="hidden" name="action" value="delete_device">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($d['id']) ?>">
                      <button type="submit" class="w-full px-3 py-1.5 bg-red-50 text-red-500 text-xs font-medium rounded hover:bg-red-100 border border-red-200">
                        🗑 Hapus
                      </button>
                    </form>
                    <button type="button" onclick="editDevice(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['device_name'])) ?>', '<?= htmlspecialchars(addslashes($d['room_number'])) ?>')"
                      class="w-full px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-medium rounded hover:bg-blue-100 border border-blue-200">
                      ✏️ Edit
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Device Modal -->
<div id="editDeviceModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display:none">
  <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
    <h3 class="font-bold text-lg mb-4">✏️ Edit Perangkat</h3>
    <form method="POST" class="space-y-3">
      <input type="hidden" name="action" value="update_device_info">
      <input type="hidden" name="id" id="edit-id">

      <div>
        <label class="block text-sm font-medium mb-1">Nama Perangkat</label>
        <input type="text" name="device_name" id="edit-name" required
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Nomor Kamar</label>
        <input type="text" name="room_number" id="edit-room" required
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
      </div>

      <div class="flex gap-2">
        <button type="submit" class="flex-1 bg-yellow-400 text-gray-900 py-2 rounded font-semibold hover:bg-yellow-500">Simpan</button>
        <button type="button" onclick="closeEditDevice()" class="px-4 bg-gray-200 py-2 rounded hover:bg-gray-300">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function editDevice(id, name, room) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-room').value = room;
  document.getElementById('editDeviceModal').style.display = 'flex';
}
function closeEditDevice() {
  document.getElementById('editDeviceModal').style.display = 'none';
}
</script>