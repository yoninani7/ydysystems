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
            ts.course_name LIKE ? OR
            d.name LIKE ? OR
            ts.venue LIKE ? OR
            CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 4, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM training_schedules ts
                 LEFT JOIN departments d ON ts.department_id = d.id
                 LEFT JOIN employees e ON ts.trainer_id = e.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            ts.id,
            ts.course_name AS course,
            COALESCE(d.name, 'All Depts') AS dept,
            CONCAT(e.first_name, ' ', e.last_name) AS trainer,
            ts.training_date AS date,
            ts.training_time AS time,
            ts.venue,
            ts.total_seats,
            ts.enrolled_seats,
            ts.status,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM training_schedules ts
        LEFT JOIN departments d ON ts.department_id = d.id
        LEFT JOIN employees e ON ts.trainer_id = e.id
        LEFT JOIN users u ON ts.updated_by = u.id
        $searchCondition
        ORDER BY ts.training_date ASC
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