<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$db = init_db_connection();
$uploadDir = __DIR__ . '/../uploads/promotion/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Ambil kategori untuk dropdown
$katList = [];
try {
    $katList = $db->query("SELECT id_kat_promotion, nm_kat_promotion FROM kat_promotion ORDER BY nm_kat_promotion ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_info'])) {
        $name = $_POST['name']; $name_en = $_POST['name_en'];
        $desc = $_POST['description']; $desc_en = $_POST['description_en'];
        $showDesc = (int)($_POST['show_description'] ?? 1);
        $katId = !empty($_POST['id_kat_promotion']) ? (int)$_POST['id_kat_promotion'] : null;
        $img = $_POST['image_url'] ?? '';
        
        if (!empty($_FILES['image']['name'])) {
            $fn = 'promo_' . time() . '.jpg';
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn)) $img = 'uploads/promotion/' . $fn;
        }
        $db->prepare("INSERT INTO promotion (id_kat_promotion, name, name_en, description, description_en, icon_path, show_description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")->execute([$katId, $name, $name_en, $desc, $desc_en, $img, $showDesc]);
    }
    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM promotion WHERE id=?")->execute([(int)$_POST['delete_id']]);
    }
    if (isset($_POST['edit_id'])) {
        $id = (int)$_POST['edit_id'];
        $name = $_POST['name']; $name_en = $_POST['name_en'];
        $desc = $_POST['description']; $desc_en = $_POST['description_en'];
        $showDesc = (int)($_POST['show_description'] ?? 1);
        $katId = !empty($_POST['id_kat_promotion']) ? (int)$_POST['id_kat_promotion'] : null;
        $img = $_POST['image_url'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $fn = 'promo_' . time() . '.jpg';
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn)) $img = 'uploads/promotion/' . $fn;
        }
        if ($img) {
            $db->prepare("UPDATE promotion SET id_kat_promotion=?, name=?, name_en=?, description=?, description_en=?, icon_path=?, show_description=? WHERE id=?")->execute([$katId, $name, $name_en, $desc, $desc_en, $img, $showDesc, $id]);
        } else {
            $db->prepare("UPDATE promotion SET id_kat_promotion=?, name=?, name_en=?, description=?, description_en=?, show_description=? WHERE id=?")->execute([$katId, $name, $name_en, $desc, $desc_en, $showDesc, $id]);
        }
        echo '<script>window.location.href="admin.php?page=promotion";</script>';
        exit;
    }
}

// Ambil info dengan nama kategori
$infos = $db->query("SELECT f.*, k.nm_kat_promotion FROM promotion f LEFT JOIN kat_promotion k ON f.id_kat_promotion = k.id_kat_promotion ORDER BY f.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">Promotion (Bilingual)</h1>
<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="add_info" value="1">
            
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="id_kat_promotion" class="w-full border p-2 rounded">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php foreach ($katList as $k): ?>
                        <option value="<?= $k['id_kat_promotion'] ?>"><?= htmlspecialchars($k['nm_kat_promotion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input name="name" placeholder="Nama Promosi (ID)" class="w-full border p-2 rounded" required>
            <input name="name_en" placeholder="Promotion Name (EN)" class="w-full border p-2 rounded">
            <textarea name="description" placeholder="Deskripsi (ID)" class="w-full border p-2 rounded"></textarea>
            <textarea name="description_en" placeholder="Description (EN)" class="w-full border p-2 rounded"></textarea>
            
            <div>
                <label class="block text-sm font-medium mb-1">Tampilkan Keterangan di TV?</label>
                <select name="show_description" class="w-full border p-2 rounded">
                    <option value="1">Ya, Tampilkan Teks</option>
                    <option value="0">Tidak (Hanya Gambar Full)</option>
                </select>
            </div>

            <input type="file" name="image" class="w-full border p-2 rounded">
            <button class="w-full bg-yellow-500 py-2 rounded text-white">Simpan</button>
        </form>
    </div>
    <div class="bg-white p-6 rounded shadow max-h-[600px] overflow-auto">
        <?php foreach($infos as $i): ?>
        <div class="border-b pb-2 mb-2 flex justify-between items-start">
            <div class="flex gap-3">
                <img src="<?= htmlspecialchars(get_full_url($i['icon_path'])) ?>" class="w-16 h-16 object-cover rounded">
                <div>
                    <b class="block"><?=$i['name']?> <span class="text-gray-400 text-sm font-normal">/ <?=$i['name_en']?></span></b>
                    <p class="text-xs text-gray-600"><?=$i['description']?></p>
                    <?php if (!empty($i['nm_kat_promotion'])): ?>
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">📂 <?= htmlspecialchars($i['nm_kat_promotion']) ?></span>
                    <?php else: ?>
                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Tanpa Kategori</span>
                    <?php endif; ?>
                    <span class="text-xs px-2 py-0.5 rounded <?= $i['show_description'] ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' ?>">
                        <?= $i['show_description'] ? 'Teks Aktif' : 'Gambar Full' ?>
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick='edit(<?= json_encode($i) ?>)' class="text-blue-500 text-sm">Edit</button>
                <form method="POST" onsubmit="return confirm('Hapus?')">
                    <input type="hidden" name="delete_id" value="<?=$i['id']?>">
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
        <h3 class="font-bold text-lg mb-4">Edit Promotion</h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="edit_id" id="edit-id">

            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="id_kat_promotion" id="edit-kategori" class="w-full border p-2 rounded">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php foreach ($katList as $k): ?>
                        <option value="<?= $k['id_kat_promotion'] ?>"><?= htmlspecialchars($k['nm_kat_promotion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input name="name" id="edit-name" placeholder="Nama Promosi (ID)" class="w-full border p-2 rounded" required>
            <input name="name_en" id="edit-name_en" placeholder="Promotion Name (EN)" class="w-full border p-2 rounded">
            <textarea name="description" id="edit-description" placeholder="Deskripsi (ID)" class="w-full border p-2 rounded"></textarea>
            <textarea name="description_en" id="edit-description_en" placeholder="Description (EN)" class="w-full border p-2 rounded"></textarea>

            <div>
                <label class="block text-sm font-medium mb-1">Tampilkan Keterangan di TV?</label>
                <select name="show_description" id="edit-show_desc" class="w-full border p-2 rounded">
                    <option value="1">Ya, Tampilkan Teks</option>
                    <option value="0">Tidak (Hanya Gambar Full)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Ganti Gambar (kosongkan jika tidak diubah)</label>
                <input type="file" name="image" class="w-full border p-2 rounded">
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
    document.getElementById('edit-show_desc').value = data.show_description || '1';
    document.getElementById('edit-kategori').value = data.id_kat_promotion || '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
