<?php
// pages/units.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if (!$db) {
  echo "<h2 style='color:red;text-align:center;margin-top:20vh;'>❌ Database tidak dapat terhubung.<br>Periksa file config.php</h2>";
  exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  try {
    if ($action === 'add' || $action === 'edit') {
      $id = (int)($_POST['id'] ?? 0);
      $unit_name = trim($_POST['unit_name'] ?? '');
      $launcher_script = trim($_POST['launcher_script'] ?? '');
      $restore_script = trim($_POST['restore_script'] ?? '');
      $clear_script = trim($_POST['clear_script'] ?? '');

      if ($unit_name === '' || $clear_script === '') {
        $error = 'Field Nama Unit dan Daftar Package Clear Data wajib diisi.';
      } else {
        if ($action === 'add') {
          $stmt = $db->prepare("INSERT INTO device_units (unit_name, launcher_script, restore_script, clear_script) VALUES (?, ?, ?, ?)");
          $stmt->execute([$unit_name, $launcher_script, $restore_script, $clear_script]);
          $success = 'Data Unit berhasil ditambahkan.';
        } else {
          $stmt = $db->prepare("UPDATE device_units SET unit_name = ?, launcher_script = ?, restore_script = ?, clear_script = ? WHERE id = ?");
          $stmt->execute([$unit_name, $launcher_script, $restore_script, $clear_script, $id]);
          $success = 'Data Unit berhasil diperbarui.';
        }
      }
    }

    if ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM device_units WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Data Unit berhasil dihapus.';
      }
    }
  } catch (Exception $e) {
    if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "not found") !== false) {
        $error = "Tabel 'device_units' tidak ditemukan. Pastikan Anda sudah menjalankan script SQL 'database_update.sql' di database Anda.";
    } else {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
  }
}

try {
  $stmt = $db->query("SELECT * FROM device_units ORDER BY id ASC");
  $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $units = [];
  if (empty($error)) {
    $error = "Gagal memuat data unit. Pastikan script SQL 'database_update.sql' sudah dijalankan. (Error: " . $e->getMessage() . ")";
  }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($units as $u) {
        if ($u['id'] === $edit_id) {
            $edit_data = $u;
            break;
        }
    }
}
?>

<div class="bg-gray-100 min-h-screen">
  <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Master Data Unit</h1>
    <p class="text-gray-500 mb-6">
      Kelola jenis Unit Perangkat dan script ADB yang akan dieksekusi saat proses Set Launcher atau Restore pada halaman perangkat.
    </p>

    <!-- ALERTS -->
    <?php if ($success): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- FORM TAMBAH / UBAH -->
    <div class="bg-gray-50 p-6 rounded-lg mb-8 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-700 mb-4"><?= $edit_data ? 'Ubah' : 'Tambah' ?> Data Unit</h2>
        <form method="POST" action="admin.php?page=units" class="grid grid-cols-1 gap-4">
          <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'add' ?>">
          <?php if($edit_data): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">
          <?php endif; ?>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit <span class="text-red-500">*</span></label>
            <input type="text" name="unit_name" value="<?= htmlspecialchars($edit_data['unit_name'] ?? '') ?>" placeholder="Contoh: TCL Smart TV" required
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-yellow-400 focus:border-yellow-400">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Script Set Launcher (ADB) <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea name="launcher_script" rows="8" placeholder="Masukkan perintah shell baris per baris...&#10;Contoh: shell cmd package set-home-activity com.takeoff.launcher/.MainActivity"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-yellow-400 focus:border-yellow-400"
                ><?= htmlspecialchars($edit_data['launcher_script'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Script Restore (ADB) <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea name="restore_script" rows="8" placeholder="Masukkan perintah shell baris per baris...&#10;Contoh: shell pm enable com.google.android.tvlauncher"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-yellow-400 focus:border-yellow-400"
                ><?= htmlspecialchars($edit_data['restore_script'] ?? '') ?></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Daftar Package Clear Data (satu per baris) <span class="text-red-500">*</span></label>
                <textarea name="clear_script" rows="8" placeholder="Isi dengan nama package per baris...&#10;Contoh: com.google.android.youtube" required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm focus:ring-yellow-400 focus:border-yellow-400"
                ><?= htmlspecialchars($edit_data['clear_script'] ?? '') ?></textarea>
              </div>
          </div>

          <div class="flex items-center gap-4 mt-2">
            <button type="submit"
              class="px-6 py-2 bg-yellow-400 text-gray-900 font-semibold rounded-lg shadow-md hover:bg-yellow-500 focus:ring-2 focus:ring-yellow-300">
              Simpan
            </button>
            <?php if ($edit_data): ?>
                <a href="admin.php?page=units" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-400">Batal</a>
            <?php endif; ?>
          </div>
        </form>
    </div>

    <!-- TABEL UNIT -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden" style="table-layout: fixed;">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 50px;">ID</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 18%;">Nama Unit</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 22%;">Set Launcher</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 22%;">Restore</th>
            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 22%;">Clear Data</th>
            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider" style="width: 12%;">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($units)): ?>
            <tr>
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data unit.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($units as $u): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700 align-top"><?= htmlspecialchars($u['id']) ?></td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-800 align-top"><?= htmlspecialchars($u['unit_name']) ?></td>
                <td class="px-4 py-3 align-top">
                  <div class="text-xs font-mono text-gray-600 whitespace-pre-wrap  break-all"  title="<?= htmlspecialchars($u['launcher_script']) ?>"><?= htmlspecialchars($u['launcher_script']) ?></div>
                </td>
                <td class="px-4 py-3 align-top">
                  <div class="text-xs font-mono text-gray-600 whitespace-pre-wrap  break-all"  title="<?= htmlspecialchars($u['restore_script']) ?>"><?= htmlspecialchars($u['restore_script']) ?></div>
                </td>
                <td class="px-4 py-3 align-top">
                  <div class="text-xs font-mono text-gray-600 whitespace-pre-wrap  break-all"  title="<?= htmlspecialchars($u['clear_script'] ?? '') ?>"><?= htmlspecialchars($u['clear_script'] ?? '') ?></div>
                </td>
                <td class="px-4 py-3 align-top">
                  <div class="flex justify-center items-center gap-2">
                    <a href="admin.php?page=units&edit=<?= htmlspecialchars($u['id']) ?>" class="px-3 py-1 bg-blue-100 text-blue-600 text-xs font-medium rounded hover:bg-blue-200">
                      Ubah
                    </a>
                    <form method="POST" action="admin.php?page=units" class="inline" onsubmit="return confirm('Hapus data unit ini?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                      <!-- <button type="submit" class="px-3 py-1 bg-red-100 text-red-600 text-xs font-medium rounded hover:bg-red-200">
                        Hapus
                      </button> -->
                    </form>
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
