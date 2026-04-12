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
            c.full_name AS candidate,
            jv.title AS position,
            CONCAT(e.first_name, ' ', e.last_name) AS interviewer,
            i.interview_date AS date,
            i.interview_time AS time,
            i.mode,
            i.result
        FROM interviews i
        JOIN candidates c ON i.candidate_id = c.id
        LEFT JOIN job_vacancies jv ON i.vacancy_id = jv.id
        LEFT JOIN employees e ON i.interviewer_id = e.id
        ORDER BY i.interview_date ASC, i.interview_time ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}