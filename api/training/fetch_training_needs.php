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
            tn.id,
            COALESCE(d.name, 'All Departments') AS dept,
            tn.skill_gap AS skill,
            tn.priority,
            tn.affected_employees AS emp_count,
            tn.proposed_training AS proposed,
            tn.status
        FROM training_needs tn
        LEFT JOIN departments d ON tn.department_id = d.id
        ORDER BY tn.created_at DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}