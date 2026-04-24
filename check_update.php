<?php
/**
 * check_update.php
 * Endpoint untuk TV mengecek apakah ada update APK baru
 * 
 * GET parameter:
 *   current_version - versi yang terinstall di TV (e.g. "v260227-031500")
 * 
 * Response:
 *   { has_update: bool, version: string, url: string, filename: string }
 */

header('Content-Type: application/json; charset=utf-8');

$metaFile = __DIR__ . '/uploads/update/metadata.json';

// Jika tidak ada metadata, berarti belum pernah upload update
if (!file_exists($metaFile)) {
    echo json_encode([
        'has_update' => false,
        'message' => 'No update available'
    ]);
    exit;
}

$meta = json_decode(file_get_contents($metaFile), true);
if (!$meta || empty($meta['version'])) {
    echo json_encode([
        'has_update' => false,
        'message' => 'Invalid metadata'
    ]);
    exit;
}

$currentVersion = $_GET['current_version'] ?? '';
$serverVersion = $meta['version'] ?? '';
$apkUrl = $meta['url'] ?? '';

// Bandingkan versi - jika berbeda, ada update
$hasUpdate = !empty($currentVersion) && $currentVersion !== $serverVersion;

// Jika tidak ada current_version (pertama kali), anggap ada update
if (empty($currentVersion)) {
    $hasUpdate = true;
}

echo json_encode([
    'has_update' => $hasUpdate,
    'version' => $serverVersion,
    'url' => $apkUrl,
    'filename' => $meta['filename'] ?? '',
    'uploaded_at' => $meta['uploaded_at'] ?? ''
]);
