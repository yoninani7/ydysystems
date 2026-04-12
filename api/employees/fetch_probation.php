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
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            COALESCE(d.name, 'N/A') AS dept,
            pr.start_date AS start,
            pr.end_date AS end,
            DATEDIFF(pr.end_date, CURDATE()) AS days,
            pr.status
        FROM probation_records pr
        JOIN employees e ON pr.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id 
        ORDER BY pr.end_date ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}