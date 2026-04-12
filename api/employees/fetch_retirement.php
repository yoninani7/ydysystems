<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    // Using the view created in the SQL schema
    // It filters for employees age 55+ and calculates days remaining
    $stmt = $pdo->query("
        SELECT 
            full_name AS name,
            department_name AS dept,
            current_age AS age,
            CONCAT(years_of_service, ' yrs') AS tenure,
            scheduled_retirement_date AS date,
            days_until_retirement AS days
        FROM v_retirement_forecast
        ORDER BY days_until_retirement ASC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}