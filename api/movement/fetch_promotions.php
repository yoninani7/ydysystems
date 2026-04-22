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
            p.change_type LIKE ? OR
            jp1.title LIKE ? OR
            jp2.title LIKE ? OR
            d.name LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $params = array_fill(0, 5, $searchTerm);
    }

    $countSql = "SELECT COUNT(*) FROM promotions p
                 JOIN employees e ON p.employee_id = e.id
                 LEFT JOIN job_positions jp1 ON p.from_position_id = jp1.id
                 LEFT JOIN job_positions jp2 ON p.to_position_id = jp2.id
                 LEFT JOIN departments d ON p.to_department_id = d.id
                 $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    
    // We join job_positions twice: once for the old role and once for the new role
        $sql = "    
        SELECT 
            CONCAT(e.first_name, ' ', e.last_name) AS emp,
            p.change_type AS type,
            jp1.title AS from_pos,
            jp2.title AS to_pos,
            d.name AS dept,
            p.old_salary AS sal_from,
            p.new_salary AS sal_to,
            p.effective_date AS eff_date,
            p.status,
            COALESCE(u.username, 'System') AS updated_by_name
        FROM promotions p
        JOIN employees e ON p.employee_id = e.id
        LEFT JOIN job_positions jp1 ON p.from_position_id = jp1.id
        LEFT JOIN job_positions jp2 ON p.to_position_id = jp2.id
        LEFT JOIN departments d ON p.to_department_id = d.id
        LEFT JOIN users u ON p.updated_by = u.id
        $searchCondition
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);

    echo json_encode([
        'success' => true,
        'data'    => $stmt->fetchAll(PDO::FETCH_ASSOC),
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