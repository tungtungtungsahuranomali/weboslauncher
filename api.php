<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

include 'config.php';
include 'functions.php';
include 'checkout_clear_helper.php';
include 'wa_helper.php';
header('Content-Type: application/json; charset=utf-8');

$db = init_db_connection();
if ($db === null) {
    http_response_code(503);
    echo json_encode(['STATUS' => 'FAIL', 'description' => 'Koneksi database gagal.', 'message' => 'Failed']);
    exit;
}

$action = $_GET['action'] ?? '';
$lang = $_GET['lang'] ?? 'id'; // Default bahasa Indonesia

function handleVHPAuth($db)
{
    if (!defined('VHP_USER') || !defined('VHP_PASS')) {
        $vhp_user = 'vhp_admin';
        $vhp_pass = 'PassHotelRahasia123!';
    }
    else {
        $vhp_user = VHP_USER;
        $vhp_pass = VHP_PASS;
    }
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($auth_header) || strpos($auth_header, 'Basic ') !== 0) {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            if ($_SERVER['PHP_AUTH_USER'] === $vhp_user && $_SERVER['PHP_AUTH_PW'] === $vhp_pass)
                return true;
        }
        http_response_code(401);
        header('WWW-Authenticate: Basic realm="VHP Integration"');
        echo json_encode(['STATUS' => 'FAIL', 'description' => 'Authorization required.', 'message' => 'Failed']);
        exit;
    }
    $base64_auth = substr($auth_header, 6);
    $decoded_auth = base64_decode($base64_auth);
    list($username, $password) = explode(':', $decoded_auth, 2);
    if ($username === $vhp_user && $password === $vhp_pass)
        return true;
    http_response_code(401);
    echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid credentials.', 'message' => 'Failed']);
    exit;
}

try {
    // === Integrasi VHP ===
    if (in_array($action, ['vhp_checkin', 'vhp_modifyguest', 'vhp_checkout'])) {
        handleVHPAuth($db);
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid JSON.', 'message' => 'Failed']);
            exit;
        }
        switch ($action) {
            case 'vhp_checkin':
                if ($action === 'vhp_checkin') {
                    handleVHPAuth($db);
                    $input = json_decode(file_get_contents('php://input'), true);

                    // Validasi input JSON
                    if (!is_array($input)) {
                        http_response_code(400);
                        echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid JSON.', 'message' => 'Failed']);
                        exit;
                    }

                    // Ambil data dari request
                    $roomNo = $input['roomNo'] ?? null;
                    $firstName = $input['firstName'] ?? '';
                    $lastName = $input['lastName'] ?? '';
                    $checkinDate = $input['checkinDate'] ?? null;
                    $checkoutDate = $input['checkoutDate'] ?? null; // Checkout date untuk check-in yang benar

                    // Validasi roomNo
                    if (!$roomNo) {
                        http_response_code(400);
                        echo json_encode(['STATUS' => 'FAIL', 'description' => 'Missing roomNo.', 'message' => 'Failed']);
                        exit;
                    }

                    // Format nama tamu
                    $fullName = trim("$firstName $lastName");
                    if (empty($fullName))
                        $fullName = "Room Guests of $roomNo";

                    // Format waktu check-in jika ada
                    $checkinTimeVal = 'NOW()';
                    if ($checkinDate) {
                        // Pastikan formatnya benar dengan DateTime
                        $dt = DateTime::createFromFormat('d/m/Y H:i:s', $checkinDate);
                        if ($dt) {
                            $checkinTimeVal = "'" . $dt->format('Y-m-d H:i:s') . "'";
                        }
                        else {
                            http_response_code(400);
                            echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid checkinDate format. Please use d/m/Y H:i:s.', 'message' => 'Failed']);
                            exit;
                        }
                    }

                    // Format checkoutDate jika ada
                    $checkoutTimeVal = 'NULL';
                    if ($checkoutDate) {
                        // Pastikan formatnya benar dengan DateTime
                        $dtCheckout = DateTime::createFromFormat('d/m/Y H:i:s', $checkoutDate);
                        if ($dtCheckout) {
                            $checkoutTimeVal = "'" . $dtCheckout->format('Y-m-d H:i:s') . "'";
                        }
                        else {
                            http_response_code(400);
                            echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid checkoutDate format. Please use d/m/Y H:i:s.', 'message' => 'Failed']);
                            exit;
                        }
                    }

                    // Update status check-out untuk room yang sudah ter-check-in
                    $db->prepare("UPDATE guest_checkin SET status='checked_out', checkout_time=NOW() WHERE room_number = ? AND status='checked_in'")->execute([$roomNo]);

                    // SQL untuk insert check-in tamu baru
                    $sql = "INSERT INTO guest_checkin (room_number, guest_name, checkin_time, status, checkout_time) VALUES (?, ?, " . ($checkinTimeVal === 'NOW()' ? 'NOW()' : '?') . ", 'checked_in', $checkoutTimeVal)";
                    $params = [$roomNo, $fullName];
                    if ($checkinTimeVal !== 'NOW()')
                        $params[] = trim($checkinTimeVal, "'");

                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);

                    echo json_encode(['STATUS' => 'SUCCESS', 'description' => 'Room Checked In', 'message' => 'Success']);
                    exit;
                }

            case 'vhp_modifyguest':

                if ($action === 'vhp_modifyguest') {
                    handleVHPAuth($db);
                    $input = json_decode(file_get_contents('php://input'), true);

                    // Validasi input JSON
                    if (!is_array($input)) {
                        http_response_code(400);
                        echo json_encode(['STATUS' => 'FAIL', 'description' => 'Invalid JSON.', 'message' => 'Failed']);
                        exit;
                    }

                    // Ambil data dari request
                    $roomNo = $input['roomNo'] ?? null;
                    $firstName = $input['firstName'] ?? '';
                    $lastName = $input['lastName'] ?? '';
                    $modifyDate = $input['modifyDate'] ?? null; // Modify date untuk memperpanjang masa inap

                    // Validasi roomNo
                    if (!$roomNo) {
                        http_response_code(400);
                        echo json_encode(['STATUS' => 'FAIL', 'description' => 'Missing roomNo.', 'message' => 'Failed']);
                        exit;
                    }

                    // Format nama tamu
                    $fullName = trim("$firstName $lastName");
                    if (empty($fullName))
                        $fullName = "Room Guests of $roomNo";

                    // Format modify date jika ada
                    $modifyTimeVal = 'NOW()';
                    if ($modifyDate) {
                        $dt = DateTime::createFromFormat('d/m/Y H:i:s', $modifyDate);
                        if (!$dt) {
                            http_response_code(400);
                            echo json_encode([
                                'STATUS' => 'FAIL',
                                'description' => 'Invalid modifyDate format. Please use d/m/Y H:i:s.',
                                'message' => 'Failed'
                            ]);
                            exit;
                        }

                        $modifyTimeVal = $dt->format('Y-m-d H:i:s');
                    }
                    else {
                        $modifyTimeVal = null;
                    }

                    // Update status tamu yang sudah ada untuk room yang dimodifikasi
                    $sql = "UPDATE guest_checkin SET guest_name = ?, checkout_time = ? WHERE room_number = ? AND status = 'checked_in'";
                    $params = [$fullName, $modifyTimeVal, $roomNo];

                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);

                    echo json_encode(['STATUS' => 'SUCCESS', 'description' => 'Guest Information Modified', 'message' => 'Success']);
                    exit;
                }

            case 'vhp_checkout':
                $roomNo = $input['roomNo'] ?? null;
                if (!$roomNo) {
                    http_response_code(400);
                    echo json_encode(['STATUS' => 'FAIL', 'description' => 'Missing roomNo.', 'message' => 'Failed']);
                    exit;
                }
                $db->beginTransaction();
                $db->prepare("UPDATE guest_checkin SET status = 'checked_out', checkout_time = NOW() WHERE room_number = ? AND status = 'checked_in'")->execute([$roomNo]);
                $db->prepare("DELETE FROM hotel_orders WHERE room_number = ?")->execute([$roomNo]);
                $db->prepare("DELETE FROM amenity_requests WHERE room_number = ?")->execute([$roomNo]);
                $db->prepare("DELETE FROM transportation_requests WHERE room_number = ?")->execute([$roomNo]);
                $db->commit();

                // AUTO CLEAR: Kirim perintah ADB clear ke TV
                $clearResult = clearTVDataByRoom($db, $roomNo);

                echo json_encode([
                    'STATUS' => 'SUCCESS',
                    'description' => 'Room Checked Out',
                    'message' => 'Success',
                    'clear_data' => $clearResult['status']
                ]);
                break;
        }
        exit;
    }

    // Endpoint untuk mengambil notifikasi terbaru berdasarkan device_id
    if ($action === 'getNotifications') {
        header('Content-Type: application/json; charset=utf-8');

        $deviceId = trim((string)($_GET['device_id'] ?? ''));
        if ($deviceId === '') {
            http_response_code(400);
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode(['status' => 'error', 'message' => 'device_id required']);
            exit;
        }

        // Hard limit agar aman
        if (strlen($deviceId) > 128) {
            http_response_code(400);
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode(['status' => 'error', 'message' => 'device_id too long']);
            exit;
        }

        try {
            // Wajib InnoDB agar FOR UPDATE efektif
            $db->beginTransaction();

            // Ambil 1 notifikasi pending untuk device ini, lock row agar tidak dobel
            $stmt = $db->prepare("
                SELECT id, title, body, sound_url, image_url
                FROM popup_notifications
                WHERE device_id = ?
                AND status = 'pending'
                AND (expires_at IS NULL OR expires_at > NOW())
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY created_at ASC
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->execute([$deviceId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $db->commit();
                if (ob_get_length()) {
                    ob_clean();
                }
                echo json_encode(['status' => 'success', 'notification' => null]);
                exit;
            }

            // Mark delivered supaya tidak muncul lagi di polling berikutnya
            $upd = $db->prepare("
                UPDATE popup_notifications
                SET status = 'delivered', delivered_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([(int)$row['id']]);

            $db->commit();

            $title = trim((string)($row['title'] ?? ''));
            $body = trim((string)($row['body'] ?? ''));
            $sound_url = trim((string)($row['sound_url'] ?? ''));
            $image_url = trim((string)($row['image_url'] ?? ''));

            // Kirim sebagai object agar index.php bisa isi <h2> dan body terpisah
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode([
                'status' => 'success',
                'notification' => [
                    'title' => $title, // boleh kosong
                    'body' => $body, // wajib ada isi kalau memang ada notif
                    'sound_url' => $sound_url, // wajib ada isi kalau memang ada notif
                    'image_url' => $image_url, // wajib ada isi kalau memang ada notif
                ]
            ]);
            exit;

        }
        catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            http_response_code(500);
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
            exit;
        }
    }


    // === API: Register Device IP (dipanggil oleh APK saat boot) ===
    if ($action === 'registerDeviceIp') {
        $input = json_decode(file_get_contents('php://input'), true);
        $deviceId = $input['device_id'] ?? ($_POST['device_id'] ?? '');
        $deviceIp = $input['device_ip'] ?? ($_POST['device_ip'] ?? '');

        if (empty($deviceId) || empty($deviceIp)) {
            echo json_encode(['status' => 'error', 'message' => 'device_id dan device_ip wajib diisi']);
            exit;
        }

        try {
            $stmt = $db->prepare("UPDATE managed_devices SET device_ip = ?, last_seen = NOW() WHERE device_id = ?");
            $stmt->execute([$deviceIp, $deviceId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'IP registered', 'device_ip' => $deviceIp]);
            }
            else {
                echo json_encode(['status' => 'error', 'message' => 'Device not found']);
            }
        }
        catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === API: Check Pending Clear (dipanggil APK saat boot) ===
    if ($action === 'checkPendingClear') {
        $deviceId = trim($_GET['device_id'] ?? '');
        if (empty($deviceId)) {
            echo json_encode(['status' => 'error', 'message' => 'device_id required']);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT pending_clear FROM managed_devices WHERE device_id = ?");
            $stmt->execute([$deviceId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                echo json_encode(['status' => 'success', 'pending_clear' => (bool)$row['pending_clear']]);
            }
            else {
                echo json_encode(['status' => 'error', 'message' => 'Device not found']);
            }
        }
        catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === API: Confirm Clear (dipanggil APK setelah clear berhasil) ===
    if ($action === 'confirmClear') {
        $input = json_decode(file_get_contents('php://input'), true);
        $deviceId = $input['device_id'] ?? ($_POST['device_id'] ?? '');

        if (empty($deviceId)) {
            echo json_encode(['status' => 'error', 'message' => 'device_id required']);
            exit;
        }

        try {
            $stmt = $db->prepare("UPDATE managed_devices SET pending_clear = 0 WHERE device_id = ?");
            $stmt->execute([$deviceId]);
            echo json_encode(['status' => 'success', 'message' => 'Pending clear reset']);
        }
        catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === API: Get Server Config (dipanggil oleh APK setiap 60 detik) ===
    if ($action === 'getServerConfig') {
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('remote_server_url', 'remote_ws_port', 'remote_config_version', 'scheduled_clear_enabled', 'scheduled_clear_time')");
            $config = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $config[$row['setting_key']] = $row['setting_value'];
            }

            $response = [
                'status' => 'success',
                'has_config' => !empty($config['remote_server_url']),
                'scheduled_clear_enabled' => ($config['scheduled_clear_enabled'] ?? '0') === '1',
                'scheduled_clear_time' => $config['scheduled_clear_time'] ?? ''
            ];

            if (!empty($config['remote_server_url'])) {
                $response['server_url'] = $config['remote_server_url'];
                $response['ws_port'] = (int)($config['remote_ws_port'] ?? 8080);
                $response['config_version'] = $config['remote_config_version'] ?? '0';
            }

            echo json_encode($response);
        }
        catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // === API: Get IPTV Channels (format hybrid: manual + playlists) ===
    if ($action === 'get_channels') {
        $manual_channels = [];
        $playlists = [];
        $categoryTextColor = '#FFFFFF';
        $channelTextColor = '#EDEDED';

        try {
            // Channel manual (status enabled)
            $stmt = $db->query("SELECT * FROM channels WHERE status='enabled' ORDER BY lcn ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $manual_channels[] = [
                    'id' => $row['id'],
                    'name' => $row['title'],
                    'url' => $row['stream_url'],
                    'logo' => $row['logo_url'] ?? '',
                    'category' => $row['category'] ?? 'Umum'
                ];
            }

            // Playlist M3U external
            $stmt2 = $db->query("SELECT * FROM playlists");
            while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $playlists[] = [
                    'source_name' => $row['name'],
                    'url' => $row['url'],
                    'default_category' => $row['default_category'] ?? 'Playlist'
                ];
            }

            $rawCategoryColor = strtoupper(trim((string)($db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_category_text_color'")->fetchColumn() ?? '')));
            $rawChannelColor = strtoupper(trim((string)($db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_channel_text_color'")->fetchColumn() ?? '')));
            if (preg_match('/^#[0-9A-F]{6}$/', $rawCategoryColor)) {
                $categoryTextColor = $rawCategoryColor;
            }
            if (preg_match('/^#[0-9A-F]{6}$/', $rawChannelColor)) {
                $channelTextColor = $rawChannelColor;
            }
        }
        catch (Exception $e) {
        // Tables might not exist yet
        }

        echo json_encode([
            'manual' => $manual_channels,
            'playlists' => $playlists,
            'category_text_color' => $categoryTextColor,
            'channel_text_color' => $channelTextColor
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // === API Frontend ===
    switch ($action) {
        case 'checkRegistration':
            $device_id = strtoupper(trim($_GET['device_id'] ?? ''));
            if (!$device_id)
                throw new Exception('Device ID kosong.');
            $stmt = $db->prepare("SELECT COUNT(*) FROM managed_devices WHERE device_id=?");
            $stmt->execute([$device_id]);
            echo json_encode(['status' => 'success', 'is_registered' => $stmt->fetchColumn() > 0]);
            break;

        case 'getStatus':
            $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key='launcher_enabled'");
            $stmt->execute();
            echo json_encode(['status' => 'success', 'is_launcher_enabled' => (bool)($stmt->fetchColumn() ?? 0)]);
            break;

        case 'getGuestInfo':
            $device_id = strtoupper(trim($_GET['device_id'] ?? ''));
            $stmt_room = $db->prepare("SELECT room_number FROM managed_devices WHERE device_id=?");
            $stmt_room->execute([$device_id]);
            $room_number = $stmt_room->fetchColumn();
            if (!$room_number)
                throw new Exception('Perangkat tidak terdaftar.');
            $stmt_guest = $db->prepare("SELECT guest_name FROM guest_checkin WHERE room_number = ? AND status = 'checked_in' ORDER BY checkin_time DESC LIMIT 1");
            $stmt_guest->execute([$room_number]);
            $guest_name = $stmt_guest->fetchColumn() ?: "Guest";
            echo json_encode(['status' => 'success', 'data' => ['guest_name' => $guest_name, 'room_number' => $room_number]]);
            break;

        case 'getMarqueeText':
            $text = $db->query("SELECT content FROM system_marquee WHERE id=1")->fetchColumn() ?: '';
            echo json_encode(['status' => 'success', 'text' => $text]);
            break;

        case 'getAppVisibility':
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(app_name_en, ""), app_name) as app_name' : 'app_name';

            $apps = $db->query("SELECT app_key, $nameField, icon_path, android_package, is_visible FROM system_apps WHERE is_visible=1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($apps as &$a)
                $a['icon_path'] = get_full_url($a['icon_path']);
            echo json_encode(['status' => 'success', 'apps' => $apps]);
            break;

        case 'getKatFacilities':
            $rows = $db->query("SELECT id_kat_facilities, nm_kat_facilities, foto_kat_facilities FROM kat_facilities ORDER BY id_kat_facilities ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['foto_kat_facilities'] = get_full_url($r['foto_kat_facilities']);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getFacilities':
            // Bilingual & Show Description Logic
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(f.name_en, ""), f.name) as name' : 'f.name';
            $descField = ($lang === 'en') ? 'COALESCE(NULLIF(f.description_en, ""), f.description) as description' : 'f.description';

            $where = "f.is_active=1";
            $params = [];
            if (!empty($_GET['kat_id'])) {
                $where .= " AND f.id_kat_facilities=?";
                $params[] = (int)$_GET['kat_id'];
            }

            $sql = "SELECT f.id, f.id_kat_facilities, $nameField, $descField, f.icon_path, f.show_description FROM hotel_facilities f WHERE $where ORDER BY f.id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getKatInfo':
            $rows = $db->query("SELECT id_kat_info, nm_kat_info, foto_kat_info FROM kat_info ORDER BY id_kat_info ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['foto_kat_info'] = get_full_url($r['foto_kat_info']);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getInfo':
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(title_en, ""), title) as title' : 'title';
            $descField = ($lang === 'en') ? 'COALESCE(NULLIF(description_en, ""), description) as description' : 'description';

            $where = "1=1";
            $params = [];
            if (!empty($_GET['kat_id'])) {
                $where .= " AND id_kat_info=?";
                $params[] = (int)$_GET['kat_id'];
            }

            $sql = "SELECT id, id_kat_info, $nameField, $descField, icon_path, show_description FROM hotel_info WHERE $where ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getKatGeneralInfo':
            $rows = $db->query("SELECT id_kat_general_info, nm_kat_general_info, foto_kat_general_info FROM kat_general_info ORDER BY id_kat_general_info ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['foto_kat_general_info'] = get_full_url($r['foto_kat_general_info']);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getGeneralInfo':
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(title_en, ""), title) as title' : 'title';
            $descField = ($lang === 'en') ? 'COALESCE(NULLIF(description_en, ""), description) as description' : 'description';

            $where = "is_active=1";
            $params = [];
            if (!empty($_GET['kat_id'])) {
                $where .= " AND id_kat_general_info=?";
                $params[] = (int)$_GET['kat_id'];
            }

            $sql = "SELECT id, id_kat_general_info, $nameField, $descField, icon_path, show_description FROM general_info WHERE $where ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getKatPromotion':
            $rows = $db->query("SELECT id_kat_promotion, nm_kat_promotion, foto_kat_promotion FROM kat_promotion ORDER BY id_kat_promotion ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['foto_kat_promotion'] = get_full_url($r['foto_kat_promotion']);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getPromotion':
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(name_en, ""), name) as name' : 'name';
            $descField = ($lang === 'en') ? 'COALESCE(NULLIF(description_en, ""), description) as description' : 'description';

            $where = "is_active=1";
            $params = [];
            if (!empty($_GET['kat_id'])) {
                $where .= " AND id_kat_promotion=?";
                $params[] = (int)$_GET['kat_id'];
            }

            $sql = "SELECT id, id_kat_promotion, $nameField, $descField, icon_path, show_description FROM promotion WHERE $where ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getAmenities':
            // Bilingual
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(name_en, ""), name) as name' : 'name';
            $descField = ($lang === 'en') ? 'COALESCE(NULLIF(description_en, ""), description) as description' : 'description';
            $rows = $db->query("SELECT id, $nameField, $descField, icon_path FROM room_amenities ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            // Baca flag apakah card daftar permintaan ditampilkan di APK
            $reqCardEnabled = 1;
            try {
                $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'amenities_request_card_enabled' LIMIT 1");
                $stmt->execute();
                $val = $stmt->fetchColumn();
                if ($val !== false) {
                    $reqCardEnabled = (int)$val;
                }
            } catch (Exception $e) {
            }
            echo json_encode([
                'status' => 'success',
                'request_card_enabled' => (bool)$reqCardEnabled,
                'data' => $rows
            ]);
            break;

        case 'getKatDining':
            $rows = $db->query("SELECT id_kat_dining, nm_kat_dining, foto_kat_dining FROM kat_dining ORDER BY id_kat_dining ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['foto_kat_dining'] = get_full_url($r['foto_kat_dining']);
            }
            echo json_encode(['status' => 'success', 'data' => $rows]);
            break;

        case 'getDining':
            // Bilingual
            $nameField = ($lang === 'en') ? 'COALESCE(NULLIF(name_en, ""), name) as name' : 'name';
            
            $where = "status='active'";
            $params = [];
            if (!empty($_GET['kat_id'])) {
                $where .= " AND id_kat_dining=?";
                $params[] = (int)$_GET['kat_id'];
            }
            
            $sql = "SELECT id, id_kat_dining, $nameField, price, image_url AS icon_path, status FROM dining_menu WHERE $where ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['icon_path'] = get_full_url($r['icon_path']);
            // Baca flag apakah card keranjang ditampilkan di APK
            $cartEnabled = 1;
            try {
                $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'dining_cart_enabled' LIMIT 1");
                $stmt->execute();
                $val = $stmt->fetchColumn();
                if ($val !== false) {
                    $cartEnabled = (int)$val;
                }
            } catch (Exception $e) {
            }
            echo json_encode([
                'status' => 'success',
                'cart_enabled' => (bool)$cartEnabled,
                'data' => $rows
            ]);
            break;

        case 'submitDiningOrder':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['items'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
                break;
            }
            $guest = $input['guest_name'] ?? 'Guest';
            $room = $input['room_number'] ?? '-';
            $items = $input['items'];
            $total = 0;
            foreach ($items as $it)
                $total += ($it['qty'] ?? 0) * ($it['price'] ?? 0);
            $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("INSERT INTO hotel_orders(room_number,guest_name,items,total_price,status,ordered_at)VALUES(?,?,?,?,'Pending',NOW())");
            $stmt->execute([$room, $guest, $json, $total]);
            echo json_encode(['status' => 'success', 'message' => 'Order Saved']);

            // Kirim notifikasi WhatsApp
            try {
                $itemList = implode("\n", array_map(fn($i) => "  - " . ($i['name'] ?? 'Item') . " x" . ($i['qty'] ?? 1), $items));
                $waMsg = "\xF0\x9F\x8D\xBD *Pesanan Dining Baru*\n\n\xF0\x9F\x91\xA4 Tamu: {$guest}\n\xF0\x9F\x9A\xAA Kamar: {$room}\n\n\xF0\x9F\x93\x8B Detail Pesanan:\n{$itemList}\n\n\xF0\x9F\x92\xB0 Total: Rp " . number_format($total, 0, ',', '.');
                sendWhatsAppNotification($db, $waMsg, 'dining');
            }
            catch (Exception $e) { /* WA notification optional */
            }
            break;

        case 'submitAmenityRequest':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['items'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
                break;
            }
            $guest = $input['guest_name'] ?? 'Guest';
            $room = $input['room_number'] ?? '-';
            $items = $input['items'];
            $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt = $db->prepare("INSERT INTO amenity_requests(room_number,guest_name,items,status,requested_at)VALUES(?,?,?,'Pending',NOW())");
            $stmt->execute([$room, $guest, $json]);
            echo json_encode(['status' => 'success', 'message' => 'Request Saved']);

            // Kirim notifikasi WhatsApp
            try {
                $itemList = implode("\n", array_map(fn($i) => "  - " . ($i['name'] ?? 'Item') . " x" . ($i['qty'] ?? 1), $items));
                $waMsg = "\xF0\x9F\xA7\xB4 *Permintaan Amenities Baru*\n\n\xF0\x9F\x91\xA4 Tamu: {$guest}\n\xF0\x9F\x9A\xAA Kamar: {$room}\n\n\xF0\x9F\x93\x8B Detail Permintaan:\n{$itemList}";
                sendWhatsAppNotification($db, $waMsg, 'amenities');
            }
            catch (Exception $e) { /* WA notification optional */
            }
            break;

        case 'submitTransportRequest':
            $input = json_decode(file_get_contents('php://input'), true);
            $guest_name = trim($input['guest_name'] ?? 'Guest');
            $room_number = trim($input['room_number'] ?? '-');
            $pickup_point = trim($input['pickup_point'] ?? 'Kamar ' . $room_number);
            $destination = trim($input['destination'] ?? 'By Request');
            $num_passengers = (int)($input['num_passengers'] ?? 1);
            $preferred_time = trim($input['preferred_time'] ?? 'NOW');
            $notes = trim($input['notes'] ?? 'Dari Kamar/From Hotel Room');

            $stmt = $db->prepare("INSERT INTO transportation_requests (room_number, guest_name, pickup_point, destination, num_passengers, preferred_time, notes, status, requested_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
            $stmt->execute([$room_number, $guest_name, $pickup_point, $destination, $num_passengers, $preferred_time, $notes]);

            try {
                $timeLabel = ($preferred_time === 'NOW') ? 'Sekarang' : $preferred_time;
                $waMsg = "\xF0\x9F\x9A\x90 *Permintaan Transportasi Baru*\n\n\xF0\x9F\x91\xA4 Tamu: {$guest_name}\n\xF0\x9F\x9A\xAA Kamar: {$room_number}\n\xF0\x9F\x93\x8D Pick-up: {$pickup_point}\n\xF0\x9F\x9A\x8F Tujuan: {$destination}\n\xF0\x9F\x91\xA5 Penumpang: {$num_passengers}\n\xE2\x8F\xB0 Waktu: {$timeLabel}\n\xF0\x9F\x93\x9D Catatan: {$notes}";
                sendWhatsAppNotification($db, $waMsg, 'transportation');
            } catch (Exception $e) { /* WA optional */ }

            echo json_encode(['status' => 'success', 'message' => 'Permintaan transportasi terkirim.']);
            break;

        case 'getTransportDestinations':
            try {
                $stmt = $db->query("SELECT id, name FROM transport_destinations ORDER BY sort_order ASC");
                $dests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['status' => 'success', 'data' => $dests]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'getSplash':
            $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key='splash_enabled'");
            $stmt->execute();
            if (!((int)($stmt->fetchColumn() ?? 0))) {
                echo json_encode(['status' => 'disabled']);
                break;
            }
            $metaFile = __DIR__ . '/uploads/flashscreen/metadata.json';
            if (!file_exists($metaFile)) {
                echo json_encode(['status' => 'error']);
                break;
            }
            $meta = json_decode(file_get_contents($metaFile), true);
            $url = $meta['url'] ?? '';
            if (!empty($url) && strpos($url, 'http') !== 0)
                $url = BASE_URL . ltrim($url, '/');
            
            // Append version timestamp
            if (!empty($meta['version'])) {
                $ts = urlencode($meta['version']);
                $url .= (strpos($url, '?') === false ? '?v=' : '&v=') . $ts;
            }

            echo json_encode(['status' => 'success', 'url' => $url]);
            break;

        case 'getAssetManifest':
            // Manifest semua asset yang perlu di-cache oleh TV
            $manifest = ['status' => 'success', 'assets' => []];

            // 1. Flashscreen video
            $metaFile = __DIR__ . '/uploads/flashscreen/metadata.json';
            if (file_exists($metaFile)) {
                $meta = json_decode(file_get_contents($metaFile), true);
                $fUrl = $meta['url'] ?? '';
                if (!empty($fUrl) && strpos($fUrl, 'http') !== 0)
                    $fUrl = BASE_URL . ltrim($fUrl, '/');

                // Append version timestamp
                if (!empty($meta['version'])) {
                    $ts = urlencode($meta['version']);
                    $fUrl .= (strpos($fUrl, '?') === false ? '?v=' : '&v=') . $ts;
                }

                $manifest['assets'][] = [
                    'type' => 'flashscreen',
                    'id' => 'flashscreen',
                    'url' => $fUrl,
                    'updated_at' => $meta['version'] ?? ''
                ];
            }

            // 2. Dining images
            $dRows = $db->query("SELECT id, image_url FROM dining_menu WHERE status='active' AND image_url != '' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dRows as $dr) {
                $manifest['assets'][] = [
                    'type' => 'dining',
                    'id' => 'dining_' . $dr['id'],
                    'url' => get_full_url($dr['image_url']),
                    'updated_at' => '' // timestamp akan ditambahkan nanti jika perlu
                ];
            }

            // 3. Amenities images
            $aRows = $db->query("SELECT id, icon_path FROM room_amenities WHERE icon_path != '' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($aRows as $ar) {
                $manifest['assets'][] = [
                    'type' => 'amenities',
                    'id' => 'amenities_' . $ar['id'],
                    'url' => get_full_url($ar['icon_path']),
                    'updated_at' => ''
                ];
            }

            // 4. Facilities images
            $fRows = $db->query("SELECT id, icon_path FROM hotel_facilities WHERE is_active=1 AND icon_path != '' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($fRows as $fr) {
                $manifest['assets'][] = [
                    'type' => 'facilities',
                    'id' => 'facilities_' . $fr['id'],
                    'url' => get_full_url($fr['icon_path']),
                    'updated_at' => ''
                ];
            }

            // 5. Information images
            $iRows = $db->query("SELECT id, icon_path FROM hotel_info WHERE icon_path != '' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($iRows as $ir) {
                $manifest['assets'][] = [
                    'type' => 'information',
                    'id' => 'information_' . $ir['id'],
                    'url' => get_full_url($ir['icon_path']),
                    'updated_at' => ''
                ];
            }

            // 6. Home background
            $homeBg = $db->query("SELECT setting_value FROM global_settings WHERE setting_key='launcher_home_bg'")->fetchColumn() ?: '';
            if (!empty($homeBg)) {
                $manifest['assets'][] = [
                    'type' => 'home_bg',
                    'id' => 'home_bg',
                    'url' => get_full_url($homeBg),
                    'updated_at' => ''
                ];
            }

            // 7. Greeting card image
            $greetingImg = get_setting('custom_greeting_image') ?? '';
            if (!empty($greetingImg)) {
                $manifest['assets'][] = [
                    'type' => 'greeting_bg',
                    'id' => 'greeting_bg',
                    'url' => get_full_url($greetingImg),
                    'updated_at' => ''
                ];
            }

            echo json_encode($manifest);
            break;

        case 'getHomeBackground':
            $bg = $db->query("SELECT setting_value FROM global_settings WHERE setting_key='launcher_home_bg'")->fetchColumn() ?: '';
            echo json_encode(['status' => 'success', 'background_url' => get_full_url($bg)]);
            break;

        case 'getIptvBackground':
            // Background khusus untuk aplikasi IPTV player (diatur via CMS: admin.php?page=iptv)
            $bg = $db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_bg'")->fetchColumn() ?: '';
            if (empty($bg)) {
                // fallback agar tetap tampil walau belum set khusus IPTV
                $bg = $db->query("SELECT setting_value FROM global_settings WHERE setting_key='launcher_home_bg'")->fetchColumn() ?: '';
            }
            echo json_encode(['status' => 'success', 'background_url' => get_full_url($bg)]);
            break;

        case 'getWeather':
            $city = "Jakarta,ID";
            $apiKey = "acb2744e5516a24f85e86a97e73f9427";
            $apiLang = ($lang === 'en') ? 'en' : 'id';
            $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric&lang={$apiLang}";
            $fallback = ['temp' => 28, 'description' => 'Cerah Berawan', 'icon' => '02d'];
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 2]]);
                $json = @file_get_contents($url, false, $ctx);
                if (!$json)
                    throw new Exception("Error");
                $data = json_decode($json, true);
                echo json_encode(['status' => 'success', 'data' => ['temp' => (int)$data['main']['temp'], 'description' => ucwords($data['weather'][0]['description']), 'icon' => $data['weather'][0]['icon']]]);
            }
            catch (Throwable $e) {
                echo json_encode(['status' => 'success', 'data' => $fallback]);
            }
            break;

        case 'getCustomGreeting':
            $lang = $_GET['lang'] ?? 'id';

            if ($lang === 'en') {
                $title = get_setting('custom_greeting_title_en') ?: get_setting('custom_greeting_title') ?: 'Welcome';
                $content = get_setting('custom_welcome_greeting_en') ?: get_setting('custom_welcome_greeting') ?: 'Welcome to our Hotel';
                $title_enabled = (int) (get_setting('greeting_title_en_enabled') ?? 1);
                $content_enabled = (int) (get_setting('greeting_content_en_enabled') ?? 1);
            }
            else {
                $title = get_setting('custom_greeting_title') ?: 'Selamat Datang';
                $content = get_setting('custom_welcome_greeting') ?: 'Selamat datang di Hotel kami';
                $title_enabled = (int) (get_setting('greeting_title_id_enabled') ?? 1);
                $content_enabled = (int) (get_setting('greeting_content_id_enabled') ?? 1);
            }

            $title_color = get_setting('greeting_title_color') ?: '#000000';
            $content_color = get_setting('greeting_content_color') ?: '#000000';
            $btn_color = get_setting('greeting_btn_color') ?: '#facc15';
            $btn_text_color = get_setting('greeting_btn_text_color') ?: '#000000';

            $image = get_setting('custom_greeting_image') ?? 'img/hotel3.png';
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'title' => htmlspecialchars_decode($title),
                    'content' => htmlspecialchars_decode($content),
                    'image' => get_full_url($image),
                    'title_enabled' => (bool)$title_enabled,
                    'content_enabled' => (bool)$content_enabled,
                    'title_color' => $title_color,
                    'content_color' => $content_color,
                    'btn_color' => $btn_color,
                    'btn_text_color' => $btn_text_color
                ]
            ]);
            break;

        case 'pushUpdate':
            // Push update ke semua TV (set version timestamp baru agar TV mendeteksi update)
            $metaFile = __DIR__ . '/uploads/update/metadata.json';
            if (!file_exists($metaFile)) {
                echo json_encode(['status' => 'error', 'message' => 'Belum ada file update yang di-upload.']);
                break;
            }
            $meta = json_decode(file_get_contents($metaFile), true);
            if (!$meta) {
                echo json_encode(['status' => 'error', 'message' => 'Metadata invalid.']);
                break;
            }
            // Update timestamp agar TV polling mendeteksi perubahan
            $meta['pushed_at'] = date('Y-m-d H:i:s');
            file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
            echo json_encode(['status' => 'success', 'message' => 'Update berhasil di-push! Semua TV akan auto-update dalam 5 menit.']);
            break;

        case 'registerDeviceByCode':
            $input = json_decode(file_get_contents('php://input'), true);
            $device_id = strtoupper(trim($input['device_id'] ?? ($_POST['device_id'] ?? '')));
            if (empty($device_id)) {
                echo json_encode(['status' => 'error', 'message' => 'device_id wajib diisi']);
                break;
            }
            $stmt = $db->prepare("INSERT INTO managed_devices (device_id, device_name, room_number, registered_at, last_seen) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
            $short = substr($device_id, 0, 10);
            $stmt->execute([$device_id, $device_id, $short]);
            echo json_encode(['status' => 'success', 'message' => 'Device registered', 'device_id' => $device_id]);
            break;

        case 'updateDeviceInfo':
            $input = json_decode(file_get_contents('php://input'), true);
            $device_id = strtoupper(trim($input['device_id'] ?? ($_POST['device_id'] ?? '')));
            $device_name = trim($input['device_name'] ?? ($_POST['device_name'] ?? ''));
            $room_number = trim($input['room_number'] ?? ($_POST['room_number'] ?? ''));
            $unit_id = (int)($input['unit_id'] ?? ($_POST['unit_id'] ?? 0));
            if (empty($device_id)) {
                echo json_encode(['status' => 'error', 'message' => 'device_id wajib diisi']);
                break;
            }
            $stmt = $db->prepare("UPDATE managed_devices SET device_name = ?, room_number = ?, unit_id = ? WHERE device_id = ?");
            $stmt->execute([$device_name ?: $device_id, $room_number ?: $device_id, $unit_id ?: null, $device_id]);
            echo json_encode(['status' => 'success', 'message' => 'Device info updated']);
            break;

        default:
            throw new Exception('Invalid Action');
    }

}
catch (Throwable $e) {
    if (isset($db) && $db->inTransaction())
        $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>