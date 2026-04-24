<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if ($db === null) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
    return;
}

$success = '';
$error = '';

// Pastikan folder upload tersedia
$uploadDir = __DIR__ . '/../uploads/facilities/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Tambah fasilitas
    if ($action === 'add_facility') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imagePath = '';

        if ($name === '') {
            $error = 'Nama fasilitas wajib diisi.';
        } else {
          
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['image']['tmp_name'];
                $origName = basename($_FILES['image']['name']);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed)) {
                    $error = 'Hanya file JPG, PNG, atau WEBP yang diperbolehkan.';
                } else {
                    $newName = 'facility_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $dest = $uploadDir . $newName;

                    if (move_uploaded_file($tmpName, $dest)) {
                        $imagePath = 'uploads/facilities/' . $newName;
                    } else {
                        $error = 'Gagal mengunggah gambar.';
                    }
                }
            }
         
            elseif (!empty($_POST['image_url'])) {
                $imagePath = trim($_POST['image_url']);
            }

          
            if ($error === '') {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO hotel_facilities (name, description, icon_path, is_active)
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute([$name, $description, $imagePath]);
                    $success = '✅ Fasilitas berhasil disimpan.';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    if ($action === 'delete_facility') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("SELECT icon_path FROM hotel_facilities WHERE id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Jika file lokal, hapus dari server
            if ($row && strpos($row['icon_path'], 'http') !== 0) {
                $file = __DIR__ . '/../' . $row['icon_path'];
                if (is_file($file)) @unlink($file);
            }

            $stmt = $db->prepare("DELETE FROM hotel_facilities WHERE id=?");
            $stmt->execute([$id]);
            $success = '🗑️ Fasilitas berhasil dihapus.';
        }
    }
}


$facilities = [];
try {
    $facilities = $db->query("SELECT * FROM hotel_facilities ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Fasilitas Hotel</h1>

<?php if ($success): ?>
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Tambah Fasilitas Baru</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="action" value="add_facility">

      <div>
        <label class="block text-sm font-medium mb-1">Nama Fasilitas</label>
        <input name="name" required placeholder="Kolam Renang, Gym, Spa..."
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Deskripsi</label>
        <textarea name="description" rows="3" placeholder="Keterangan jam buka, lokasi, dll..."
          class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400"></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Upload Gambar</label>
          <input type="file" name="image" accept="image/*"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                   file:rounded-md file:border-0 file:text-sm file:font-semibold
                   file:bg-yellow-400 file:text-gray-900 hover:file:bg-yellow-500">
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Atau URL Gambar</label>
          <input type="url" name="image_url" placeholder="https://..."
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
        </div>
      </div>

      <button type="submit" class="w-full bg-yellow-400 text-gray-900 py-2 font-semibold rounded hover:bg-yellow-500">
        Simpan Fasilitas
      </button>
    </form>
  </div>

  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Daftar Fasilitas</h2>

    <?php if (empty($facilities)): ?>
      <p class="text-gray-500 text-sm">Belum ada fasilitas yang ditambahkan.</p>
    <?php else: ?>
      <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
        <?php foreach ($facilities as $f): ?>
          <div class="flex gap-4 border-b pb-3">
            <?php if (!empty($f['icon_path'])): ?>
              <img src="<?= htmlspecialchars(get_full_url($f['icon_path'])) ?>"
                   class="w-20 h-20 object-cover rounded border">
            <?php else: ?>
              <div class="w-20 h-20 bg-gray-200 flex items-center justify-center text-xs text-gray-500">
                No Image
              </div>
            <?php endif; ?>

            <div class="flex-1">
              <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($f['name']) ?></h3>
              <?php if (!empty($f['description'])): ?>
                <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($f['description'])) ?></p>
              <?php endif; ?>
            </div>

            <form method="POST" onsubmit="return confirm('Hapus fasilitas ini?')">
              <input type="hidden" name="action" value="delete_facility">
              <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
              <button type="submit"
                      class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600">
                Hapus
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>