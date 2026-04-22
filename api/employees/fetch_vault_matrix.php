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
    $searchCondition = " WHERE e.status = 'Active' ";
    $params = [];

    if ($search !== '') {
        $searchCondition .= " AND (CONCAT(e.first_name, ' ', e.last_name) LIKE ?) ";
        $searchTerm = '%' . $search . '%';
        $params = [$searchTerm];
    }

    // 1. Get the global document requirements defined in the system 
        $typeStmt = $pdo->query("SELECT id, code, name, category FROM document_types WHERE is_mandatory = 1 ORDER BY id ASC");
        $docTypes = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Count query (unchanged)
        $countSql = "SELECT COUNT(DISTINCT e.id) 
                    FROM employees e 
                    JOIN v_employee_compliance_status v ON e.id = v.employee_id
                    $searchCondition";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // 3. Data query – now includes most recent updater per employee
        $sql = "
            SELECT 
                e.employee_id AS empId,
                CONCAT(e.first_name, ' ', e.last_name) AS name,
                v.compliance_percentage AS progress,
                GROUP_CONCAT(DISTINCT ed.document_type_id) AS uploaded_ids,
                (
                    SELECT u.username 
                    FROM employee_documents ed2
                    LEFT JOIN users u ON ed2.updated_by = u.id
                    WHERE ed2.employee_id = e.id 
                    ORDER BY ed2.updated_at DESC 
                    LIMIT 1
                ) AS updated_by_name
            FROM employees e
            JOIN v_employee_compliance_status v ON e.id = v.employee_id
            LEFT JOIN employee_documents ed ON e.id = ed.employee_id
            $searchCondition
            GROUP BY e.id
            ORDER BY v.compliance_percentage ASC
            LIMIT ? OFFSET ?
        ";
    
    $stmt = $pdo->prepare($sql);
    $allParams = $params;
    $allParams[] = $limit;
    $allParams[] = $offset;
    $stmt->execute($allParams);
    
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($employees as $emp) {
        $uploaded = explode(',', (string)($emp['uploaded_ids'] ?? ''));
        $row = [
            'empId'    => $emp['empId'],
            'name'     => $emp['name'],
            'progress' => (float)$emp['progress']
        ];
        // Create a boolean flag for each document type
        foreach ($docTypes as $dt) {
            $row['doc_' . $dt['id']] = in_array((string)$dt['id'], $uploaded);
        }
        $data[] = $row;
    }

    echo json_encode([
        'success' => true, 
        'docTypes' => $docTypes, 
        'data' => $data,
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