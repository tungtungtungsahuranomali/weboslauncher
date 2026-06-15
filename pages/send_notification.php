<?php
/**
 * pages/send_notification.php
 *
 * Admin page untuk mengirim POPUP message ke device tertentu (berdasarkan kamar).
 * Cara kerja:
 *  - Admin memilih kamar + isi pesan
 *  - Server insert ke tabel popup_notifications per device_id (status=pending)
 *  - Android TV/STB ambil via polling: api.php?action=getNotifications&device_id=...
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if ($db === null) {
    http_response_code(500);
    echo "<div style='padding:12px;background:#fee2e2;color:#991b1b;border-radius:8px;'>❌ Gagal koneksi database.</div>";
    exit;
}

// Auto-create upload directories
$uploadDirs = [__DIR__ . '/../uploads/notif', __DIR__ . '/../uploads/sound'];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

function json_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    if (ob_get_length()) {
        ob_clean();
    }  // bersihkan output “nyasar”
    echo json_encode($payload);
    exit;
}

/**
 * Ambil daftar device dari managed_devices.
 * room_number dipakai untuk tampilan & pemilihan kamar.
 */
function getManagedDevices(PDO $db): array
{
    $stmt = $db->prepare("
        SELECT id, device_id, device_name, room_number
        FROM managed_devices
        ORDER BY
            CASE WHEN room_number REGEXP '^[0-9]+$' THEN CAST(room_number AS UNSIGNED) ELSE 999999 END,
            room_number,
            device_name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Enqueue popup untuk target devices.
 * title boleh null/empty.
 */
function enqueuePopupNotifications(PDO $db, array $targets, ?string $title, string $body, int $ttlMinutes = 1440, ?string $imageUrl = null, ?string $soundUrl = null): int
{
    // Modifikasi query untuk memasukkan image_url dan sound_url
    $insert = $db->prepare("
        INSERT INTO popup_notifications
            (device_id, room_number, title, body, status, created_at, expires_at, image_url, sound_url)
        VALUES
            (:device_id, :room_number, :title, :body, 'pending', NOW(), DATE_ADD(NOW(), INTERVAL :ttl MINUTE), :image_url, :sound_url)
    ");

    $count = 0;
    foreach ($targets as $t) {
        $deviceId = trim((string) ($t['device_id'] ?? ''));
        if ($deviceId === '') {
            continue; // skip device yang belum punya device_id
        }

        // Eksekusi query dengan URL gambar dan suara jika ada
        $insert->execute([
            ':device_id' => $deviceId,
            ':room_number' => (string) ($t['room_number'] ?? ''),
            ':title' => ($title !== null && $title !== '') ? $title : null,
            ':body' => $body,
            ':ttl' => $ttlMinutes,
            ':image_url' => $imageUrl,  // Menyimpan URL gambar jika ada
            ':sound_url' => $soundUrl,  // Menyimpan URL suara jika ada
        ]);
        $count++;
    }
    return $count;
}

/**
 * Resolve selected managed_devices.id -> target rows.
 */
function resolveTargetsByIds(PDO $db, array $ids): array
{
    $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
    if (!$ids)
        return [];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("
        SELECT id, device_id, room_number
        FROM managed_devices
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/* =========================
 * POST handler (AJAX)
 * ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Anda bisa menambahkan auth check di sini (session/role) jika diperlukan.

    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    $rooms = $_POST['rooms'] ?? [];
    $ttl = (int) ($_POST['ttl_minutes'] ?? 1440);



    if ($_POST['notificationType'] == 'image' && isset($_FILES['imageUpload'])) {
        // Upload gambar
        $image = $_FILES['imageUpload'];
        $targetDir = __DIR__ . '/../uploads/notif/';
        $targetFile = $targetDir . basename($image['name']);

        // Cek apakah file valid (gambar)
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];  // Format gambar yang diterima

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                $imageUrl = 'uploads/notif/' . basename($image['name']);
            } else {
                echo "Error uploading image.";
                exit;
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            exit;
        }
    }

    if ($_POST['notificationType'] == 'image' && isset($_POST['sound_url']) && $_POST['sound_url'] !== '') {
        // Ambil URL suara yang dipilih dari form
        $soundUrl = $_POST['sound_url'];
    } else {
        // Jika suara diupload, proses upload suara
        if (isset($_FILES['soundUpload'])) {
            $sound = $_FILES['soundUpload'];
            $soundTargetDir = __DIR__ . '/../uploads/sound/';
            $soundTargetFile = $soundTargetDir . basename($sound['name']);
            $soundFileType = strtolower(pathinfo($soundTargetFile, PATHINFO_EXTENSION));
            $allowedSoundTypes = ['mp3', 'wav', 'ogg'];

            if (in_array($soundFileType, $allowedSoundTypes)) {
                if (move_uploaded_file($sound['tmp_name'], $soundTargetFile)) {
                    $soundUrl = 'uploads/sound/' . basename($sound['name']);
                } else {
                    echo "Error uploading sound.";
                    exit;
                }
            } else {
                echo "Invalid sound file type.";
                exit;
            }
        }
    }



    if ($ttl <= 0)
        $ttl = 1440;
    if ($ttl > 10080)
        $ttl = 10080; // max 7 hari

    // Validasi minimal client-side dan server-side
    if ($_POST['notificationType'] == 'text') {
        // Jika tipe notifikasi adalah text, body harus diisi
        if ($body === '') {
            json_response(['status' => 'error', 'message' => 'Pesan (body) wajib diisi.'], 400);
        }
    }


    // Batasi panjang agar aman
    if (mb_strlen($title) > 255)
        $title = mb_substr($title, 0, 255);
    if (mb_strlen($body) > 2000)
        $body = mb_substr($body, 0, 2000);

    $imageUrl = isset($imageUrl) ? $imageUrl : null;
    $soundUrl = isset($soundUrl) ? $soundUrl : null;

    if (!is_array($rooms) || empty($rooms)) {
        json_response(['status' => 'error', 'message' => 'Pilih minimal 1 kamar.'], 400);
    }

    // Resolve target device(s)
    $targets = resolveTargetsByIds($db, $rooms);
    if (!$targets) {
        json_response(['status' => 'error', 'message' => 'Target device tidak ditemukan.'], 404);
    }

    try {
        $db->beginTransaction();

        $queued = enqueuePopupNotifications(
            $db,
            $targets,
            ($title !== '' ? $title : null),
            $body,
            $ttl,
            $imageUrl,  // URL gambar (jika ada)
            $soundUrl   // URL suara (jika ada) 
        );

        $db->commit();

        if ($queued <= 0) {
            json_response([
                'status' => 'error',
                'message' => 'Tidak ada device yang valid untuk menerima pesan (device_id kosong / belum terdaftar).'
            ], 400);
        }

        json_response([
            'status' => 'success',
            'message' => "Pesan berhasil di-queue ke {$queued} device."
        ]);
    } catch (Throwable $e) {
        if ($db->inTransaction())
            $db->rollBack();
        json_response(['status' => 'error', 'message' => 'Terjadi kesalahan saat enqueue pesan.'], 500);
    }
}

/* GET: Render HTML */
$devices = getManagedDevices($db);
?>
<style>
    .notif-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .notif-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media(max-width:860px) {
        .notif-grid {
            grid-template-columns: 1fr;
        }
    }

    .notif-label {
        display: block;
        font-size: 13px;
        color: #374151;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .notif-input,
    .notif-card textarea,
    .notif-card select {
        width: 100%;
        box-sizing: border-box;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        color: #111827;
        padding: 10px 12px;
        font-size: 14px;
        outline: none;
    }

    .notif-input:focus,
    .notif-card textarea:focus {
        border-color: #eab308;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.1);
    }

    .notif-card textarea {
        min-height: 100px;
        resize: vertical;
    }

    .notif-btn {
        border: 0;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
    }

    .notif-btn-primary {
        background: #eab308;
        color: #111827;
    }

    .notif-btn-primary:hover {
        background: #ca8a04;
    }

    .notif-btn-ghost {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .notif-btn-ghost:hover {
        background: #e5e7eb;
    }

    .notif-alert {
        display: none;
        margin: 12px 0 0;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
    }

    .notif-alert.success {
        display: block;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .notif-alert.error {
        display: block;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .rooms-toolbar {
        margin-top: 16px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .rooms-box {
        margin-top: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
        background: #f9fafb;
        max-height: 300px;
        overflow: auto;
    }

    .rooms-box ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    @media(max-width:860px) {
        .rooms-box ul {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .room-item {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    .room-item:hover {
        border-color: #eab308;
    }

    .room-item small {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-top: 2px;
    }

    .room-item.disabled {
        opacity: 0.45;
    }

    .radio-group {
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 16px;
    }

    .radio-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 14px;
        color: #374151;
    }
</style>
<div class="notif-card">
    <p class="text-sm text-gray-500 mb-4">Pesan akan di-queue ke device yang dipilih, lalu TV/STB akan menampilkan popup
        saat polling.</p>
    <form id="notificationForm" method="post">

        <div class="radio-group">
            <span class="notif-label" style="margin:0">Tipe:</span>
            <label><input type="radio" name="notificationType" value="text" checked> 📝 Text</label>
            <label><input type="radio" name="notificationType" value="image"> 🖼️ Image</label>
        </div>

        <div class="notif-grid" id="tiitleText">
            <div>
                <label class="notif-label">Judul (opsional)</label>
                <input class="notif-input" name="title" placeholder="Contoh: Informasi" />
            </div>

            <!-- <div>
                    <label>Masa berlaku (menit)</label>
                    <select class="input" name="ttl_minutes">
                        <option value="60">60 menit</option>
                        <option value="240">240 menit</option>
                        <option value="1440" selected>1 hari</option>
                        <option value="4320">3 hari</option>
                        <option value="10080">7 hari</option>
                    </select>
                </div> -->
        </div>

        <div style="margin-top:14px;" id="textInputContainer">
            <label class="notif-label">Pesan (wajib)</label>
            <textarea class="notif-input" name="body" placeholder="Tulis isi popup di sini..."></textarea>
        </div>

        <div id="imageInputContainer" style="display: none; margin-top:14px;">
            <label class="notif-label">Upload Gambar</label>
            <input type="file" id="imageUpload" name="imageUpload" accept="image/*" class="notif-input">
        </div>

        <div id="soundInputContainer" style="margin-top:14px;">
            <label class="notif-label">Efek Suara</label>
            <select id="soundEffect" name="soundEffect" class="notif-input">
                <option value="">-- Pilih Suara --</option>
                <?php
                $soundDir = __DIR__ . '/../uploads/sound';
                if (is_dir($soundDir)) {
                    $soundFiles = array_diff(scandir($soundDir), array('..', '.'));
                    foreach ($soundFiles as $soundFile) {
                        echo "<option value='uploads/sound/{$soundFile}'>{$soundFile}</option>";
                    }
                }
                ?>
            </select>
            <div id="newSoundUpload" style="margin-top:8px;">
                <label class="notif-label">Atau Upload Suara Baru</label>
                <input type="file" id="soundUpload" name="soundUpload" accept="audio/*" class="notif-input">
            </div>
        </div>

        <div class="rooms-toolbar">
            <input class="notif-input" id="roomSearch" placeholder="🔍 Cari kamar / nama device..."
                style="flex:1;min-width:240px;" />
            <button type="button" class="notif-btn notif-btn-ghost" id="selectAllBtn">Pilih Semua</button>
            <button type="button" class="notif-btn notif-btn-ghost" id="deselectAllBtn">Batal Pilih</button>
        </div>

        <div class="rooms-box">
            <ul id="roomsList">
                <?php if (!$devices): ?>
                    <li style="grid-column:1/-1;color:#6b7280;">Tidak ada data device di managed_devices.</li>
                <?php else: ?>
                    <?php foreach ($devices as $d): ?>
                        <?php
                        $id = (int) $d['id'];
                        $room = htmlspecialchars((string) ($d['room_number'] ?? ''));
                        $name = htmlspecialchars((string) ($d['device_name'] ?? ''));
                        $hasDeviceId = trim((string) ($d['device_id'] ?? '')) !== '';
                        ?>
                        <li class="room-item <?= $hasDeviceId ? '' : 'disabled' ?>" data-room="<?= $room ?>"
                            data-name="<?= $name ?>">
                            <input type="checkbox" name="rooms[]" value="<?= $id ?>" id="room_<?= $id ?>" <?= $hasDeviceId ? '' : 'disabled' ?> />
                            <label for="room_<?= $id ?>" style="margin:0;cursor:pointer;">
                                <div><strong>Kamar <?= $room !== '' ? $room : '-' ?></strong></div>
                                <small><?= $name !== '' ? $name : 'Device' ?><?= $hasDeviceId ? '' : ' (belum terdaftar)' ?></small>
                            </label>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div style="margin-top:16px; display:flex; justify-content:flex-end;">
            <button type="submit" class="notif-btn notif-btn-primary">🔔 Kirim Popup</button>
        </div>

        <div id="alertMessage" class="notif-alert"></div>
    </form>
</div>

<script>
    (function () {
        const form = document.getElementById('notificationForm');
        const alertBox = document.getElementById('alertMessage');
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const searchInput = document.getElementById('roomSearch');
        const roomsList = document.getElementById('roomsList');

        // Event listener untuk memilih tipe notifikasi (text/image)
        document.querySelectorAll('input[name="notificationType"]').forEach(input => {
            input.addEventListener('change', function () {
                const notificationType = this.value;

                // Menampilkan input sesuai dengan tipe yang dipilih
                if (notificationType === 'text') {
                    document.getElementById('textInputContainer').style.display = 'block';
                    document.getElementById('tiitleText').style.display = 'block';
                    document.getElementById('imageInputContainer').style.display = 'none';
                    document.getElementById('soundInputContainer').style.display = 'none';
                } else if (notificationType === 'image') {
                    document.getElementById('textInputContainer').style.display = 'none';
                    document.getElementById('tiitleText').style.display = 'none';
                    document.getElementById('imageInputContainer').style.display = 'block';
                    document.getElementById('soundInputContainer').style.display = 'block';
                }
            });
        });

        // Pastikan tampilan sudah sesuai saat halaman pertama kali dimuat
        window.addEventListener('load', function () {
            const notificationType = document.querySelector('input[name="notificationType"]:checked').value;

            // Menampilkan elemen berdasarkan pilihan yang terpilih saat load
            if (notificationType === 'text') {
                document.getElementById('textInputContainer').style.display = 'block';
                document.getElementById('tiitleText').style.display = 'block';
                document.getElementById('imageInputContainer').style.display = 'none';
                document.getElementById('soundInputContainer').style.display = 'none';
            } else if (notificationType === 'image') {
                document.getElementById('textInputContainer').style.display = 'none';
                document.getElementById('tiitleText').style.display = 'none';
                document.getElementById('imageInputContainer').style.display = 'block';
                document.getElementById('soundInputContainer').style.display = 'block';
            }
        });

        // Fungsi untuk menampilkan alert
        function setAlert(type, msg) {
            alertBox.classList.remove('success', 'error');
            alertBox.classList.add(type);
            alertBox.textContent = msg;
            alertBox.style.display = 'block';
        }

        // Fungsi untuk menghapus alert
        function clearAlert() {
            alertBox.style.display = 'none';
            alertBox.textContent = '';
            alertBox.classList.remove('success', 'error');
        }

        // Menangani klik "Pilih Semua" dan "Batal Pilih"
        selectAllBtn.addEventListener('click', () => {
            roomsList.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = true);
        });

        deselectAllBtn.addEventListener('click', () => {
            roomsList.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        });

        // Mencari kamar berdasarkan input
        searchInput.addEventListener('input', () => {
            const q = (searchInput.value || '').toLowerCase().trim();
            roomsList.querySelectorAll('.room-item').forEach(item => {
                const room = (item.getAttribute('data-room') || '').toLowerCase();
                const name = (item.getAttribute('data-name') || '').toLowerCase();
                const match = room.includes(q) || name.includes(q);
                item.style.display = match ? '' : 'none';
            });
        });

        // Menangani submit form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearAlert();

            // Validasi minimal client-side untuk tipe text
            const body = (form.querySelector('textarea[name="body"]').value || '').trim();
            if (document.querySelector('input[name="notificationType"]:checked').value == 'text') {
                if (!body) {
                    setAlert('error', 'Pesan (body) wajib diisi.');
                    return;
                }
            }

            // Cek jika tipe notifikasi "image" dan pastikan file image diupload
            if (document.querySelector('input[name="notificationType"]:checked').value == 'image') {
                const imageFile = document.getElementById('imageUpload').files[0];

                // Jika gambar tidak dipilih, tampilkan alert
                if (!imageFile) {
                    setAlert('error', 'Gambar wajib diupload.');
                    return;
                }
            }

            // Mengambil file suara (jika ada) dari file upload
            const soundFile = document.getElementById('soundUpload').files[0];

            // Buat FormData dan hanya tambahkan file suara jika ada
            const formData = new FormData(form);

            // Cek jika file suara tidak ada, hapus field soundUpload
            if (!soundFile) {
                formData.delete('soundUpload');
            }

            // Ambil URL suara dari dropdown (list view)
            const soundUrlFromDropdown = document.getElementById('soundEffect').value;

            // Jika suara dipilih dari dropdown, tambahkan URL suara ke FormData
            if (soundUrlFromDropdown) {
                formData.append('sound_url', soundUrlFromDropdown);
            }

            // Validasi minimal kamar yang dipilih
            const checked = roomsList.querySelectorAll('input[type="checkbox"]:checked');
            if (!checked.length) {
                setAlert('error', 'Pilih minimal 1 kamar.');
                return;
            }

            try {
                const res = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json().catch(() => null);
                if (!data) {
                    console.log(res);
                    setAlert('error', 'Respons server tidak valid.');
                    return;
                }

                if (data.status === 'success') {
                    setAlert('success', data.message || 'Berhasil mengirim popup.');
                    // optional: reset body saja
                    form.reset();

                    // Memastikan tipe notifikasi yang dipilih tetap sesuai setelah pengiriman
                    const notificationType = document.querySelector('input[name="notificationType"]:checked').value;
                    if (notificationType === 'text') {
                        document.getElementById('textInputContainer').style.display = 'block';
                        document.getElementById('tiitleText').style.display = 'block';
                        document.getElementById('imageInputContainer').style.display = 'none';
                        document.getElementById('soundInputContainer').style.display = 'none';
                    } else if (notificationType === 'image') {
                        document.getElementById('textInputContainer').style.display = 'none';
                        document.getElementById('tiitleText').style.display = 'none';
                        document.getElementById('imageInputContainer').style.display = 'block';
                        document.getElementById('soundInputContainer').style.display = 'block';
                    }
                } else {
                    setAlert('error', data.message || 'Gagal mengirim popup.');
                }
            } catch (err) {
                setAlert('error', 'Gagal menghubungi server.');
            }
        });
    })();
</script>