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
    
    // Select the name, description (aliased as desc), and count active employees
    $stmt = $pdo->query("
        SELECT 
            et.name,
            et.description AS `desc`,
            (SELECT COUNT(*) FROM employees e WHERE e.employment_type_id = et.id AND e.status = 'Active') AS count
        FROM employment_types et
        ORDER BY et.id ASC
    ");
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}