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
    $pdo = get_pdo();
     
    $stmt = $pdo->query("
        SELECT 
            lr.id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            d.name AS dept,
            lt.name AS type,
            CONCAT(appr.first_name, ' ', appr.last_name) AS approver,
            lr.from_date AS `from`,
            lr.to_date AS `to`,
            lr.total_days AS days,
            lr.reason,
            lr.status
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        LEFT JOIN employees appr ON lr.approved_by = appr.id
        ORDER BY lr.created_at DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}