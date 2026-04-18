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
            i.intern_code LIKE ? OR
            i.full_name LIKE ? OR
            i.institution LIKE ? OR
            d.name LIKE ? OR
            CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 5, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM internships i
                 LEFT JOIN departments d ON i.department_id = d.id
                 LEFT JOIN employees e ON i.mentor_id = e.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            i.id,
            i.intern_code AS id_code,
            i.full_name AS name,
            i.institution AS uni,
            COALESCE(d.name, 'Unassigned') AS dept,
            CONCAT(e.first_name, ' ', e.last_name) AS mentor,
            i.start_date AS start,
            i.end_date AS end,
            i.evaluation_score AS eval,
            i.status
        FROM internships i
        LEFT JOIN departments d ON i.department_id = d.id
        LEFT JOIN employees e ON i.mentor_id = e.id
        $searchCondition
        ORDER BY i.start_date DESC
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