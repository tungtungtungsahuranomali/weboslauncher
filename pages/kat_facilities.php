<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$db = init_db_connection();

$uploadDir = __DIR__ . '/../uploads/kat_facilities/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$success = '';
$error = '';

// ====== PROSES POST ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // TAMBAH KATEGORI
    if ($action === 'add_kat') {
        $nama = trim($_POST['nm_kat_facilities'] ?? '');
        $fotoPath = '';

        if ($nama === '') {
            $error = 'Nama kategori wajib diisi.';
        } else {
            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed)) {
                    $error = 'Hanya file JPG, PNG, atau WEBP yang diperbolehkan.';
                } else {
                    $newName = 'kat_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $newName)) {
                        $fotoPath = 'uploads/kat_facilities/' . $newName;
                    } else {
                        $error = 'Gagal mengunggah foto.';
                    }
                }
            }

            if ($error === '') {
                try {
                    $stmt = $db->prepare("INSERT INTO kat_facilities (nm_kat_facilities, foto_kat_facilities) VALUES (?, ?)");
                    $stmt->execute([$nama, $fotoPath]);
                    $success = '✅ Kategori berhasil ditambahkan.';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    // EDIT KATEGORI
    if ($action === 'edit_kat') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nm_kat_facilities'] ?? '');

        if ($id <= 0 || $nama === '') {
            $error = 'Data tidak valid.';
        } else {
            $fotoPath = null; // null = tidak update foto

            if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed)) {
                    $error = 'Hanya file JPG, PNG, atau WEBP yang diperbolehkan.';
                } else {
                    $newName = 'kat_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $newName)) {
                        // Hapus foto lama
                        $old = $db->prepare("SELECT foto_kat_facilities FROM kat_facilities WHERE id_kat_facilities=?");
                        $old->execute([$id]);
                        $oldRow = $old->fetch();
                        if ($oldRow && !empty($oldRow['foto_kat_facilities']) && strpos($oldRow['foto_kat_facilities'], 'http') !== 0) {
                            $oldFile = __DIR__ . '/../' . $oldRow['foto_kat_facilities'];
                            if (is_file($oldFile)) @unlink($oldFile);
                        }
                        $fotoPath = 'uploads/kat_facilities/' . $newName;
                    } else {
                        $error = 'Gagal mengunggah foto.';
                    }
                }
            }

            if ($error === '') {
                try {
                    if ($fotoPath !== null) {
                        $stmt = $db->prepare("UPDATE kat_facilities SET nm_kat_facilities=?, foto_kat_facilities=? WHERE id_kat_facilities=?");
                        $stmt->execute([$nama, $fotoPath, $id]);
                    } else {
                        $stmt = $db->prepare("UPDATE kat_facilities SET nm_kat_facilities=? WHERE id_kat_facilities=?");
                        $stmt->execute([$nama, $id]);
                    }
                    $success = '✅ Kategori berhasil diperbarui.';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    // HAPUS KATEGORI
    if ($action === 'delete_kat') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            // Hapus foto dari server
            $stmt = $db->prepare("SELECT foto_kat_facilities FROM kat_facilities WHERE id_kat_facilities=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['foto_kat_facilities']) && strpos($row['foto_kat_facilities'], 'http') !== 0) {
                $file = __DIR__ . '/../' . $row['foto_kat_facilities'];
                if (is_file($file)) @unlink($file);
            }
            // Set null pada facilities yang pakai kategori ini
            $db->prepare("UPDATE hotel_facilities SET id_kat_facilities=NULL WHERE id_kat_facilities=?")->execute([$id]);
            // Hapus kategori
            $db->prepare("DELETE FROM kat_facilities WHERE id_kat_facilities=?")->execute([$id]);
            $success = '🗑️ Kategori berhasil dihapus.';
        }
    }
}

// ====== AMBIL DATA ======
$categories = [];
try {
    $categories = $db->query("SELECT * FROM kat_facilities ORDER BY id_kat_facilities DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Hitung jumlah facilities per kategori
$facCounts = [];
try {
    $countStmt = $db->query("SELECT id_kat_facilities, COUNT(*) as cnt FROM hotel_facilities WHERE id_kat_facilities IS NOT NULL GROUP BY id_kat_facilities");
    while ($row = $countStmt->fetch()) {
        $facCounts[$row['id_kat_facilities']] = $row['cnt'];
    }
} catch (PDOException $e) {}

$editKat = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($categories as $c) {
        if ($c['id_kat_facilities'] == $editId) { $editKat = $c; break; }
    }
}
?>

<h1 class="text-2xl font-bold mb-4">📂 Kategori Facilities</h1>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- FORM TAMBAH / EDIT -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4"><?= $editKat ? '✏️ Edit Kategori' : '➕ Tambah Kategori Baru' ?></h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="<?= $editKat ? 'edit_kat' : 'add_kat' ?>">
            <?php if ($editKat): ?>
                <input type="hidden" name="id" value="<?= $editKat['id_kat_facilities'] ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                <input name="nm_kat_facilities" required placeholder="Contoh: Swimming Pool, Gym, Spa..."
                    value="<?= htmlspecialchars($editKat['nm_kat_facilities'] ?? '') ?>"
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Foto Kategori</label>
                <?php if ($editKat && !empty($editKat['foto_kat_facilities'])): ?>
                    <div class="mb-2">
                        <img src="<?= htmlspecialchars(get_full_url($editKat['foto_kat_facilities'])) ?>"
                            class="w-24 h-24 object-cover rounded border">
                        <p class="text-xs text-gray-500 mt-1">Foto saat ini (upload baru untuk mengganti)</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                           file:rounded-md file:border-0 file:text-sm file:font-semibold
                           file:bg-yellow-400 file:text-gray-900 hover:file:bg-yellow-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-yellow-400 text-gray-900 py-2 font-semibold rounded hover:bg-yellow-500">
                    <?= $editKat ? 'Perbarui Kategori' : 'Simpan Kategori' ?>
                </button>
                <?php if ($editKat): ?>
                    <a href="?page=kat_facilities" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-center">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR KATEGORI -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">📋 Daftar Kategori</h2>
        <?php if (empty($categories)): ?>
            <p class="text-gray-500 text-sm">Belum ada kategori yang ditambahkan.</p>
        <?php else: ?>
            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                <?php foreach ($categories as $c): ?>
                    <div class="flex gap-4 items-center border-b pb-3">
                        <?php if (!empty($c['foto_kat_facilities'])): ?>
                            <img src="<?= htmlspecialchars(get_full_url($c['foto_kat_facilities'])) ?>"
                                class="w-16 h-16 object-cover rounded border flex-shrink-0">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-gray-200 flex items-center justify-center text-xs text-gray-500 rounded flex-shrink-0">
                                No Foto
                            </div>
                        <?php endif; ?>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($c['nm_kat_facilities']) ?></h3>
                            <span class="text-xs text-gray-500"><?= $facCounts[$c['id_kat_facilities']] ?? 0 ?> facilities</span>
                        </div>

                        <div class="flex gap-1 flex-shrink-0">
                            <a href="?page=kat_facilities&edit=<?= $c['id_kat_facilities'] ?>"
                                class="bg-blue-500 text-white text-xs px-3 py-1 rounded hover:bg-blue-600">Edit</a>
                            <form method="POST" onsubmit="return confirm('Hapus kategori ini? Facilities yang terkait tidak akan terhapus.')">
                                <input type="hidden" name="action" value="delete_kat">
                                <input type="hidden" name="id" value="<?= $c['id_kat_facilities'] ?>">
                                <button type="submit" class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
