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
    $searchCondition = " AND 1=1";
    $params = [];

    if ($search !== '') {
        $searchCondition = " AND (
            e.employee_id LIKE ? OR
            CONCAT(e.first_name, ' ', e.last_name) LIKE ? OR
            d.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 3, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM employees e
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE e.status = 'Active'
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    // This query pivots the data so Annual Leave and Sick Leave appear on the same row
   $sql = "
    SELECT 
        e.employee_id AS id,
        CONCAT(e.first_name, ' ', e.last_name) AS name,
        COALESCE(d.name, 'N/A') AS dept,
        MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.total_days ELSE 0 END) AS al_total,
        MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.used_days ELSE 0 END) AS al_used,
        MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.balance_days ELSE 0 END) AS al_bal,
        MAX(CASE WHEN lt.name = 'Annual Leave' THEN le.carried_over_days ELSE 0 END) AS carry,
        MAX(CASE WHEN lt.name = 'Sick Leave' THEN le.used_days ELSE 0 END) AS sl_used,
        MAX(CASE WHEN lt.name = 'Sick Leave' THEN le.balance_days ELSE 0 END) AS sl_bal,
        COALESCE(MAX(u.username), 'System') AS updated_by_name
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN leave_entitlements le ON e.id = le.employee_id AND le.fiscal_year = YEAR(CURDATE())
    LEFT JOIN leave_types lt ON le.leave_type_id = lt.id
    LEFT JOIN users u ON le.updated_by = u.id
    WHERE e.status = 'Active'
    $searchCondition
    GROUP BY e.id
    ORDER BY e.employee_id ASC
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