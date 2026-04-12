<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    // Selecting columns that match the keys used in your JS buildTable function
    $stmt = $pdo->query("
        SELECT 
            full_name AS name,
            last_department AS dept,
            last_job_position AS role,
            exit_date AS exitDate,
            exit_type AS type,
            CONCAT(years_of_service, ' yrs') AS duration,
            rehire_eligible AS rehire
        FROM former_employees
        ORDER BY exit_date DESC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // We send back the data. Note: the JS handles badge rendering based on the string values
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}