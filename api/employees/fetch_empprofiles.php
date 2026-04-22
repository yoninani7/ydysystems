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

    // Pagination parameters
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(5, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    // Search parameter
    $search = trim($_GET['search'] ?? '');
    // Build base WHERE clause
        $baseCondition = " WHERE e.status = 'Active' AND e.deleted_at IS NULL ";
        $params = [];

        // Append search condition if present
        if ($search !== '') {
            $searchCondition = " AND (
                CONCAT(e.first_name, ' ', IFNULL(e.middle_name, ''), ' ', e.last_name) LIKE ? OR
                jp.title LIKE ? OR
                d.name LIKE ?
            )";
            $searchTerm = '%' . $search . '%';
            $params = array_fill(0, 3, $searchTerm);
        } else {
            $searchCondition = '';
        }

        // Count query
        $countSql = "SELECT COUNT(*) FROM employees e
                    LEFT JOIN users u ON e.id = u.employee_id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN branches b ON e.branch_id = b.id
                    LEFT JOIN job_positions jp ON e.job_position_id = jp.id
                    LEFT JOIN employment_types et ON e.employment_type_id = et.id
                    $baseCondition $searchCondition";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Data query
        $sql = "
            SELECT 
                e.employee_id AS id,
                e.first_name AS fname,
                e.middle_name AS mname,
                e.last_name AS lname,
                u.username AS uname,
                e.gender,
                e.date_of_birth AS dob,
                e.hire_date AS hire,
                e.status,
                e.marital_status AS marital,
                e.personal_phone AS phone,
                e.personal_email AS email,
                d.name AS dept,
                jp.title AS position,
                b.name AS branch,
                et.name AS type,
                e.bank_name AS bankname,
                e.bank_account AS bankacc,
                e.tin,
                e.profile_photo AS photo,
                e.created_at AS created,
                COALESCE(updater.username, 'System') AS updated_by_name
            FROM employees e
            LEFT JOIN users u ON e.id = u.employee_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN branches b ON e.branch_id = b.id
            LEFT JOIN job_positions jp ON e.job_position_id = jp.id
            LEFT JOIN employment_types et ON e.employment_type_id = et.id
            LEFT JOIN users updater ON e.updated_by = updater.id
            $baseCondition $searchCondition
            ORDER BY e.id DESC
            LIMIT ? OFFSET ?
        ";

    $stmt = $pdo->prepare($sql);
    
    // Combine all parameters: search terms (if any) + limit + offset
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    
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

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}