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
            jv.title LIKE ? OR
            d.name LIKE ? OR
            b.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 3, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM job_vacancies jv
                 LEFT JOIN departments d ON jv.department_id = d.id
                 LEFT JOIN branches b ON jv.branch_id = b.id
                 LEFT JOIN employment_types et ON jv.employment_type_id = et.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

   $sql = "
    SELECT 
        jv.id,
        jv.title,
        d.name AS dept,
        b.name AS branch,
        et.name AS type,
        jv.posted_date AS posted,
        jv.deadline_date AS deadline,
        jv.description,
        jv.requirements,
        jv.status,
        COALESCE(u.username, 'System') AS updated_by_name
    FROM job_vacancies jv
    LEFT JOIN departments d ON jv.department_id = d.id
    LEFT JOIN branches b ON jv.branch_id = b.id
    LEFT JOIN employment_types et ON jv.employment_type_id = et.id
    LEFT JOIN users u ON jv.updated_by = u.id
    $searchCondition
    ORDER BY jv.posted_date DESC
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