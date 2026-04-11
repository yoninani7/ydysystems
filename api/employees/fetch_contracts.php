<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    $stmt = $pdo->query("
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            COALESCE(d.name, 'Unassigned') AS dept,
            COALESCE(e.hire_date, 'N/A') AS start,
            COALESCE(e.contract_end_date, 'Permanent') AS expiry,
            IF(e.contract_end_date IS NULL, 999, DATEDIFF(e.contract_end_date, CURDATE())) AS days
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.status = 'Active' 
        ORDER BY e.contract_end_date ASC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}