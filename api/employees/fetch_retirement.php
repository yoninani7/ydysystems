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

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['search'] ?? '');
    $searchCondition = '';
    $params = [];

    if ($search !== '') {
        $searchCondition = " WHERE full_name LIKE ? OR department_name LIKE ? ";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm];
    }

    $countSql = "SELECT COUNT(*) FROM v_retirement_forecast $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    /**
     * Query utilizing the 'v_retirement_forecast' view from the SQL schema.
     * We alias the columns to match the keys expected by the 
     * JavaScript 'buildTable' function in your frontend.
     */
    $sql = "
        SELECT 
            full_name AS name,
            department_name AS dept,
            current_age AS age,
            years_of_service AS tenure,
            scheduled_retirement_date AS date,
            days_until_retirement AS days
        FROM v_retirement_forecast
        $searchCondition
        ORDER BY days_until_retirement ASC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);
    
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true, 
        'data' => $results,
        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}