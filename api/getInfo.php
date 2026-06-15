case 'getInfo':
    $stmt = $db->query("
        SELECT id, title AS name, description, icon_path 
        FROM hotel_info 
        ORDER BY sort_order ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        if (!empty($r['icon_path']) && strpos($r['icon_path'], 'http') !== 0) {
            $r['icon_path'] = 'https://takeoff.web.id/' . ltrim($r['icon_path'], '/');
        }
    }
    echo json_encode(['status' => 'success', 'data' => $rows]);
    break;