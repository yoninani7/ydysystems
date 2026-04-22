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
            d.name LIKE ? OR
            COALESCE(v.head_of_dept, '') LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm];
    }

    // Count total records
    $countSql = "
        SELECT COUNT(*)
        FROM departments d
        LEFT JOIN v_dept_structure_stats v ON d.id = v.dept_id
        $searchCondition
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch paginated data
    $sql = "
    SELECT
        d.id,
        d.name,
        COALESCE(v.head_of_dept, '—')   AS head,
        COALESCE(v.active_headcount, 0) AS emp,
        d.status,
        COALESCE(u.username, 'System')  AS updated_by_name
    FROM departments d
    LEFT JOIN v_dept_structure_stats v ON d.id = v.dept_id
    LEFT JOIN users u ON d.updated_by = u.id
    $searchCondition
    ORDER BY d.name ASC
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