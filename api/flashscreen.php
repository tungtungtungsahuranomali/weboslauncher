<?php
header('Content-Type: application/json; charset=utf-8');
// Agar APK selalu dapat metadata terbaru (trigger download ulang saat ada upload baru)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// ============================
// CEK: Apakah flashscreen AKTIF di database?
// ============================
$db = $db ?? init_db_connection();
$splashEnabled = false;
if ($db) {
    try {
        $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key='splash_enabled'");
        $stmt->execute();
        $splashEnabled = (bool)(int)($stmt->fetchColumn() ?? 0);
    } catch (Exception $e) {
        // Jika query gagal, anggap mati agar aman
        $splashEnabled = false;
    }
}

// Jika flashscreen MATI, langsung kirim response disabled
if (!$splashEnabled) {
    echo json_encode([
        'enabled' => false,
        'version' => 'none',
        'filename' => null,
        'url' => null,
    ]);
    exit;
}

// ============================
// Flashscreen AKTIF — kirim metadata video
// ============================
$metaFile = __DIR__ . '/../uploads/flashscreen/metadata.json';

if (file_exists($metaFile)) {
    $meta = json_decode(file_get_contents($metaFile), true) ?: [];
    $meta['enabled'] = true;

    $relUrl = $meta['url'] ?? '';
    $filename = $meta['filename'] ?? '';

    // Hitung versi berbasis waktu modifikasi file video,
    // supaya meskipun metadata.json tidak ter-update, perubahan file tetap terdeteksi.
    $videoPath = null;
    if (!empty($filename)) {
        $videoPath = __DIR__ . '/../uploads/flashscreen/' . basename($filename);
    } elseif (!empty($relUrl)) {
        $videoPath = __DIR__ . '/../' . ltrim(parse_url($relUrl, PHP_URL_PATH) ?: '', '/');
    }

    $fileMtime = ($videoPath && is_file($videoPath)) ? filemtime($videoPath) : null;

    if ($fileMtime !== null) {
        // Pakai timestamp mtime sebagai versi yang dikirim ke APK
        $meta['version'] = (string)$fileMtime;
    } elseif (empty($meta['version'])) {
        // Fallback jika tidak ada versi maupun mtime
        $meta['version'] = (string)time();
    }

    if (!empty($relUrl)) {
        $ts = (int)($meta['version'] ?? time());
        $meta['url'] = get_full_url($relUrl) . '?v=' . $ts;
    }

    echo json_encode($meta);
} else {
    echo json_encode([
        'enabled' => true,
        'version' => 'none',
        'filename' => null,
        'url' => null,
    ]);
}
