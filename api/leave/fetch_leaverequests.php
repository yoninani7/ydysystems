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
            lt.name LIKE ? OR
            CONCAT(appr.first_name, ' ', appr.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 4, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM leave_requests lr
                 JOIN employees e ON lr.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 JOIN leave_types lt ON lr.leave_type_id = lt.id
                 LEFT JOIN employees appr ON lr.approved_by = appr.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            lr.id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            d.name AS dept,
            lt.name AS type,
            CONCAT(appr.first_name, ' ', appr.last_name) AS approver,
            lr.from_date AS `from`,
            lr.to_date AS `to`,
            lr.total_days AS days,
            lr.reason,
            lr.status,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        LEFT JOIN employees appr ON lr.approved_by = appr.id
        LEFT JOIN users u ON lr.updated_by = u.id
        $searchCondition
        ORDER BY lr.created_at DESC
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