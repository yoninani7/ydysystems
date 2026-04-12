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
     
    // We join with employees twice to get both current and previous custodian names
    $stmt = $pdo->query("
        SELECT 
            a.asset_code AS id,
            a.name AS name,
            ac.name AS cat,
            a.serial_number AS serial,
            a.asset_value AS val,
            CONCAT(e_prev.first_name, ' ', e_prev.last_name) AS user_prev,
            CONCAT(e_curr.first_name, ' ', e_curr.last_name) AS user,
            b.name AS loc,
            a.warranty_expiry AS war,
            a.status
        FROM assets a
        LEFT JOIN asset_categories ac ON a.category_id = ac.id
        LEFT JOIN employees e_curr ON a.current_custodian_id = e_curr.id
        LEFT JOIN employees e_prev ON a.previous_custodian_id = e_prev.id
        LEFT JOIN branches b ON a.location_branch_id = b.id
        ORDER BY a.created_at DESC
    ");
    
    $results = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}