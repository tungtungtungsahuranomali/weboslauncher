<?php

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$db = init_db_connection();
if (!$db) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}


$db->query("
CREATE TABLE IF NOT EXISTS dining_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(50),
  items JSON,
  total INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$input = json_decode(file_get_contents('php://input'), true);
$room_number = $input['room_number'] ?? '';
$items = $input['items'] ?? [];

if (empty($room_number) || empty($items)) {
    echo json_encode(["status" => "error", "message" => "Incomplete order data"]);
    exit;
}

// Hitung total
$total = 0;
foreach ($items as $i) {
    $total += intval($i['price']) * intval($i['qty']);
}

try {
    $stmt = $db->prepare("INSERT INTO dining_orders (room_number, items, total) VALUES (?, ?, ?)");
    $stmt->execute([$room_number, json_encode($items, JSON_UNESCAPED_UNICODE), $total]);

    echo json_encode(["status" => "success", "message" => "Order saved successfully"]);
} catch (PDOException $e) {
    log_error("postOrder.php: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Failed to save order"]);
}