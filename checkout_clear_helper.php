<?php
/**
 * Helper: Clear data TV saat checkout
 * Digunakan oleh checkin.php dan api.php (vhp_checkout)
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/adb_helper.php';

/**
 * Jalankan ADB clear untuk semua app di TV berdasarkan room_number.
 * Jika ADB gagal (TV mati), set pending_clear = 1 agar clear dijalankan saat TV boot.
 * 
 * @param PDO $db Database connection
 * @param string $room_number Nomor kamar yang checkout
 * @return array Hasil clear data untuk logging
 */
function clearTVDataByRoom(PDO $db, string $room_number): array
{
    $log = [
        'room_number' => $room_number,
        'status' => 'skipped',
        'message' => '',
        'results' => []
    ];

    try {
        // 1. Cari device & unit dari managed_devices berdasarkan room_number
        $stmt = $db->prepare("
            SELECT md.device_id, md.device_ip, md.unit_id, du.clear_script
            FROM managed_devices md
            LEFT JOIN device_units du ON md.unit_id = du.id
            WHERE md.room_number = ? AND md.device_ip IS NOT NULL AND md.device_ip != ''
        ");
        $stmt->execute([$room_number]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device || empty($device['device_ip'])) {
            // Tidak ada IP → set pending_clear agar clear saat TV nyala
            setPendingClear($db, $room_number, true);
            $log['status'] = 'pending';
            $log['message'] = "Tidak ada device IP untuk kamar $room_number. Pending clear diset.";
            error_log("[AutoClear] " . $log['message']);
            return $log;
        }

        $deviceIp = $device['device_ip'];
        $deviceId = $device['device_id'];

        // Siapkan daftar package yang akan di-clear berdasarkan konfigurasi per-unit
        // di device_units.clear_script (satu package per baris).
        $packages = [];
        if (!empty($device['clear_script'])) {
            $lines = preg_split('/\r\n|\r|\n/', $device['clear_script']);
            foreach ($lines as $line) {
                $pkg = trim($line);
                if ($pkg !== '') {
                    $packages[] = $pkg;
                }
            }
        }

        if (empty($packages)) {
            $log['status'] = 'skipped';
            $log['message'] = "Tidak ada daftar package clear yang terkonfigurasi untuk kamar $room_number.";
            error_log("[AutoClear] " . $log['message']);
            return $log;
        }

        // 2. Connect ke TV via ADB
        $connectResult = adbConnect($deviceIp);
        error_log("[AutoClear] ADB connect ke $deviceIp: $connectResult");

        // 3. Cek apakah ADB connect berhasil
        $adbSuccess = false;

        // 4. Clear semua app sesuai daftar package
        $results = [];
        foreach ($packages as $pkg) {
            $output = adbClearPackage($deviceIp, $pkg);
            $result = trim($output ?? '');
            $results[] = [
                'package' => $pkg,
                'result' => $result
            ];
            // Jika salah satu berhasil, artinya ADB terkoneksi
            if (stripos($result, 'Success') !== false) {
                $adbSuccess = true;
            }
        }

        // 5. Disconnect dari TV via ADB setelah selesai
        $disconnectResult = adbDisconnect($deviceIp);
        error_log("[AutoClear] ADB disconnect dari $deviceIp: $disconnectResult");

        if ($adbSuccess) {
            // Clear berhasil → pastikan pending_clear = 0
            setPendingClear($db, $room_number, false);
            $log['status'] = 'success';
            $log['message'] = "Clear data berhasil untuk kamar $room_number (IP: $deviceIp, Device: $deviceId)";
        } else {
            // ADB gagal (TV mati/offline) → set pending_clear
            setPendingClear($db, $room_number, true);
            $log['status'] = 'pending';
            $log['message'] = "ADB gagal untuk kamar $room_number. Pending clear diset.";
        }

        $log['results'] = $results;
        error_log("[AutoClear] " . $log['message']);

    } catch (Exception $e) {
        // Error → set pending_clear sebagai safety net
        setPendingClear($db, $room_number, true);
        $log['status'] = 'error';
        $log['message'] = "Error clear data kamar $room_number: " . $e->getMessage() . ". Pending clear diset.";
        error_log("[AutoClear] " . $log['message']);
    }

    return $log;
}

/**
 * Set atau reset flag pending_clear untuk semua device di room tertentu
 */
function setPendingClear(PDO $db, string $room_number, bool $pending): void
{
    try {
        $val = $pending ? 1 : 0;
        $db->prepare("UPDATE managed_devices SET pending_clear = ? WHERE room_number = ?")
            ->execute([$val, $room_number]);
    } catch (Exception $e) {
        error_log("[AutoClear] Gagal set pending_clear: " . $e->getMessage());
    }
}
