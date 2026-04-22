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
            da.action_type LIKE ? OR
            CONCAT(issuer.first_name, ' ', issuer.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 4, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM disciplinary_actions da
                 JOIN employees e ON da.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 LEFT JOIN employees issuer ON da.issued_by = issuer.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            da.id,
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            COALESCE(d.name, 'N/A') AS dept,
            da.action_type AS type,
            da.incident_date AS incident,
            da.issued_date AS issued,
            CONCAT(issuer.first_name, ' ', issuer.last_name) AS issuer_name,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM disciplinary_actions da
        JOIN employees e ON da.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN employees issuer ON da.issued_by = issuer.id
        LEFT JOIN users u ON da.updated_by = u.id
        $searchCondition
        ORDER BY da.issued_date DESC
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