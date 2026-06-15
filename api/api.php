<?php
// API utama untuk launcher (checkRegistration, status, guest, marquee, apps, version)

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$db = init_db_connection();
if ($db === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal'
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // 1) Cek registrasi perangkat
        case 'checkRegistration':
            $device_id = trim($_GET['device_id'] ?? '');
            if ($device_id === '') {
                throw new Exception('Device ID tidak ada');
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM managed_devices WHERE device_id = ?");
            $stmt->execute([$device_id]);
            $is_registered = $stmt->fetchColumn() > 0;

            echo json_encode([
                'status' => 'success',
                'is_registered' => $is_registered,
            ]);
            break;

        // 2) Status global launcher (enable/disable)
        case 'getStatus':
            $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'launcher_enabled'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $enabled = $row ? (bool) $row['setting_value'] : false;

            echo json_encode([
                'status' => 'success',
                'is_launcher_enabled' => $enabled,
            ]);
            break;

        // 3) Info tamu berdasarkan device_id
        case 'getGuestInfo':
            $device_id = trim($_GET['device_id'] ?? '');
            if ($device_id === '') {
                throw new Exception('Device ID tidak ada');
            }

            // cari room number dari managed_devices
            $stmt = $db->prepare("SELECT room_number FROM managed_devices WHERE device_id = ?");
            $stmt->execute([$device_id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                throw new Exception('Perangkat belum terdaftar');
            }

            $room_number = $room['room_number'];

            // sementara: dummy guest name (nanti bisa diambil dari PMS)
            $guest_name = 'Guest';
            if ($room_number === '87') {
                $guest_name = 'Mr. Ogie';
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'guest_name' => $guest_name,
                    'room_number' => $room_number,
                ],
            ]);
            break;

        // 4) Running text (marquee bawah layar)
        case 'getMarqueeText':
            $stmt = $db->query("SELECT content FROM system_marquee WHERE id = 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $text = $row ? $row['content'] : '';

            echo json_encode([
                'status' => 'success',
                'text' => $text,
            ]);
            break;

        // 5) Visibility & ikon aplikasi entertainment
        case 'getAppVisibility':
            $stmt = $db->query("
                SELECT app_key, app_name, icon_path, android_package, is_visible
                FROM system_apps
                WHERE is_visible = 1
                ORDER BY sort_order ASC
            ");
            $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // normalisasi URL icon
            foreach ($apps as &$app) {
                if (!empty($app['icon_path']) && !preg_match('~^https?://~', $app['icon_path'])) {
                    $app['icon_path'] = 'https://takeoff.web.id/' . ltrim($app['icon_path'], '/');
                }
            }

            echo json_encode([
                'status' => 'success',
                'apps' => $apps,
            ]);
            break;

        // 6) Versi launcher (untuk tombol UPDATE di panel admin)
        case 'getAppVersion':
            $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'launcher_version'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $version = $row ? $row['setting_value'] : '1.0.0';

            echo json_encode([
                'status' => 'success',
                'version' => $version,
            ]);
            break;

        default:
            throw new Exception('Aksi tidak dikenal');
    }

} catch (PDOException $e) {
    log_error('API PDOException: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Kesalahan database',
    ]);
} catch (Exception $e) {
    log_error('API Exception: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
}