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
     
    // This query aggregates individual attendance records into a department summary
    // for the current month and year.
    $stmt = $pdo->query("
        SELECT 
            d.name AS dept,
            COUNT(DISTINCT e.id) AS total,
            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) AS absent,
            SUM(CASE WHEN a.status = 'L' THEN 1 ELSE 0 END) AS leave_days,
            SUM(CASE WHEN a.is_late = 1 THEN 1 ELSE 0 END) AS late,
            COALESCE(SUM(a.overtime_hours), 0) AS ot,
            -- Calculate Attendance Rate: (Present / Expected Days) * 100
            CASE 
                WHEN COUNT(a.id) > 0 THEN 
                    ROUND((SUM(CASE WHEN a.status IN ('P', 'H') THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1)
                ELSE 0 
            END AS rate
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        LEFT JOIN attendance a ON e.id = a.employee_id 
             AND a.month = MONTH(CURDATE()) 
             AND a.year = YEAR(CURDATE())
        GROUP BY d.id
        ORDER BY rate DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}