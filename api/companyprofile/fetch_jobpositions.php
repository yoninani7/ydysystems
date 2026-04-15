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

try {
    $pdo  = get_pdo();
    $stmt = $pdo->query("
        SELECT 
            jp.title, 
            COALESCE(d.name, 'Unassigned') as dept,
            jp.status,   
            (SELECT COUNT(*) FROM employees e WHERE e.job_position_id = jp.id AND e.status = 'Active') as count
        FROM job_positions jp
        LEFT JOIN departments d ON jp.department_id = d.id
        ORDER BY d.name ASC, jp.title ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}