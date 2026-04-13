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
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            COALESCE(d.name, 'N/A') AS dept,
            ec.it_cleared AS it,
            ec.finance_cleared AS finance,
            ec.hr_cleared AS hr,
            ec.admin_cleared AS admin,
            ec.assets_cleared AS assets,
            ec.overall_status AS overall
        FROM exit_clearances ec
        JOIN employees e ON ec.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        ORDER BY ec.created_at DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}