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
            CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR
            d1.name LIKE ? OR
            d2.name LIKE ? OR
            b1.name LIKE ? OR
            b2.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 5, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM transfers t
                 JOIN employees e ON t.employee_id = e.id
                 LEFT JOIN departments d1 ON t.from_department_id = d1.id
                 LEFT JOIN departments d2 ON t.to_department_id = d2.id
                 LEFT JOIN branches b1 ON t.from_branch_id = b1.id
                 LEFT JOIN branches b2 ON t.to_branch_id = b2.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    
    // We join departments and branches twice to show the full movement path
    $sql = "
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            d1.name AS from_dept,
            d2.name AS to_dept,
            b1.name AS from_branch,
            b2.name AS to_branch,
            t.request_date AS req_date,
            t.effective_date AS eff_date,
            t.status
        FROM transfers t
        JOIN employees e ON t.employee_id = e.id
        LEFT JOIN departments d1 ON t.from_department_id = d1.id
        LEFT JOIN departments d2 ON t.to_department_id = d2.id
        LEFT JOIN branches b1 ON t.from_branch_id = b1.id
        LEFT JOIN branches b2 ON t.to_branch_id = b2.id
        $searchCondition
        ORDER BY t.request_date DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);

    echo json_encode([
        'success' => true,
        'data'    => $stmt->fetchAll(PDO::FETCH_ASSOC),
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