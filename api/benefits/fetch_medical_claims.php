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
            mc.claim_code AS id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            COALESCE(d.name, 'N/A') AS dept,
            mc.category,
            mc.amount,
            mc.submitted_date AS submitted,
            mc.receipt_attached AS receipt,
            mc.status
        FROM medical_claims mc
        JOIN employees e ON mc.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        ORDER BY mc.submitted_date DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}