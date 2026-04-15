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
            b.name,
            CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) AS manager,
            b.phone,
            b.email,
            b.city AS location,
            (SELECT COUNT(*) FROM employees emp WHERE emp.branch_id = b.id AND emp.status = 'Active') AS emp,
            b.status
        FROM branches b
        LEFT JOIN employees e ON b.manager_id = e.id
        ORDER BY b.name ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>