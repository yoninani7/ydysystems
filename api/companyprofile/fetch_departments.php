<?php
declare(strict_types=1);
define('IS_API', true);          // ← ADD THIS LINE FIRST
require_once '../../config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $pdo  = get_pdo();
    $stmt = $pdo->query("
        SELECT
            d.name,
            COALESCE(v.head_of_dept, '—')   AS head,
            COALESCE(v.active_headcount, 0) AS emp,
            d.status
        FROM departments d
        LEFT JOIN v_dept_structure_stats v ON d.id = v.dept_id
        ORDER BY d.name ASC
    ");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}