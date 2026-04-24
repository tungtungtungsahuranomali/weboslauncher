<?php
// Worker sederhana untuk menjalankan pending_start_launcher
// Jalankan via cron / Task Scheduler setiap beberapa detik/menit.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/adb_helper.php';

$db = init_db_connection();
if ($db === null) {
    echo "[Worker] DB connection failed\n";
    exit(1);
}

date_default_timezone_set('Asia/Jakarta');

function worker_log(string $message): void
{
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
}

/**
 * Cek apakah IP perangkat bisa di-ping.
 */
function worker_is_device_reachable(string $ip, int $timeoutSeconds = 1): bool
{
    $os = strtoupper(substr(PHP_OS, 0, 3));
    if ($os === 'WIN') {
        $cmd = sprintf('ping -n 1 -w %d %s', $timeoutSeconds * 1000, escapeshellarg($ip));
    } else {
        $cmd = sprintf('ping -c 1 -W %d %s', $timeoutSeconds, escapeshellarg($ip));
    }

    exec($cmd, $output, $status);
    return $status === 0;
}

// Ambil semua device yang:
// - Berada pada unit_id = 3
// Worker akan:
// - jika ping gagal -> set pending_start_launcher = 1 (agar saat online lagi bisa start launcher)
// - jika ping sukses dan pending_start_launcher = 1 -> jalankan start launcher lalu set pending_start_launcher = 0
try {
    $stmt = $db->query("
        SELECT md.id, md.device_ip, md.room_number
        FROM managed_devices md
        WHERE md.device_ip IS NOT NULL 
          AND md.device_ip != ''
          AND md.unit_id = 3
    ");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    worker_log("[Worker] Query error: " . $e->getMessage());
    exit(1);
}

worker_log("[Worker] Monitoring " . count($devices) . " device(s) on unit 3.");

foreach ($devices as $dev) {
    $id = (int)$dev['id'];
    $ip = $dev['device_ip'];
    $room = $dev['room_number'];

    if (!worker_is_device_reachable($ip)) {
        // Jika device offline saat masih checked-in, set pending agar saat hidup lagi otomatis start launcher
        $db->prepare("UPDATE managed_devices SET pending_start_launcher = 1 WHERE id = ?")->execute([$id]);
        worker_log("[Worker] $room / $ip not reachable -> set pending_start_launcher=1");
        continue;
    }

    // Device online, cek apakah masih pending
    $stmtPending = $db->prepare("SELECT pending_start_launcher FROM managed_devices WHERE id = ? LIMIT 1");
    $stmtPending->execute([$id]);
    $pending = (int)($stmtPending->fetchColumn() ?? 0);

    if ($pending !== 1) {
        worker_log("[Worker] $room / $ip reachable (pending=0), skip");
        continue;
    }

    worker_log("[Worker] $room / $ip reachable (pending=1), starting launcher...");
    $connect = trim((string)adbConnect($ip));
    $start = trim((string)adbStartLauncher($ip));
    $disconnect = trim((string)adbDisconnect($ip));
    worker_log("[Worker] ADB connect: " . ($connect !== '' ? $connect : '(empty)'));
    worker_log("[Worker] ADB start: " . ($start !== '' ? $start : '(empty)'));
    worker_log("[Worker] ADB disconnect: " . ($disconnect !== '' ? $disconnect : '(empty)'));

    $db->prepare("UPDATE managed_devices SET pending_start_launcher = 0 WHERE id = ?")->execute([$id]);
}

worker_log("[Worker] Done");

