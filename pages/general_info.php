<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$db = init_db_connection();
$uploadDir = __DIR__ . '/../uploads/general_info/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Ambil kategori untuk dropdown
$katList = [];
try {
    $katList = $db->query("SELECT id_kat_general_info, nm_kat_general_info FROM kat_general_info ORDER BY nm_kat_general_info ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_info'])) {
        $title = $_POST['title']; $title_en = $_POST['title_en'];
        $desc = $_POST['description']; $desc_en = $_POST['description_en'];
        $showDesc = (int)($_POST['show_description'] ?? 1);
        $katId = !empty($_POST['id_kat_general_info']) ? (int)$_POST['id_kat_general_info'] : null;
        $img = $_POST['image_url'] ?? '';
        
        if (!empty($_FILES['image']['name'])) {
            $fn = 'gen_info_' . time() . '.jpg';
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fn)) $img = 'uploads/general_info/' . $fn;
        }
        $db->prepare("INSERT INTO general_info (id_kat_general_info, title, title_en, description, description_en, icon_path, show_description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")->execute([$katId, $title, $title_en, $desc, $desc_en, $img, $showDesc]);
    }
    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM general_info WHERE id=?")->execute([(int)$_POST['delete_id']]);
    }
}

// Ambil info dengan nama kategori
$infos = $db->query("SELECT f.*, k.nm_kat_general_info FROM general_info f LEFT JOIN kat_general_info k ON f.id_kat_general_info = k.id_kat_general_info ORDER BY f.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">General Info (Bilingual)</h1>
<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <form method="POST" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="add_info" value="1">
            
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="id_kat_general_info" class="w-full border p-2 rounded">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php foreach ($katList as $k): ?>
                        <option value="<?= $k['id_kat_general_info'] ?>"><?= htmlspecialchars($k['nm_kat_general_info']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input name="title" placeholder="Judul (ID)" class="w-full border p-2 rounded" required>
            <input name="title_en" placeholder="Title (EN)" class="w-full border p-2 rounded">
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
                    <b class="block"><?=$i['title']?> <span class="text-gray-400 text-sm font-normal">/ <?=$i['title_en']?></span></b>
                    <p class="text-xs text-gray-600"><?=$i['description']?></p>
                    <?php if (!empty($i['nm_kat_general_info'])): ?>
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">📂 <?= htmlspecialchars($i['nm_kat_general_info']) ?></span>
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
