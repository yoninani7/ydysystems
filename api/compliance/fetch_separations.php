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
            s.id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            COALESCE(d.name, 'N/A') AS dept,
            s.separation_type AS type,
            s.notice_date AS notice,
            s.last_working_day AS last_day,
            s.clearance_status AS clearance,
            s.final_settlement AS settlement,
            s.status
        FROM separations s
        JOIN employees e ON s.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        ORDER BY s.last_working_day DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}