<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

require_once __DIR__ . '/config.php';


try {
    if (function_exists('init_db_connection')) {
        $db = init_db_connection();
    } else {
        $db = null;
    }
} catch (Throwable $e) {
    $db = null;
}

header('Content-Type: application/json');

try {
    if (!isset($_FILES['update_file'])) {
        throw new Exception('No file uploaded.');
    }

    $file = $_FILES['update_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload failed with error code: ' . $file['error']);
    }

    // Validasi ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'apk') {
        throw new Exception('Invalid file type. Only .apk allowed.');
    }

    // Folder upload
    $uploadDir = __DIR__ . '/uploads/update/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    // Simpan file dengan nama unik
    $newName = 'launcher_update_' . date('Ymd_His') . '.apk';
    $targetFile = $uploadDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        throw new Exception('Failed to save uploaded file.');
    }

    // Buat versi baru
    $newVersion = 'v' . date('ymd-His');

    // Simpan versi di database jika DB aktif
    try {
        if ($db instanceof PDO) {
            $stmt = $db->prepare("
                INSERT INTO global_settings (setting_key, setting_value)
                VALUES ('system_version', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$newVersion]);
        }
    } catch (Throwable $e) {
        file_put_contents(__DIR__ . '/log.error', "[".date('Y-m-d H:i:s')."] DB Save Fail: ".$e->getMessage()."\n", FILE_APPEND);
    }

    // Simpan metadata.json untuk update otomatis
    // GUNAKAN BASE_URL DI SINI
    $meta = [
        'version' => $newVersion,
        'filename' => $newName,
        'uploaded_at' => date('Y-m-d H:i:s'),
        'url' => BASE_URL . 'uploads/update/' . $newName
    ];
    file_put_contents($uploadDir . 'metadata.json', json_encode($meta, JSON_PRETTY_PRINT));

    echo json_encode([
        'status' => 'success',
        'message' => "✅ File uploaded successfully as {$newName} (version {$newVersion})"
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ ' . $e->getMessage()
    ]);
}
?>