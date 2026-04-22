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
            d.name LIKE ? OR
            CONCAT(rev.first_name, ' ', rev.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 3, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM performance_reviews pr
                 JOIN employees e ON pr.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 LEFT JOIN employees rev ON pr.reviewer_id = rev.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            pr.id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            COALESCE(d.name, 'N/A') AS dept,
            CONCAT(rev.first_name, ' ', rev.last_name) AS reviewer,
            pr.review_period AS period,
            pr.overall_score AS score,
            pr.rating AS rank,
            pr.status,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM performance_reviews pr
        JOIN employees e ON pr.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN employees rev ON pr.reviewer_id = rev.id
        LEFT JOIN users u ON pr.updated_by = u.id
        $searchCondition
        ORDER BY pr.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);

    echo json_encode([
        'success' => true,
        'data'    => $stmt->fetchAll(),
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