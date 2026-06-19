<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
$uploadDir = __DIR__ . '/../uploads/dining/';
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0775, true);

// Ambil status cart dining dari system_settings (default aktif)
$diningCartEnabled = 1;
try {
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'dining_cart_enabled' LIMIT 1");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    if ($val !== false) {
        $diningCartEnabled = (int)$val;
    }
} catch (Exception $e) {}

// Ambil kategori untuk dropdown
$katList = [];
try {
    $katList = $db->query("SELECT id_kat_dining, nm_kat_dining FROM kat_dining ORDER BY nm_kat_dining ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toggle status cart
    if (isset($_POST['toggle_dining_cart'])) {
        $newVal = isset($_POST['dining_cart_enabled']) ? '1' : '0';
        try {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value)
                VALUES ('dining_cart_enabled', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmt->execute([$newVal]);
        } catch (Exception $e) {
            // optional: bisa pakai flash error, tapi kita keep simple
        }
        header('Location: ?page=dining');
        exit;
    }
    if (isset($_POST['add_menu'])) {
        $katId = !empty($_POST['id_kat_dining']) ? (int)$_POST['id_kat_dining'] : null;
        $name = trim($_POST['name'] ?? '');
        $name_en = trim($_POST['name_en'] ?? ''); // Input baru
        $price = (int) ($_POST['price'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $image = trim($_POST['image'] ?? '');

        if (!empty($_FILES['upload_image']['name'])) {
            $maxSize = 10 * 1024 * 1024 * 1024; // 10GB (no limit)
            if ($_FILES['upload_image']['size'] > $maxSize) {
                flash('error', 'Ukuran gambar terlalu besar! Large file allowed. File Anda: ' . round($_FILES['upload_image']['size'] / 1024, 0) . 'KB');
                header('Location: ?page=dining');
                exit;
            }
            $fn = 'menu_' . time() . '_' . rand(1000, 9999) . '.jpg';
            if (move_uploaded_file($_FILES['upload_image']['tmp_name'], $uploadDir . $fn))
                $image = 'uploads/dining/' . $fn;
        }

        $db->prepare("INSERT INTO dining_menu (id_kat_dining, name, name_en, price, image_url, status) VALUES (?, ?, ?, ?, ?, ?)")->execute([$katId, $name, $name_en, $price, $image, $status]);
        header('Location: ?page=dining');
        exit;
    }

    if (isset($_POST['edit_id'])) {
        $katId = !empty($_POST['id_kat_dining']) ? (int)$_POST['id_kat_dining'] : null;
        $id = (int) $_POST['edit_id'];
        $name = trim($_POST['name'] ?? '');
        $name_en = trim($_POST['name_en'] ?? ''); // Input baru
        $price = (int) ($_POST['price'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $image = trim($_POST['image'] ?? '');

        if (!empty($_FILES['upload_image']['name'])) {
            $maxSize = 10 * 1024 * 1024 * 1024; // 10GB (no limit)
            if ($_FILES['upload_image']['size'] > $maxSize) {
                flash('error', 'Ukuran gambar terlalu besar! Large file allowed. File Anda: ' . round($_FILES['upload_image']['size'] / 1024, 0) . 'KB');
                header('Location: ?page=dining');
                exit;
            }
            $fn = 'menu_' . time() . '_' . rand(1000, 9999) . '.jpg';
            if (move_uploaded_file($_FILES['upload_image']['tmp_name'], $uploadDir . $fn))
                $image = 'uploads/dining/' . $fn;
        }

        $db->prepare("UPDATE dining_menu SET id_kat_dining=?, name=?, name_en=?, price=?, image_url=?, status=? WHERE id=?")->execute([$katId, $name, $name_en, $price, $image, $status, $id]);
        header('Location: ?page=dining');
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM dining_menu WHERE id=?")->execute([(int) $_POST['delete_id']]);
        header('Location: ?page=dining');
        exit;
    }
}
    // BATCH DELETE
    if (isset($_POST['batch_delete'])) {
        $ids = $_POST['selected_ids'] ?? [];
        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $db->prepare("DELETE FROM dining_menu WHERE id=?")->execute([$id]);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            $success = "$deleted item dihapus.";
        }
    }

$menus = $db->query("SELECT d.*, k.nm_kat_dining FROM dining_menu d LEFT JOIN kat_dining k ON d.id_kat_dining = k.id_kat_dining ORDER BY d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-yellow-500 mb-4">Dining Menu (Bilingual)</h1>
<form method="POST" class="mb-3 flex items-center gap-2">
    <input type="hidden" name="batch_delete" value="1">
    <label class="flex items-center gap-1 text-sm text-gray-600 cursor-pointer">
        <input type="checkbox" id="select-all" class="select-all-checkbox"> ☑ Select All
    </label>
    <button type="submit" class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600" onclick="return confirm('Hapus item terpilih?')">🗑 Hapus Terpilih</button>
</form>
    <!-- Toggle Cart Display for APK -->
    <div class="bg-white p-4 rounded shadow mb-4 flex items-center justify-between">
        <div>
            <p class="font-semibold text-gray-800">Tampilan Keranjang di APK</p>
            <p class="text-xs text-gray-500">Jika dimatikan, card keranjang di APK disembunyikan dan produk ditampilkan full-screen.</p>
        </div>
        <form method="POST" class="flex items-center gap-2">
            <input type="hidden" name="toggle_dining_cart" value="1">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="dining_cart_enabled" value="1" <?= $diningCartEnabled ? 'checked' : '' ?>>
                <span><?= $diningCartEnabled ? 'Aktif' : 'Nonaktif' ?></span>
            </label>
            <button type="submit" class="px-3 py-1 text-xs bg-yellow-500 text-white rounded">Simpan</button>
        </form>
    </div>
    <div class="bg-white p-6 rounded shadow mb-6">
        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2 md:col-span-1">
                    <label class="block font-semibold">Kategori</label>
                    <select name="id_kat_dining" class="w-full border rounded p-2">
                        <option value="">-- Tanpa Kategori --</option>
                        <?php foreach ($katList as $k): ?>
                            <option value="<?= $k['id_kat_dining'] ?>"><?= htmlspecialchars($k['nm_kat_dining']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block font-semibold">Nama (ID)</label><input type="text" name="name"
                        class="w-full border rounded p-2" required></div>
                <div><label class="block font-semibold">Name (EN)</label><input type="text" name="name_en"
                        class="w-full border rounded p-2" placeholder="English Name"></div>
                <div><label class="block font-semibold">Harga</label><input type="number" name="price"
                        class="w-full border rounded p-2" required></div>
                <div><label class="block font-semibold">Upload Gambar <span class="text-red-500 text-xs">(Maks.
                            1MB)</span></label><input type="file" name="upload_image" accept="image/*"
                        class="w-full border rounded p-2"></div>
                <div><label class="block font-semibold">Status</label><select name="status"
                        class="w-full border rounded p-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select></div>
            </div>
            <button type="submit" name="add_menu" class="bg-yellow-500 text-white px-4 py-2 rounded">Tambah
                Menu</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border"><input type="checkbox" id="select-all" class="select-all-checkbox"></th>
                    <th class="p-2 border">IMG</th>
                    <th class="p-2 border">Kategori</th>
                    <th class="p-2 border">Nama (ID)</th>
                    <th class="p-2 border">Name (EN)</th>
                    <th class="p-2 border">Harga</th>
                    <th class="p-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $m): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border text-center"><input type="checkbox" name="selected_ids[]" value="<?= $m['id'] ?>" class="batch-checkbox"></td>
                        <td class="p-2 border text-center"><img src="<?= htmlspecialchars(get_full_url($m['image_url'])) ?>"
                                class="h-10 w-10 object-cover mx-auto"></td>
                        <td class="p-2 border font-medium text-blue-800"><?= htmlspecialchars($m['nm_kat_dining'] ?? '-') ?></td>
                        <td class="p-2 border"><?= $m['name'] ?></td>
                        <td class="p-2 border italic text-gray-500"><?= $m['name_en'] ?></td>
                        <td class="p-2 border"><?= number_format($m['price']) ?></td>
                        <td class="p-2 border text-center space-x-2">
                            <button
                                onclick="edit(<?= $m['id'] ?>,'<?= $m['id_kat_dining'] ?>','<?= $m['name'] ?>','<?= $m['name_en'] ?>',<?= $m['price'] ?>,'<?= $m['image_url'] ?>','<?= $m['status'] ?>')"
                                class="text-blue-500 font-bold">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus?')"><input type="hidden"
                                    name="delete_id" value="<?= $m['id'] ?>"><button
                                    class="text-red-500 font-bold">Hapus</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-96">
        <h3 class="font-bold text-lg mb-4">Edit Menu</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="e_id">
            <select name="id_kat_dining" id="e_kat" class="w-full border mb-2 p-2">
                <option value="">-- Tanpa Kategori --</option>
                <?php foreach ($katList as $k): ?>
                    <option value="<?= $k['id_kat_dining'] ?>"><?= htmlspecialchars($k['nm_kat_dining']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="name" id="e_name" class="w-full border mb-2 p-2" placeholder="Nama ID">
            <input type="text" name="name_en" id="e_name_en" class="w-full border mb-2 p-2" placeholder="Name EN">
            <input type="number" name="price" id="e_price" class="w-full border mb-2 p-2">
            <input type="hidden" name="image" id="e_image"> <input type="file" name="upload_image"
                class="w-full border mb-2 p-2">
            <select name="status" id="e_status" class="w-full border mb-4 p-2">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <div class="flex justify-end gap-2"><button type="button"
                    onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="bg-gray-300 px-3 py-1 rounded">Batal</button><button
                    class="bg-yellow-500 text-white px-3 py-1 rounded">Simpan</button></div>
        </form>
    </div>
</div>
<script>
    function edit(id, k, n, ne, p, i, s) {
        document.getElementById('e_id').value = id; 
        document.getElementById('e_kat').value = k;
        document.getElementById('e_name').value = n; document.getElementById('e_name_en').value = ne;
        document.getElementById('e_price').value = p; document.getElementById('e_image').value = i; document.getElementById('e_status').value = s;
        document.getElementById('editModal').classList.remove('hidden'); document.getElementById('editModal').classList.add('flex');
    }
</script>