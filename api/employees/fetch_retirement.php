<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

// 1. Security Check: Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $pdo = get_pdo();
     
    /**
     * Query utilizing the 'v_retirement_forecast' view from the SQL schema.
     * We alias the columns to match the keys expected by the 
     * JavaScript 'buildTable' function in your frontend.
     */
    $stmt = $pdo->query("
        SELECT 
            full_name AS name,
            department_name AS dept,
            current_age AS age,
            years_of_service AS tenure,
            scheduled_retirement_date AS date,
            days_until_retirement AS days
        FROM v_retirement_forecast
        ORDER BY days_until_retirement ASC
    ");
    
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true, 
        'data' => $results
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}