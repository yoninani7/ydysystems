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
            u.username LIKE ? OR
            u.email LIKE ? OR
            r.name LIKE ? OR
            d.name LIKE ? OR
            CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 5, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 LEFT JOIN employees e ON u.employee_id = e.id
                 LEFT JOIN departments d ON u.department_id = d.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
    SELECT 
        u.id,
        COALESCE(CONCAT(e.first_name, ' ', e.last_name), u.username) AS name,
        u.email,
        r.name AS role,
        COALESCE(d.name, 'N/A') AS dept,
        u.last_login,
        u.status,
        COALESCE(updater.username, 'System') AS updated_by_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN employees e ON u.employee_id = e.id
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN users updater ON u.updated_by = updater.id
    $searchCondition
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(),
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