case 'getInfo':
    $stmt = $db->query("
        SELECT id, title AS name, description, icon_path 
        FROM hotel_info 
        ORDER BY sort_order ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        if (!empty($r['icon_path']) && !str_starts_with($r['icon_path'], 'http')) {
            $r['icon_path'] = 'https://ogietv.com/AHotel/' . ltrim($r['icon_path'], '/');
        }
    }
    echo json_encode(['status' => 'success', 'data' => $rows]);
    break;