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
     
    // Fetch attendance for today. 
    // If you want a specific date filter later, you can add a WHERE clause.
    $stmt = $pdo->query("
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            COALESCE(d.name, 'N/A') AS dept,
            'Day' AS shift,
            a.check_in AS checkin,
            a.check_out AS checkout,
            a.hours_worked AS hours,
            a.overtime_hours AS ot,
            a.status,
            a.is_late
        FROM attendance a
        JOIN employees e ON a.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE a.attendance_date = CURDATE()
        ORDER BY a.check_in ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}