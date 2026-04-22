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
            jp.title LIKE ? OR
            COALESCE(d.name, 'Unassigned') LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm];
    }

    // Count total records
    $countSql = "
        SELECT COUNT(*)
        FROM job_positions jp
        LEFT JOIN departments d ON jp.department_id = d.id
        $searchCondition
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch paginated data
     $sql = "
    SELECT 
        jp.id,
        jp.title,
        COALESCE(d.name, 'Unassigned') AS dept,
        jp.status,
        (SELECT COUNT(*) FROM employees e WHERE e.job_position_id = jp.id AND e.status = 'Active') AS count,
        COALESCE(u.username, 'System') AS updated_by_name
        FROM job_positions jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN users u ON jp.updated_by = u.id
        $searchCondition
        ORDER BY d.name ASC, jp.title ASC
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