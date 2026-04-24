<?php
// ===========================================================
// APP CONTROL MODULE (v15.1 - Fixed Icons)
// ===========================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if (!$db)
    die("<div class='bg-red-100 border border-red-400 text-red-700 p-4 rounded'>Gagal konek database.</div>");

// === Hapus aplikasi ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM system_apps WHERE id=?");
    $stmt->execute([$id]);
    flash('success', "Aplikasi berhasil dihapus.");
    header("Location: admin.php?page=app_control");
    exit;
}

// === Tambah aplikasi baru ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_app'])) {
    $name = trim($_POST['app_name'] ?? '');
    $package = trim($_POST['android_package'] ?? '');
    $sort = (int) ($_POST['sort_order'] ?? 99);
    
    if ($name) {
        $app_key = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
        $app_key = trim($app_key, '_'); // Hapus underscore di awal/akhir
        
        // Overwrite common keys to match PAGE_ROUTES
        if ($app_key === 'general_information' || $app_key === 'general_info') {
            $app_key = 'general_info';
        }

        // Jika package kosong, buat package internal unik
        if (empty($package)) {
            $package = "internal." . $app_key . "." . time();
        }

        $uploadDir = __DIR__ . '/../uploads/icons/';
        if (!file_exists($uploadDir))
            mkdir($uploadDir, 0777, true);

        $iconUrl = '';
        if (!empty($_FILES['icon_file']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION));
            $filename = 'icon_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['icon_file']['tmp_name'], $uploadDir . $filename);
            $iconUrl = "uploads/icons/" . $filename;
        }

        $stmt = $db->prepare("INSERT INTO system_apps (app_key, app_name, android_package, icon_path, is_visible, sort_order)
                              VALUES (?, ?, ?, ?, 1, ?)");
        $stmt->execute([
            $app_key,
            $name,
            $package,
            $iconUrl,
            $sort
        ]);
        flash('success', "Aplikasi <b>{$name}</b> berhasil ditambahkan!");
    } else {
        flash('error', 'Nama aplikasi wajib diisi.');
    }

    header("Location: admin.php?page=app_control");
    exit;
}

// === Update Aplikasi ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_app'])) {
    $id = (int) $_POST['id'];
    $name = trim($_POST['app_name'] ?? '');
    $package = trim($_POST['android_package'] ?? '');
    $sort = (int) ($_POST['sort_order'] ?? 99);

    if ($name) {
        $app_key = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
        $app_key = trim($app_key, '_');

        if ($app_key === 'general_information' || $app_key === 'general_info') {
            $app_key = 'general_info';
        }

        if (empty($package)) {
            $package = "internal." . $app_key;
        }

        $uploadDir = __DIR__ . '/../uploads/icons/';
        if (!file_exists($uploadDir))
            mkdir($uploadDir, 0777, true);

        $iconSql = '';
        $params = [$name, $app_key, $package, $sort];

        if (!empty($_FILES['icon_file']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION));
            $filename = 'icon_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['icon_file']['tmp_name'], $uploadDir . $filename);
            $iconUrl = "uploads/icons/" . $filename;
            $iconSql = ", icon_path=?";
            $params[] = $iconUrl;
        }

        $params[] = $id;

        $stmt = $db->prepare("UPDATE system_apps SET app_name=?, app_key=?, android_package=?, sort_order=? $iconSql WHERE id=?");
        $stmt->execute($params);
        flash('success', "Aplikasi <b>{$name}</b> berhasil diperbarui!");
    } else {
        flash('error', 'Nama aplikasi wajib diisi.');
    }

    header("Location: admin.php?page=app_control");
    exit;
}

// === Update ON/OFF ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Ambil semua ID app yang ada
    $stmt = $db->query("SELECT id FROM system_apps");
    $allIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $checkedIds = $_POST['app'] ?? [];

    foreach ($allIds as $id) {
        $status = isset($checkedIds[$id]) ? 1 : 0;
        $db->prepare("UPDATE system_apps SET is_visible=? WHERE id=?")->execute([$status, $id]);
    }

    flash('success', 'Status aplikasi berhasil diperbarui.');
    header("Location: admin.php?page=app_control");
    exit;
}

// === Ambil aplikasi yang akan diedit ===
$editApp = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM system_apps WHERE id=?");
    $stmt->execute([$editId]);
    $editApp = $stmt->fetch(PDO::FETCH_ASSOC);
}

// === Ambil daftar aplikasi ===
$stmt = $db->query("SELECT id, app_name, android_package, icon_path, is_visible, sort_order 
                    FROM system_apps ORDER BY sort_order ASC, id ASC");
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto pb-32">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">🎮 Entertainment App Manager</h1>
    <p class="text-gray-600 mb-8">
        Kelola aplikasi hiburan yang muncul di launcher hotel.
    </p>

    <form method="POST">
        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach ($apps as $app): ?>
                <div
                    class="flex items-center justify-between bg-white rounded-xl shadow-md p-4 border border-gray-200 hover:shadow-lg transition">
                    <div class="flex items-center space-x-3">
                        <img src="<?= htmlspecialchars(get_full_url($app['icon_path'])) ?>"
                            class="w-12 h-12 rounded-lg border border-gray-300 object-contain bg-gray-800 p-1" alt="">
                        <div>
                            <h2 class="text-lg font-semibold"><?= htmlspecialchars($app['app_name']) ?></h2>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($app['android_package']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="app[<?= $app['id'] ?>]" 
                                   value="on"
                                   <?= $app['is_visible'] ? 'checked' : '' ?> 
                                   class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-yellow-400 transition relative">
                                <div
                                    class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-all peer-checked:translate-x-5">
                                </div>
                            </div>
                        </label>
                        <a href="admin.php?page=app_control&edit=<?= $app['id'] ?>"
                            class="px-2 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow">
                            ✏️ Edit
                        </a>
                        <a href="admin.php?page=app_control&delete=<?= $app['id'] ?>"
                            onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($app['app_name']) ?>?')"
                            class="px-2 py-1 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg shadow">
                            🗑 Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 flex justify-end">
            <button name="update"
                class="px-5 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg shadow-lg">
                💾 Simpan Perubahan
            </button>
        </div>
    </form>

    <div class="mt-12 bg-white p-6 rounded-xl shadow-lg border border-gray-200" id="formArea">
        <h2 class="text-xl font-bold text-gray-800 mb-4"><?= $editApp ? "✏️ Edit Aplikasi" : "➕ Tambah Aplikasi Baru" ?></h2>
        <form method="POST" action="admin.php?page=app_control" enctype="multipart/form-data">
            <input type="hidden" name="<?= $editApp ? 'edit_app' : 'add_app' ?>" value="1">
            <?php if ($editApp): ?>
                <input type="hidden" name="id" value="<?= $editApp['id'] ?>">
            <?php endif; ?>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi</label>
                    <input type="text" name="app_name" required value="<?= htmlspecialchars($editApp['app_name'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Package Android (Opsional)</label>
                    <input type="text" name="android_package" placeholder="contoh: com.netflix.ninja" value="<?= htmlspecialchars($editApp['android_package'] ?? '') ?>"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Tampilan</label>
                    <input type="number" name="sort_order" value="<?= htmlspecialchars($editApp['sort_order'] ?? '99') ?>"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Aplikasi (PNG/JPG)</label>
                    <?php if ($editApp && $editApp['icon_path']): ?>
                        <div class="mb-2">
                            <img src="<?= htmlspecialchars(get_full_url($editApp['icon_path'])) ?>" class="h-10 w-10 bg-gray-800 p-1 rounded-md object-contain border">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="icon_file" accept="image/*"
                        class="block w-full text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <?php if ($editApp): ?>
                    <a href="admin.php?page=app_control" class="px-5 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg shadow-lg">❌ Batal</a>
                <?php endif; ?>
                <button
                    class="px-5 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg shadow-lg">
                    <?= $editApp ? "💾 Simpan Perubahan" : "➕ Tambah Aplikasi" ?>
                </button>
            </div>
        </form>
    </div>
    
    <?php if ($editApp): ?>
    <script>
        // Scroll to form automatically if editing
        document.getElementById('formArea').scrollIntoView({ behavior: 'smooth' });
    </script>
    <?php endif; ?>
</div>