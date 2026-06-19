<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$db = init_db_connection();
$uploadDir = __DIR__ . '/../uploads/amenities/';
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0755, true);

// Ambil status card daftar permintaan amenities dari system_settings (default aktif)
$amenityRequestCardEnabled = 1;
try {
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'amenities_request_card_enabled' LIMIT 1");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    if ($val !== false) {
        $amenityRequestCardEnabled = (int)$val;
    }
} catch (Exception $e) {
    // abaikan jika tabel belum ada
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toggle status card daftar permintaan
    if (isset($_POST['toggle_amenities_request_card'])) {
        $newVal = isset($_POST['amenities_request_card_enabled']) ? '1' : '0';
        try {
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value)
                VALUES ('amenities_request_card_enabled', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmt->execute([$newVal]);
        } catch (Exception $e) {
        }
        header('Location: ?page=amenities');
        exit;
    }
    if (isset($_POST['add_amenity'])) {
        $name = $_POST['name'];
        $name_en = $_POST['name_en'];
        $desc = $_POST['description'];
        $desc_en = $_POST['description_en'];
        $img = $_POST['image_url'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $maxSize = 10 * 1024 * 1024 * 1024; // 10GB (no limit)
            if ($_FILES['image']['size'] > $maxSize) {
                flash('error', 'Ukuran gambar terlalu besar! Large file allowed. File Anda: ' . round($_FILES['image']['size'] / 1024, 0) . 'KB');
                header('Location: ?page=amenities');
                exit;
            }
            $fn = 'am_' . time() . '.jpg';
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn))
                $img = 'uploads/amenities/' . $fn;
        }
        $db->prepare("INSERT INTO room_amenities (name, name_en, description, description_en, icon_path) VALUES (?, ?, ?, ?, ?)")->execute([$name, $name_en, $desc, $desc_en, $img]);
    }
    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM room_amenities WHERE id=?")->execute([(int) $_POST['delete_id']]);
    }
    if (isset($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $name = $_POST['name']; $name_en = $_POST['name_en'];
        $desc = $_POST['description']; $desc_en = $_POST['description_en'];
        $img = $_POST['image_url'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $fn = 'am_' . time() . '.jpg';
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn)) $img = 'uploads/amenities/' . $fn;
        }
        if ($img) {
            $db->prepare("UPDATE room_amenities SET name=?, name_en=?, description=?, description_en=?, icon_path=? WHERE id=?")->execute([$name, $name_en, $desc, $desc_en, $img, $id]);
        } else {
            $db->prepare("UPDATE room_amenities SET name=?, name_en=?, description=?, description_en=? WHERE id=?")->execute([$name, $name_en, $desc, $desc_en, $id]);
        }
        echo '<script>window.location.href="admin.php?page=amenities";</script>';
        exit;
    }
}
$ams = $db->query("SELECT * FROM room_amenities ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">Amenities (Bilingual)</h1>
<div class="bg-white p-4 rounded shadow mb-4 flex items-center justify-between">
    <div>
        <p class="font-semibold text-gray-800">Tampilan Card Daftar Permintaan di APK</p>
        <p class="text-xs text-gray-500">Jika dimatikan, card daftar permintaan di APK disembunyikan sehingga fokus hanya ke daftar amenities.</p>
    </div>
    <form method="POST" class="flex items-center gap-2">
        <input type="hidden" name="toggle_amenities_request_card" value="1">
        <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" name="amenities_request_card_enabled" value="1" <?= $amenityRequestCardEnabled ? 'checked' : '' ?>>
            <span><?= $amenityRequestCardEnabled ? 'Aktif' : 'Nonaktif' ?></span>
        </label>
        <button type="submit" class="px-3 py-1 text-xs bg-yellow-500 text-white rounded">Simpan</button>
    </form>
</div>
<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="add_amenity" value="1">
            <input name="name" placeholder="Nama (ID)" class="w-full border p-2 rounded" required>
            <input name="name_en" placeholder="Name (EN)" class="w-full border p-2 rounded">
            <textarea name="description" placeholder="Deskripsi (ID)" class="w-full border p-2 rounded"></textarea>
            <textarea name="description_en" placeholder="Description (EN)" class="w-full border p-2 rounded"></textarea>
            <label class="block font-semibold text-sm mb-1">Upload Gambar <span class="text-red-500 text-xs">(Maks.
                    1MB)</span></label>
            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded">
            <button class="w-full bg-yellow-500 py-2 rounded text-white">Simpan</button>
        </form>
    </div>
    <div class="bg-white p-6 rounded shadow max-h-[600px] overflow-auto">
        <?php foreach ($ams as $a): ?>
            <div class="border-b pb-2 mb-2 flex justify-between items-start">
                <div class="flex gap-3">
                    <img src="<?= htmlspecialchars(get_full_url($a['icon_path'])) ?>"
                        class="w-16 h-16 object-cover rounded">
                    <div>
                        <b class="block"><?= $a['name'] ?> <span class="text-gray-400 text-sm font-normal">/
                                <?= $a['name_en'] ?></span></b>
                        <p class="text-xs text-gray-600"><?= $a['description'] ?></p>
                        <p class="text-xs text-gray-400 italic"><?= $a['description_en'] ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick='edit(<?= json_encode($a) ?>)' class="text-blue-500 text-sm">Edit</button>
                    <form method="POST" onsubmit="return confirm('Hapus?')">
                        <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                        <button class="text-red-500 text-sm">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display:none">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg mb-4">Edit Amenity</h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="edit_id" id="edit-id">
            <input name="name" id="edit-name" placeholder="Nama (ID)" class="w-full border p-2 rounded" required>
            <input name="name_en" id="edit-name_en" placeholder="Name (EN)" class="w-full border p-2 rounded">
            <textarea name="description" id="edit-description" placeholder="Deskripsi (ID)" class="w-full border p-2 rounded"></textarea>
            <textarea name="description_en" id="edit-description_en" placeholder="Description (EN)" class="w-full border p-2 rounded"></textarea>
            <div>
                <label class="block text-sm font-medium mb-1">Ganti Gambar (kosongkan jika tidak diubah)</label>
                <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded font-semibold">Update</button>
                <button type="button" onclick="closeEdit()" class="px-4 bg-gray-300 py-2 rounded">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function edit(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-name').value = data.name || '';
    document.getElementById('edit-name_en').value = data.name_en || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('edit-description_en').value = data.description_en || '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
