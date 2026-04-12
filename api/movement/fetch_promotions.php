<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    // We join job_positions twice: once for the old role and once for the new role
    $stmt = $pdo->query("
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            p.change_type AS type,
            jp1.title AS from_pos,
            jp2.title AS to_pos,
            d.name AS dept,
            p.old_salary AS sal_from,
            p.new_salary AS sal_to,
            p.effective_date AS eff_date,
            p.status
        FROM promotions p
        JOIN employees e ON p.employee_id = e.id
        LEFT JOIN job_positions jp1 ON p.from_position_id = jp1.id
        LEFT JOIN job_positions jp2 ON p.to_position_id = jp2.id
        LEFT JOIN departments d ON p.to_department_id = d.id
        ORDER BY p.created_at DESC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}