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
            <form method="POST" onsubmit="return confirm('Hapus?')">
                <input type="hidden" name="delete_id" value="<?=$i['id']?>">
                <button class="text-red-500 text-sm">Hapus</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
