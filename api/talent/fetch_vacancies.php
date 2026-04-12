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
            jv.id,
            jv.title,
            d.name AS dept,
            b.name AS branch,
            et.name AS type,
            jv.posted_date AS posted,
            jv.deadline_date AS deadline,
            jv.description,
            jv.requirements,
            jv.status
        FROM job_vacancies jv
        LEFT JOIN departments d ON jv.department_id = d.id
        LEFT JOIN branches b ON jv.branch_id = b.id
        LEFT JOIN employment_types et ON jv.employment_type_id = et.id
        ORDER BY jv.posted_date DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}