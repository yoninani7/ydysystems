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
            CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) LIKE ? OR
            COALESCE(d.name, 'N/A') LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm, $searchTerm];
    }

    // Count total records
    $countSql = "
        SELECT COUNT(*)
        FROM probation_records pr
        JOIN (
            SELECT employee_id, MAX(id) AS latest_id
            FROM probation_records
            GROUP BY employee_id
        ) latest_pr ON pr.id = latest_pr.latest_id
        JOIN employees e ON pr.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        $searchCondition
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch paginated data
    $sql = "
    SELECT 
        e.id AS employee_id,
        CONCAT(e.first_name, ' ', e.last_name) AS name,
        COALESCE(d.name, 'N/A') AS dept,
        pr.start_date AS start,
        pr.end_date AS end,
        DATEDIFF(pr.end_date, CURDATE()) AS days,
        pr.status,
        COALESCE(u.username, 'System') AS updated_by_name
    FROM probation_records pr
    JOIN (
        SELECT employee_id, MAX(id) AS latest_id
        FROM probation_records
        GROUP BY employee_id
    ) latest_pr ON pr.id = latest_pr.latest_id
    JOIN employees e ON pr.employee_id = e.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN users u ON pr.updated_by = u.id
    $searchCondition
    ORDER BY pr.end_date ASC
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