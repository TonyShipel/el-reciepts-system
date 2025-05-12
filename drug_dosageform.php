<?php
header('Content-Type: application/json; charset=utf-8');
if (!isset($_GET['trade_name'])) {
    echo json_encode([]);
    exit;
}

$conn = pg_connect("host=localhost dbname=Legacy user=postgres password=password");
if (!$conn) {
    echo json_encode([]);
    exit;
}

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
