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
            i.id,
            i.intern_code AS id_code,
            i.full_name AS name,
            i.institution AS uni,
            COALESCE(d.name, 'Unassigned') AS dept,
            CONCAT(e.first_name, ' ', e.last_name) AS mentor,
            i.start_date AS start,
            i.end_date AS end,
            i.evaluation_score AS eval,
            i.status
        FROM internships i
        LEFT JOIN departments d ON i.department_id = d.id
        LEFT JOIN employees e ON i.mentor_id = e.id
        ORDER BY i.start_date DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}