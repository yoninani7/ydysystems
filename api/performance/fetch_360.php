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
            f.id,
            CONCAT(e.first_name, ' ', e.last_name) AS subject,
            COALESCE(d.name, 'N/A') AS dept,
            f.total_respondents AS total,
            f.completed_respondents AS complete,
            f.average_score AS avg,
            f.status
        FROM feedback_360 f
        JOIN employees e ON f.subject_employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        ORDER BY f.created_at DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}