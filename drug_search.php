<?php
header('Content-Type: application/json; charset=utf-8');
if (!isset($_GET['q']) && !isset($_GET['trade_name'])) {
    echo json_encode([]);
    exit;
}

$conn = pg_connect("host=localhost dbname=Legacy user=postgres password=password");
if (!$conn) {
    echo json_encode([]);
    exit;
}

// Если передан trade_name, ищем все формы выпуска для конкретного лекарства
if (isset($_GET['trade_name'])) {
    $trade_name = $_GET['trade_name'];
    $res = pg_query_params($conn, 
        "SELECT DISTINCT dosageform FROM drugs WHERE trade_name = $1 AND dosageform IS NOT NULL AND dosageform <> '' ORDER BY dosageform", 
        [$trade_name]
    );
    $forms = [];
    while ($row = pg_fetch_assoc($res)) {
        $forms[] = $row['dosageform'];
    }
    pg_close($conn);
    echo json_encode($forms);
    exit;
} else {
    // Обычный поиск по названию или действующему веществу
    $q = trim($_GET['q']);
    $res = pg_query_params($conn, 
        "SELECT DISTINCT ON (trade_name) trade_name, active_substance_ru, active_substance_en 
         FROM drugs 
         WHERE trade_name ILIKE $1 OR active_substance_ru ILIKE $1 
         ORDER BY trade_name, active_substance_ru 
         LIMIT 20", 
        ["%$q%"]
    );
}

$results = [];
while ($row = pg_fetch_assoc($res)) {
    $results[] = [
        'trade_name' => $row['trade_name'],
        'active_substance_ru' => $row['active_substance_ru'],
        'active_substance_en' => $row['active_substance_en']
    ];
}

pg_close($conn);
echo json_encode($results); 
