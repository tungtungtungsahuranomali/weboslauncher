<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$db = init_db_connection();
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi DB gagal']);
    exit;
}

try {
    $stmt = $db->query("SELECT id, name, price, image_url, status FROM dining_menu WHERE status='active' ORDER BY id DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
        if (!empty($r['image_url']) && !str_starts_with($r['image_url'], 'http')) {
            $r['image_url'] = 'https://ogietv.com/AHotel/' . ltrim($r['image_url'], '/');
        }
    }

    echo json_encode(['status' => 'success', 'data' => $rows]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan database', 'detail' => $e->getMessage()]);
}