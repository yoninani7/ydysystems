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
            d.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 2, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM feedback_360 f
                 JOIN employees e ON f.subject_employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            f.id,
            CONCAT(e.first_name, ' ', e.last_name) AS subject,
            COALESCE(d.name, 'N/A') AS dept,
            f.total_respondents AS total,
            f.completed_respondents AS complete,
            f.average_score AS avg,
            f.status,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM feedback_360 f
        JOIN employees e ON f.subject_employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN users u ON f.updated_by = u.id
        $searchCondition
        ORDER BY f.created_at DESC
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