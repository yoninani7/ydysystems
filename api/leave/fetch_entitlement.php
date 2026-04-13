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
    
    // This query pivots the data so Annual Leave and Sick Leave appear on the same row
    $stmt = $pdo->query("
        SELECT 
            e.employee_id AS id,
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            COALESCE(d.name, 'N/A') AS dept,
            -- Annual Leave Stats
            MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.total_days ELSE 0 END) AS al_total,
            MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.used_days ELSE 0 END) AS al_used,
            MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.balance_days ELSE 0 END) AS al_bal,
            MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.carried_over_days ELSE 0 END) AS carry,
            -- Sick Leave Stats
            MAX(CASE WHEN lt.name = 'Sick Leave' THEN le.used_days ELSE 0 END) AS sl_used,
            MAX(CASE WHEN lt.name = 'Sick Leave' THEN le.balance_days ELSE 0 END) AS sl_bal
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN leave_entitlements le ON e.id = le.employee_id AND le.fiscal_year = YEAR(CURDATE())
        LEFT JOIN leave_types lt ON le.leave_type_id = lt.id
        WHERE e.status = 'Active'
        GROUP BY e.id
        ORDER BY e.employee_id ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}