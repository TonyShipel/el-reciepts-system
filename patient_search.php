<?php
header('Content-Type: application/json; charset=utf-8');
if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([]);
    exit;
}
$q = trim($_GET['q']);
$conn = pg_connect("host=localhost dbname=Legacy user=postgres password=password");
if (!$conn) {
    echo json_encode([]);
    exit;
}

// Split search query into parts (assuming format: "LastName FirstName Patronymic or initials")
$parts = preg_split('/\s+/', $q);
$sLST = isset($parts[0]) ? $parts[0] : '';
$sFST = isset($parts[1]) ? $parts[1] : '';
$sPTR = isset($parts[2]) ? $parts[2] : '';

$where = [];
$params = [];

// Поиск по фамилии
if ($sLST) {
    $where[] = 'pat_ident.lst_name ILIKE $' . (count($params) + 1);
    $params[] = "%$sLST%";
}
// Поиск по первой букве имени
if ($sFST) {
    $where[] = 'LEFT(pat_ident.fst_name, 1) ILIKE $' . (count($params) + 1);
    $params[] = "$sFST%";
}
// Поиск по первой букве отчества
if ($sPTR) {
    $where[] = 'LEFT(pat_ident.ptr_name, 1) ILIKE $' . (count($params) + 1);
    $params[] = "$sPTR%";
}

// Если ничего не ввели, ищем по коду
if (!$where) {
    $where[] = 'pat_ident.pat_code ILIKE $1';
    $params[] = "%$q%";
}

$sql = "SELECT * FROM PATIENTS";

$res = pg_query_params($conn, $sql, $params);

$results = [];
while ($row = pg_fetch_assoc($res)) {
    // Преобразуем дату в формат YYYY-MM-DD
    if ($row['birth_date']) {
        $row['birth_date'] = date('Y-m-d', strtotime($row['birth_date']));
    }
    
    $results[] = [
        'pat_id' => $row['pat_id']
    ];
}
pg_close($conn);

// Добавляем отладочную информацию
error_log('Search results: ' . json_encode($results));
echo json_encode($results); 
