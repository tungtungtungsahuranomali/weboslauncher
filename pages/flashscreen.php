<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = $db ?? init_db_connection();
if ($db === null) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded'>Koneksi database gagal.</div>";
    return;
}

// Helper sederhana: simpan metadata agar APK bisa mendeteksi versi baru
function flashscreen_save_metadata(string $filename): void {
    $uploadDir = __DIR__ . '/../uploads/flashscreen/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    $metadata = [
        'version' => date('Y-m-d H:i:s'),
        'filename' => $filename,
        'url' => "uploads/flashscreen/" . $filename,
    ];
    @file_put_contents($uploadDir . 'metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (isset($_POST['upload_home_bg'])) {
        if (!empty($_FILES['bg_file']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/homebg/';
            if (!is_dir($uploadDir))
                @mkdir($uploadDir, 0775, true);

            $ext = strtolower(pathinfo($_FILES['bg_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($ext, $allowed)) {
                $filename = 'launcher_home_bg.' . $ext;
                $targetFile = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['bg_file']['tmp_name'], $targetFile)) {
                    // Simpan path relatif agar fleksibel (http/https)
                    // Tambahkan parameter waktu (?v=time()) agar gambar langsung berubah (refresh cache)
                    $relativePath = "uploads/homebg/" . $filename . "?v=" . time();


                    set_setting('launcher_home_bg', $relativePath);

                    flash('success', 'Background Home berhasil diperbarui!');
                } else {
                    flash('error', 'Gagal mengupload file background.');
                }
            } else {
                flash('error', 'Format file tidak valid. Hanya JPG/PNG.');
            }
        } else {
            flash('error', 'Pilih file gambar terlebih dahulu.');
        }
        header('Location: ?page=flashscreen');
        exit;
    }


    if (isset($_POST['toggle_splash'])) {

        $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key='splash_enabled'");
        $stmt->execute();
        $current = (int) ($stmt->fetchColumn() ?? 0);


        $newStatus = $current ? 0 : 1;
        set_setting('splash_enabled', $newStatus);

        flash('success', 'Status Flashscreen diperbarui: ' . ($newStatus ? 'AKTIF' : 'MATI'));
        header('Location: ?page=flashscreen');
        exit;
    }


    if (isset($_POST['upload_flash'])) {
        if (!empty($_FILES['video_file']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/flashscreen/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['mp4', 'mov', 'webm'];

            if (in_array($ext, $allowed, true)) {
                // Cek ukuran file: maksimal 250MB
                $maxSize = 700 * 1024 * 1024; // 700MB
                if (($_FILES['video_file']['size'] ?? 0) > $maxSize) {
                    flash('error', 'Ukuran video terlalu besar! Maksimal 700MB. File Anda: ' . round(($_FILES['video_file']['size'] ?? 0) / 1024 / 1024, 1) . 'MB');
                } else {
                    $filename = 'flashscreen.' . $ext;
                    $targetFile = $uploadDir . $filename;

                    // Hapus file lama (jika beda ekstensi) agar direktori bersih
                    foreach (glob($uploadDir . 'flashscreen.*') as $oldFile) {
                        if ($oldFile !== $targetFile && is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }

                    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetFile)) {
                        // Simpan metadata supaya APK bisa mendeteksi versi baru dan download ulang
                        flashscreen_save_metadata($filename);
                        flash('success', 'Video Flashscreen berhasil diupload!');
                    } else {
                        flash('error', 'Gagal mengupload video.');
                    }
                }
            } else {
                flash('error', 'Format video tidak valid (Gunakan MP4/MOV/WEBM).');
            }
        } else {
            flash('error', 'Pilih file video terlebih dahulu.');
        }
        header('Location: ?page=flashscreen');
        exit;
    }

    // 4. Simpan Custom Greeting
    if (isset($_POST['save_custom_greeting'])) {
        $title_id = trim($_POST['greeting_title_id'] ?? '');
        $title_en = trim($_POST['greeting_title_en'] ?? '');
        $content_id = trim($_POST['greeting_content_id'] ?? '');
        $content_en = trim($_POST['greeting_content_en'] ?? '');
        $image_url = trim($_POST['greeting_image_url'] ?? '');

        // New settings
        $title_id_enabled = isset($_POST['greeting_title_id_enabled']) ? 1 : 0;
        $title_en_enabled = isset($_POST['greeting_title_en_enabled']) ? 1 : 0;
        $content_id_enabled = isset($_POST['greeting_content_id_enabled']) ? 1 : 0;
        $content_en_enabled = isset($_POST['greeting_content_en_enabled']) ? 1 : 0;
        $title_color = $_POST['greeting_title_color'] ?? '#000000';
        $content_color = $_POST['greeting_content_color'] ?? '#000000';
        $btn_color = $_POST['greeting_btn_color'] ?? '#facc15';
        $btn_text_color = $_POST['greeting_btn_text_color'] ?? '#000000';

        $uploadDir = __DIR__ . '/../uploads/greeting/';
        if (!is_dir($uploadDir))
            @mkdir($uploadDir, 0775, true);

        $imagePath = $image_url;

        if (!empty($_FILES['upload_image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['upload_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $filename = 'greeting_img.' . $ext;
                if (move_uploaded_file($_FILES['upload_image']['tmp_name'], $uploadDir . $filename)) {
                    $imagePath = "uploads/greeting/" . $filename . '?v=' . time();
                }
            }
        }

        // Simpan versi Indonesia
        set_setting('custom_greeting_title', $title_id);
        set_setting('custom_welcome_greeting', $content_id);

        // Simpan versi English
        set_setting('custom_greeting_title_en', $title_en);
        set_setting('custom_welcome_greeting_en', $content_en);

        // Simpan status enabled/disabled
        set_setting('greeting_title_id_enabled', $title_id_enabled);
        set_setting('greeting_title_en_enabled', $title_en_enabled);
        set_setting('greeting_content_id_enabled', $content_id_enabled);
        set_setting('greeting_content_en_enabled', $content_en_enabled);

        // Simpan warna
        set_setting('greeting_title_color', $title_color);
        set_setting('greeting_content_color', $content_color);
        set_setting('greeting_btn_color', $btn_color);
        set_setting('greeting_btn_text_color', $btn_text_color);

        if (!empty($imagePath)) {
            set_setting('custom_greeting_image', $imagePath);
        }

        flash('success', 'Konten Sambutan berhasil diperbarui!');
        header('Location: ?page=flashscreen');
        exit;
    }
}


$splash_enabled = (int) ($db->query("SELECT setting_value FROM global_settings WHERE setting_key='splash_enabled'")->fetchColumn() ?? 0);

$metaFile = __DIR__ . '/../uploads/flashscreen/metadata.json';
$flashscreen_url = '';
$flashscreen_preview_url = '';
if (file_exists($metaFile)) {
    $meta = json_decode(file_get_contents($metaFile), true);
    $flashscreen_url = $meta['url'] ?? '';
    $flashscreen_version = $meta['version'] ?? '';

    if (!empty($flashscreen_url)) {
        $flashscreen_preview_url = get_full_url($flashscreen_url);
        if (!empty($flashscreen_version)) {
            // Tambah parameter versi agar browser admin tidak memakai cache lama
            $flashscreen_preview_url .= (strpos($flashscreen_preview_url, '?') === false ? '?' : '&') .
                'v=' . urlencode(strtotime($flashscreen_version));
        }
    }
}

$current_home_bg = trim($db->query("SELECT setting_value FROM global_settings WHERE setting_key='launcher_home_bg'")->fetchColumn() ?? '');

$custom_greeting_content_id = htmlspecialchars($db->query("SELECT setting_value FROM global_settings WHERE setting_key='custom_welcome_greeting'")->fetchColumn() ?? '');
$custom_greeting_title_id = htmlspecialchars($db->query("SELECT setting_value FROM global_settings WHERE setting_key='custom_greeting_title'")->fetchColumn() ?? '');
$custom_greeting_content_en = htmlspecialchars($db->query("SELECT setting_value FROM global_settings WHERE setting_key='custom_welcome_greeting_en'")->fetchColumn() ?? '');
$custom_greeting_title_en = htmlspecialchars($db->query("SELECT setting_value FROM global_settings WHERE setting_key='custom_greeting_title_en'")->fetchColumn() ?? '');
$custom_greeting_image = htmlspecialchars($db->query("SELECT setting_value FROM global_settings WHERE setting_key='custom_greeting_image'")->fetchColumn() ?? '');

// New settings fetch
$greeting_title_id_enabled = (int) (get_setting('greeting_title_id_enabled') ?? 1);
$greeting_title_en_enabled = (int) (get_setting('greeting_title_en_enabled') ?? 1);
$greeting_content_id_enabled = (int) (get_setting('greeting_content_id_enabled') ?? 1);
$greeting_content_en_enabled = (int) (get_setting('greeting_content_en_enabled') ?? 1);
$greeting_title_color = get_setting('greeting_title_color') ?: '#000000';
$greeting_content_color = get_setting('greeting_content_color') ?: '#000000';
$greeting_btn_color = get_setting('greeting_btn_color') ?: '#facc15';
$greeting_btn_text_color = get_setting('greeting_btn_text_color') ?: '#000000';
?>

<div class="bg-white p-8 rounded-lg shadow-lg space-y-10">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">🎬 Flashscreen & Background Launcher</h2>

    <div class="border-b pb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-3">Video Flashscreen</h3>
        <p class="text-gray-600 mb-4">Atur status flashscreen dan upload video baru.</p>

        <form method="POST">
            <button type="submit" name="toggle_splash"
                class="px-5 py-2 rounded-lg font-semibold 
                       <?= $splash_enabled ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-400 text-gray-900 hover:bg-green-500' ?>">
                <?= $splash_enabled ? 'Matikan Flashscreen' : 'Aktifkan Flashscreen' ?>
            </button>
        </form>

        <form method="POST" enctype="multipart/form-data" class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Video Flashscreen (MP4, MOV,
                WEBM) — <span class="text-red-500">Maks. 100MB</span></label>
            <input type="file" name="video_file" accept=".mp4,.mov,.webm"
                class="block mb-3 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
            <button type="submit" name="upload_flash"
                class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500">
                Upload Video
            </button>
        </form>

        <?php if (!empty($flashscreen_preview_url)): ?>
            <div class="mt-6">
                <p class="text-gray-600 mb-2">Preview video flashscreen saat ini:</p>
                <video src="<?= htmlspecialchars($flashscreen_preview_url) ?>" controls
                    class="w-96 rounded-lg shadow-md border"></video>
            </div>
        <?php endif; ?>
    </div>

    <div class="border-b pb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-3">Background Home Screen</h3>
        <p class="text-gray-600 mb-4">Upload gambar latar belakang launcher utama (JPG/PNG).</p>

        <form method="POST" enctype="multipart/form-data">
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Gambar Background</label>
            <input type="file" name="bg_file" accept=".jpg,.jpeg,.png"
                class="block mb-3 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">

            <button type="submit" name="upload_home_bg"
                class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:bg-yellow-500">
                Upload Background
            </button>
        </form>

        <?php if (!empty($current_home_bg)): ?>
            <div class="mt-6">
                <p class="text-gray-600 mb-2">Background launcher saat ini:</p>
                <img src="<?= htmlspecialchars(get_full_url($current_home_bg)) ?>" alt="Background"
                    class="rounded-lg shadow-md w-96 border">
            </div>
        <?php endif; ?>
    </div>

    <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-3 text-yellow-600">💖 Custom Welcome Greeting (Saat Booting)
        </h3>
        <p class="text-gray-600 mb-4">Atur konten, judul, dan gambar sambutan yang tampil setelah video splash screen.
            Tamu bisa memilih bahasa di TV.</p>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="save_custom_greeting" value="1">

            <!-- Judul -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">🇮🇩 Judul Sambutan (Indonesia)</label>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="greeting_title_id_enabled" class="hidden peer" <?= $greeting_title_id_enabled ? 'checked' : '' ?>>
                            <div class="relative w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-400"></div>
                            <span class="ms-2 text-xs font-medium text-gray-900">Aktif</span>
                        </label>
                    </div>
                    <input type="text" name="greeting_title_id" value="<?= $custom_greeting_title_id ?>"
                        placeholder="Selamat Datang"
                        class="w-full mt-1 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">🇬🇧 Judul Sambutan (English)</label>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="greeting_title_en_enabled" class="hidden peer" <?= $greeting_title_en_enabled ? 'checked' : '' ?>>
                            <div class="relative w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-400"></div>
                            <span class="ms-2 text-xs font-medium text-gray-900">Aktif</span>
                        </label>
                    </div>
                    <input type="text" name="greeting_title_en" value="<?= $custom_greeting_title_en ?>"
                        placeholder="Welcome"
                        class="w-full mt-1 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
            </div>

            <!-- Isi Pesan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">🇮🇩 Isi Pesan Sambutan (Indonesia)</label>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="greeting_content_id_enabled" class="hidden peer" <?= $greeting_content_id_enabled ? 'checked' : '' ?>>
                            <div class="relative w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-400"></div>
                            <span class="ms-2 text-xs font-medium text-gray-900">Aktif</span>
                        </label>
                    </div>
                    <textarea name="greeting_content_id" rows="6" placeholder="Selamat datang di Hotel kami..."
                        class="w-full mt-1 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"><?= $custom_greeting_content_id ?></textarea>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-medium text-gray-700">🇬🇧 Isi Pesan Sambutan (English)</label>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="greeting_content_en_enabled" class="hidden peer" <?= $greeting_content_en_enabled ? 'checked' : '' ?>>
                            <div class="relative w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-400"></div>
                            <span class="ms-2 text-xs font-medium text-gray-900">Aktif</span>
                        </label>
                    </div>
                    <textarea name="greeting_content_en" rows="6" placeholder="Welcome to our Hotel..."
                        class="w-full mt-1 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"><?= $custom_greeting_content_en ?></textarea>
                </div>
            </div>

            <!-- Pengaturan Warna -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🎨 Warna Text Judul</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="greeting_title_color" value="<?= $greeting_title_color ?>"
                            class="h-10 w-20 cursor-pointer rounded border border-gray-300">
                        <span class="text-xs text-gray-500 font-mono"><?= strtoupper($greeting_title_color) ?></span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🎨 Warna Text Isi Pesan</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="greeting_content_color" value="<?= $greeting_content_color ?>"
                            class="h-10 w-20 cursor-pointer rounded border border-gray-300">
                        <span class="text-xs text-gray-500 font-mono"><?= strtoupper($greeting_content_color) ?></span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🎨 Warna Background Button</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="greeting_btn_color" value="<?= $greeting_btn_color ?>"
                            class="h-10 w-20 cursor-pointer rounded border border-gray-300">
                        <span class="text-xs text-gray-500 font-mono"><?= strtoupper($greeting_btn_color) ?></span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">🎨 Warna Text Button</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="greeting_btn_text_color" value="<?= $greeting_btn_text_color ?>"
                            class="h-10 w-20 cursor-pointer rounded border border-gray-300">
                        <span class="text-xs text-gray-500 font-mono"><?= strtoupper($greeting_btn_text_color) ?></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Gambar Baru</label>
                    <input type="file" name="upload_image" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atau Path/URL Gambar</label>
                    <input type="text" name="greeting_image_url" value="<?= $custom_greeting_image ?>"
                        placeholder="uploads/greeting/image.jpg atau https://..."
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
            </div>

            <?php if (!empty($custom_greeting_image)): ?>
                <div class="mt-2">
                    <p class="text-gray-600 mb-1">Preview Gambar:</p>
                    <img src="<?= htmlspecialchars(get_full_url($custom_greeting_image)) ?>" alt="Greeting Image"
                        class="rounded-lg shadow-md w-64 border">
                </div>
            <?php endif; ?>

            <button type="submit" name="save_custom_greeting"
                class="w-full bg-yellow-400 text-gray-900 py-3 font-semibold rounded hover:bg-yellow-500 shadow-md transition">
                💾 Simpan Sambutan
            </button>
        </form>
    </div>
</div>