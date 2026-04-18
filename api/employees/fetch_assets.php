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
            a.asset_code LIKE ? OR
            a.name LIKE ? OR
            ac.name LIKE ? OR
            a.serial_number LIKE ? OR
            CONCAT(e_curr.first_name, ' ', e_curr.last_name) LIKE ? OR
            b.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 6, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM assets a
                 LEFT JOIN asset_categories ac ON a.category_id = ac.id
                 LEFT JOIN employees e_curr ON a.current_custodian_id = e_curr.id
                 LEFT JOIN branches b ON a.location_branch_id = b.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
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
        $searchCondition
        ORDER BY a.created_at DESC
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
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}