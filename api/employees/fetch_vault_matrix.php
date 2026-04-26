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

    // 1. Hardcoded document types matching database structure
    $docTypes = [
        ['id' => 1, 'code' => 'contract', 'name' => 'Signed Employment Contract', 'category' => 'Legal', 'is_mandatory' => true],
        ['id' => 2, 'code' => 'cv', 'name' => 'Curriculum Vitae (CV)', 'category' => 'Identity', 'is_mandatory' => true],
        ['id' => 3, 'code' => 'academic', 'name' => 'Academic Credentials', 'category' => 'Education', 'is_mandatory' => true],
        ['id' => 4, 'code' => 'clearance', 'name' => 'Clearance / Release Letter', 'category' => 'History', 'is_mandatory' => true],
        ['id' => 5, 'code' => 'experience', 'name' => 'Experience Letters', 'category' => 'History', 'is_mandatory' => false],
        ['id' => 6, 'code' => 'coc', 'name' => 'Certificate of Competence (COC)', 'category' => 'Professional', 'is_mandatory' => false],
        ['id' => 7, 'code' => 'guarantor', 'name' => 'Guarantor Form & ID', 'category' => 'Legal', 'is_mandatory' => true],
        ['id' => 8, 'code' => 'nda', 'name' => 'Confidentiality / NDA Agreement', 'category' => 'Compliance', 'is_mandatory' => true],
        ['id' => 9, 'code' => 'handbook', 'name' => 'Acknowledgments', 'category' => 'Compliance', 'is_mandatory' => true],
        ['id' => 10, 'code' => 'national_id', 'name' => 'National ID / Passport Copy', 'category' => 'Identity', 'is_mandatory' => true],
        ['id' => 11, 'code' => 'tin', 'name' => 'TIN Certification Document', 'category' => 'Tax', 'is_mandatory' => true],
        ['id' => 12, 'code' => 'medical', 'name' => 'Health & Fitness Clearance', 'category' => 'Compliance', 'is_mandatory' => true]
    ];

    // 2. Count query (unchanged)
    $countSql = "SELECT COUNT(DISTINCT e.id) 
                FROM employees e 
                JOIN v_employee_compliance_status v ON e.id = v.employee_id
                $searchCondition";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // 3. Enhanced data query with detailed document information
    $sql = "
        SELECT 
            e.employee_id AS empId,
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            v.compliance_percentage AS progress,
            GROUP_CONCAT(DISTINCT 
                CASE WHEN ed.status = 'Uploaded' THEN ed.document_type_id END
            ) AS uploaded_ids,
            GROUP_CONCAT(DISTINCT 
                CONCAT(ed.document_type_id, ':', COALESCE(ed.file_name, ''), ':', COALESCE(ed.updated_at, ''))
            ) AS document_details,
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
        $docDetails = [];
        
        // Parse document details if available
        if (!empty($emp['document_details'])) {
            $details = explode(',', $emp['document_details']);
            foreach ($details as $detail) {
                if (!empty($detail)) {
                    $parts = explode(':', $detail);
                    if (count($parts) >= 3) {
                        $docDetails[$parts[0]] = [
                            'file_name' => $parts[1],
                            'updated_at' => $parts[2]
                        ];
                    }
                }
            }
        }
        
        $row = [
            'empId'    => $emp['empId'],
            'name'     => $emp['name'],
            'progress' => (float)$emp['progress'],
            'updated_by' => $emp['updated_by_name'] ?? null
        ];
        
        // Create detailed columns for each document type
        foreach ($docTypes as $dt) {
            $hasDoc = in_array((string)$dt['id'], $uploaded);
            $row['doc_' . $dt['id']] = $hasDoc;
            
            // Add additional document detail columns
            $row['doc_' . $dt['id'] . '_name'] = $hasDoc && isset($docDetails[$dt['id']]) ? $docDetails[$dt['id']]['file_name'] : null;
            $row['doc_' . $dt['id'] . '_date'] = $hasDoc && isset($docDetails[$dt['id']]) ? $docDetails[$dt['id']]['updated_at'] : null;
            $row['doc_' . $dt['id'] . '_mandatory'] = $dt['is_mandatory'];
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