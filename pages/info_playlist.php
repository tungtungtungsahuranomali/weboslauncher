<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if ($db === null) { echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ DB Error.</div>"; return; }

// Auto-create table
$db->exec("CREATE TABLE IF NOT EXISTS info_playlist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('image','video') NOT NULL DEFAULT 'image',
  url TEXT NOT NULL,
  duration INT DEFAULT 10,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$uploadDir = __DIR__ . '/../uploads/info_playlist/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $type = $_POST['type'] ?? 'image';
        $duration = (int)($_POST['duration'] ?? 10);
        $url = $_POST['url'] ?? '';

        if (!empty($_FILES['file']['name'])) {
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fn = 'info_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fn)) {
                $url = 'uploads/info_playlist/' . $fn;
            }
        }

        if ($url) {
            $maxSort = $db->query("SELECT MAX(sort_order) FROM info_playlist")->fetchColumn() ?: 0;
            $db->prepare("INSERT INTO info_playlist (type, url, duration, sort_order) VALUES (?, ?, ?, ?)")->execute([$type, $url, $duration, $maxSort + 1]);
        }
        header('Location: ?page=info_playlist');
        exit;
    }

    if (isset($_POST['toggle_active'])) {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE info_playlist SET is_active = NOT is_active WHERE id=?")->execute([$id]);
        header('Location: ?page=info_playlist');
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $row = $db->prepare("SELECT url FROM info_playlist WHERE id=?")->execute([$id]);
        // Delete file
        $stmt = $db->prepare("SELECT url FROM info_playlist WHERE id=?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && !empty($old['url']) && strpos($old['url'], 'http') !== 0) {
            $f = __DIR__ . '/../' . $old['url'];
            if (is_file($f)) @unlink($f);
        }
        $db->prepare("DELETE FROM info_playlist WHERE id=?")->execute([$id]);
        header('Location: ?page=info_playlist');
        exit;
    }
}

$items = $db->query("SELECT * FROM info_playlist ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">📋 Info Playlist</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">➕ Tambah Item</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-3">
      <input type="hidden" name="add_item" value="1">
      <div>
        <label class="block text-sm font-medium mb-1">Tipe</label>
        <select name="type" class="w-full border rounded px-3 py-2">
          <option value="image">📷 Gambar</option>
          <option value="video">🎬 Video</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Upload File</label>
        <input type="file" name="file" class="w-full border rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Atau URL (jika tidak upload)</label>
        <input type="url" name="url" placeholder="https://..." class="w-full border rounded px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Durasi (detik, untuk gambar)</label>
        <input type="number" name="duration" value="10" min="3" class="w-full border rounded px-3 py-2">
      </div>
      <button type="submit" class="w-full bg-yellow-400 py-2 rounded font-semibold hover:bg-yellow-500">Simpan</button>
    </form>
  </div>

  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">📋 Daftar Playlist</h2>
    <?php if (empty($items)): ?>
      <p class="text-gray-500 text-sm">Belum ada item.</p>
    <?php else: ?>
      <div class="space-y-2 max-h-[500px] overflow-auto">
        <?php foreach ($items as $item): ?>
          <div class="flex items-center gap-3 border-b pb-2">
            <span class="text-xs text-gray-400 w-6"><?= $item['sort_order'] ?></span>
            <?php if ($item['type'] === 'image'): ?>
              <img src="<?= htmlspecialchars(get_full_url($item['url'])) ?>" class="w-12 h-12 object-cover rounded border flex-shrink-0">
            <?php else: ?>
              <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-xs flex-shrink-0">🎬</div>
            <?php endif; ?>
            <div class="flex-1 min-w-0 text-sm">
              <p class="truncate"><?= basename($item['url']) ?></p>
              <p class="text-xs text-gray-400"><?= $item['type'] ?> · <?= $item['duration'] ?>s <?= $item['is_active'] ? '🟢' : '🔴' ?></p>
            </div>
            <div class="flex gap-1 flex-shrink-0">
              <form method="POST">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <input type="hidden" name="toggle_active" value="1">
                <button class="px-2 py-1 text-xs rounded <?= $item['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>"><?= $item['is_active'] ? 'On' : 'Off' ?></button>
              </form>
              <form method="POST" onsubmit="return confirm('Hapus?')">
                <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                <button class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded">🗑</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
