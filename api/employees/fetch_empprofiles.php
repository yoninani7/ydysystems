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
            e.employee_id AS id,
            e.first_name AS fname,
            e.middle_name AS mname,
            e.last_name AS lname,
            u.username AS uname,
            e.gender,
            e.date_of_birth AS dob,
            e.hire_date AS hire,
            e.status,
            e.marital_status AS marital,
            e.personal_phone AS phone,
            e.personal_email AS email,
            d.name AS dept,
            jp.title AS position,
            b.name AS branch,
            et.name AS type,
            e.bank_name AS bankname,
            e.bank_account AS bankacc,
            e.tin,
            e.created_at AS created
        FROM employees e
        LEFT JOIN users u ON e.id = u.employee_id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN job_positions jp ON e.job_position_id = jp.id
        LEFT JOIN branches b ON e.branch_id = b.id
        LEFT JOIN employment_types et ON e.employment_type_id = et.id
        ORDER BY e.id DESC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}