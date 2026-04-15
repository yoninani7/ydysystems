<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

    $allowed_types = [
    'employees' => [
        'table' => 'employees',
        'value_column' => 'id',  // internal numeric ID for the hidden field
        'display_column' => "CONCAT(employee_id, ' - ', first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)",
        'where' => "status = 'Active'"
    ],
    'departments' => [
        'table' => 'departments',
        'value_column' => 'id',
        'display_column' => 'name',
        'where' => "status = 'Active' AND deleted_at IS NULL"
    ],
    'branches' => [
        'table' => 'branches',
        'value_column' => 'id',
        'display_column' => 'name',
        'where' => "status = 'Active' AND deleted_at IS NULL"
    ],
    'job_positions' => [
            'table' => 'job_positions',
            'value_column' => 'id',
            'display_column' => 'title',
            'where' => "status = 'Active' AND deleted_at IS NULL"
            ],
    'employment_types' => [
            'table' => 'employment_types',
            'value_column' => 'id',
            'display_column' => 'name',
            'where' => "1=1"
        ]
];

$type = $_GET['type'] ?? '';
if (!isset($allowed_types[$type])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid dropdown type']);
    exit;
}

$config = $allowed_types[$type];
$search = $_GET['search'] ?? '';
$search_condition = '';
$params = [];

if ($search !== '') {
    $search_condition = " AND {$config['display_column']} LIKE :search";
    $params[':search'] = "%$search%";
}

try {
    $pdo = get_pdo();
    $sql = "SELECT {$config['value_column']} AS value, {$config['display_column']} AS label 
            FROM {$config['table']} 
            WHERE {$config['where']} $search_condition
            ORDER BY label ASC
            LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>