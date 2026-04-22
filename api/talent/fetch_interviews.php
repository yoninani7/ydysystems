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
            c.full_name LIKE ? OR
            jv.title LIKE ? OR
            CONCAT(e.first_name, ' ', e.last_name) LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 3, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM interviews i
                 JOIN candidates c ON i.candidate_id = c.id
                 LEFT JOIN job_vacancies jv ON i.vacancy_id = jv.id
                 LEFT JOIN employees e ON i.interviewer_id = e.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
     
    $sql = "
        SELECT 
            i.id,
            c.full_name AS candidate,
            jv.title AS position,
            CONCAT(e.first_name, ' ', e.last_name) AS interviewer,
            i.interview_date AS date,
            i.interview_time AS time,
            i.mode,
            i.result,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM interviews i
        JOIN candidates c ON i.candidate_id = c.id
        LEFT JOIN job_vacancies jv ON i.vacancy_id = jv.id
        LEFT JOIN employees e ON i.interviewer_id = e.id
        LEFT JOIN users u ON i.updated_by = u.id
        $searchCondition
        ORDER BY i.interview_date ASC, i.interview_time ASC
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