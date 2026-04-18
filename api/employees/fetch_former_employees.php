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

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['search'] ?? '');
    $searchCondition = '';
    $params = [];

    if ($search !== '') {
        $searchCondition = " WHERE (
            full_name LIKE ? OR
            last_department LIKE ? OR
            last_job_position LIKE ? OR
            exit_type LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 4, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM former_employees $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    
    // Selecting columns that match the keys used in your JS buildTable function
    $sql = "
        SELECT 
            full_name AS name,
            last_department AS dept,
            last_job_position AS role,
            exit_date AS exitDate,
            exit_type AS type,
            CONCAT(years_of_service, ' yrs') AS duration,
            rehire_eligible AS rehire
        FROM former_employees
        $searchCondition
        ORDER BY exit_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total'      => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}