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

    // Pagination parameters
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    // Search parameter
    $search = trim($_GET['search'] ?? '');
    $searchCondition = '';
    $params = [];

    if ($search !== '') {
        $searchCondition = " WHERE (
            b.name LIKE ? OR
            b.city LIKE ? OR
            b.phone LIKE ? OR
            b.email LIKE ? OR
            CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 5, $searchTerm);
    }

    // Count total records
    $countSql = "
        SELECT COUNT(*)
        FROM branches b
        LEFT JOIN employees e ON b.manager_id = e.id
        $searchCondition
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch paginated data
    $sql = "
    SELECT 
        b.id,
        b.name,
        CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) AS manager,
        b.phone,
        b.email,
        b.city AS location,
        b.address,
        (SELECT COUNT(*) FROM employees emp WHERE emp.branch_id = b.id AND emp.status = 'Active') AS emp,
        b.status,
        COALESCE(u.username, 'System') AS updated_by_name
        FROM branches b
        LEFT JOIN employees e ON b.manager_id = e.id
        LEFT JOIN users u ON b.updated_by = u.id
        $searchCondition
        ORDER BY b.name ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    
    // Combine all parameters: search terms + limit + offset
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    
    $stmt->execute($allParams);
    $data = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data'    => $data,
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