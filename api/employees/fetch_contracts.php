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

    // Verify table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'employee_contracts'");
    if ($tableCheck->rowCount() === 0) {
        throw new Exception("Employee_contracts table does not exist");
    }

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    $search = trim($_GET['search'] ?? '');
    $searchParams = [];
    $searchCondition = '';

    if ($search !== '') {
        $searchCondition = " AND (
            CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) LIKE ? OR
            COALESCE(d.name, 'Unassigned') LIKE ?
        )";
        $searchTerm = '%' . $search . '%';
        $searchParams = [$searchTerm, $searchTerm];
    }

    // Count query
    $countSql = "
        SELECT COUNT(*)
        FROM employee_contracts ec
        JOIN employees e ON ec.employee_id = e.id
        LEFT JOIN departments d ON e.department_id = d.id
        WHERE e.status = 'Active' 
          AND e.deleted_at IS NULL
          AND ec.end_date IS NOT NULL
        $searchCondition
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($searchParams);
    $total = (int)$stmt->fetchColumn();

    // Data query
    $sql = "
    SELECT 
        CONCAT(e.first_name, ' ', e.last_name) AS name,
        COALESCE(d.name, 'Unassigned') AS dept,
        ec.start_date AS start,
        ec.end_date AS expiry,
        DATEDIFF(ec.end_date, CURDATE()) AS days,
        COALESCE(u.username, 'System') AS updated_by_name
    FROM employee_contracts ec
    JOIN employees e ON ec.employee_id = e.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN users u ON ec.updated_by = u.id
    WHERE e.status = 'Active' 
      AND e.deleted_at IS NULL
      AND ec.end_date IS NOT NULL
    $searchCondition
    ORDER BY ec.end_date ASC
    LIMIT ? OFFSET ?
";

    $stmt = $pdo->prepare($sql);
    $allParams = array_merge($searchParams, [$limit, $offset]);
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

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}