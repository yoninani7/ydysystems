<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    // We join departments and branches twice to show the full movement path
    $stmt = $pdo->query("
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            d1.name AS from_dept,
            d2.name AS to_dept,
            b1.name AS from_branch,
            b2.name AS to_branch,
            t.request_date AS req_date,
            t.effective_date AS eff_date,
            t.status
        FROM transfers t
        JOIN employees e ON t.employee_id = e.id
        LEFT JOIN departments d1 ON t.from_department_id = d1.id
        LEFT JOIN departments d2 ON t.to_department_id = d2.id
        LEFT JOIN branches b1 ON t.from_branch_id = b1.id
        LEFT JOIN branches b2 ON t.to_branch_id = b2.id
        ORDER BY t.request_date DESC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}