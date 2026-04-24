<?php
require_once __DIR__ . "/../config.php";  // akses DB utama
require_once __DIR__ . "/config.php";     // konfigurasi ADB & CLEAR_APPS (fallback)
require_once __DIR__ . "/adb_helper.php";
require_once __DIR__ . "/device_helper.php";

header("Content-Type: application/json");

// Koneksi database untuk mengambil unit & clear_script berdasarkan device
$db = init_db_connection();
if ($db === null) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB_CONNECTION_FAILED"]);
    exit;
}

$deviceId = $_POST["device_id"] ?? "";

if (!isDeviceRegistered($deviceId)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "UNAUTHORIZED"]);
    exit;
}

// Ambil konfigurasi unit, clear_script, dan device_ip dari DATABASE (bukan dari APK)
// Karena APK mengirim IP lokal WiFi, sedangkan VPS butuh IP Tailscale
try {
    $stmt = $db->prepare("
        SELECT md.device_ip, md.unit_id, du.clear_script
        FROM managed_devices md
        LEFT JOIN device_units du ON md.unit_id = du.id
        WHERE md.device_id = ?
        LIMIT 1
    ");
    $stmt->execute([$deviceId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB_QUERY_FAILED"]);
    exit;
}

// Gunakan IP dari database (Tailscale IP)
$deviceIp = $row['device_ip'] ?? '';
if (empty($deviceIp)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "DEVICE_IP_NOT_SET_IN_DB"]);
    exit;
}

// Bangun daftar package berdasarkan konfigurasi unit
$packages = [];
if ($row && !empty($row['clear_script'])) {
    $lines = preg_split('/\r\n|\r|\n/', $row['clear_script']);
    foreach ($lines as $line) {
        $pkg = trim($line);
        if ($pkg !== '') {
            $packages[] = $pkg;
        }
    }
}

// Fallback opsional ke CLEAR_APPS global jika unit tidak punya konfigurasi
if (empty($packages) && !empty($CLEAR_APPS) && is_array($CLEAR_APPS)) {
    $packages = $CLEAR_APPS;
}

if (empty($packages)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "NO_CLEAR_PACKAGES_CONFIGURED"]);
    exit;
}

// Jalankan ADB clear berdasarkan daftar package
adbConnect($deviceIp);

$results = [];
foreach ($packages as $pkg) {
    $output = adbClearPackage($deviceIp, $pkg);
    $results[] = [
        "package" => $pkg,
        "result" => trim($output)
    ];
}

// Setelah selesai clear, lakukan disconnect
adbDisconnect($deviceIp);

echo json_encode([
    "status" => "success",
    "device_id" => $deviceId,
    "results" => $results
]);
