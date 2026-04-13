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
            ts.id,
            ts.course_name AS course,
            COALESCE(d.name, 'All Depts') AS dept,
            CONCAT(e.first_name, ' ', e.last_name) AS trainer,
            ts.training_date AS date,
            ts.training_time AS time,
            ts.venue,
            ts.total_seats,
            ts.enrolled_seats,
            ts.status
        FROM training_schedules ts
        LEFT JOIN departments d ON ts.department_id = d.id
        LEFT JOIN employees e ON ts.trainer_id = e.id
        ORDER BY ts.training_date ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}