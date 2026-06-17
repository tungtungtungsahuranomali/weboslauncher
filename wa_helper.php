<?php
/**
 * wa_helper.php
 * Helper untuk mengirim notifikasi WhatsApp via Fonnte API
 */

/**
 * Kirim pesan WhatsApp via Fonnte
 * @param PDO $db Database connection
 * @param string $message Pesan yang akan dikirim
 * @param string $type Tipe notifikasi: 'dining', 'amenities', atau 'general' (default)
 * @return bool True jika berhasil terkirim
 */
function sendWhatsAppNotification(PDO $db, string $message, string $type = 'general'): bool
{
    try {
        // Ambil semua settings WA
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN (
            'wa_gateway_enabled', 'wa_fonnte_token', 
            'wa_recipient_number', 'wa_recipient_dining', 'wa_recipient_amenities', 'wa_recipient_transportation'
        )");
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $enabled = ($settings['wa_gateway_enabled'] ?? '0') === '1';
        $token = trim($settings['wa_fonnte_token'] ?? '');

        if (!$enabled || empty($token)) {
            return false;
        }

        // Tentukan penerima berdasarkan tipe
        $target = '';
        if ($type === 'dining') {
            $target = $settings['wa_recipient_dining'] ?? '';
        } elseif ($type === 'amenities') {
            $target = $settings['wa_recipient_amenities'] ?? '';
        } elseif ($type === 'transportation') {
            $target = $settings['wa_recipient_transportation'] ?? '';
        }

        // Fallback ke penerima umum jika khusus kosong
        if (empty($target)) {
            $target = $settings['wa_recipient_number'] ?? '';
        }

        // Hapus spasi dan validasi
        $target = str_replace(' ', '', trim($target));

        if (empty($target)) {
            return false;
        }

        // Kirim ke setiap nomor (pisah koma)
        $numbers = array_filter(array_map('trim', explode(',', $target)));
        $allSent = true;

        foreach ($numbers as $number) {
            if (empty($number))
                continue;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $number,
                    'message' => $message,
                ],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $token,
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            // Log response
            $logFile = __DIR__ . '/wa_log.txt';
            $logEntry = date('Y-m-d H:i:s') . " | HTTP {$httpCode} | Type: {$type} | Target: {$number} | Response: {$response}\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);

            if ($httpCode !== 200) {
                $allSent = false;
            }
        }

        return $allSent;
    } catch (Exception $e) {
        $logFile = __DIR__ . '/wa_log.txt';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
