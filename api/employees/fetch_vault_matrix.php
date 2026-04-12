<?php
declare(strict_types=1);
define('IS_API', true);          
require_once '../../config.php'; 
header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    
    // 1. Get the global document requirements defined in the system
    $typeStmt = $pdo->query("SELECT id, code, name, category FROM document_types WHERE is_mandatory = 1 ORDER BY id ASC");
    $docTypes = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Employee Compliance and a list of what they HAVE uploaded
    // We use GROUP_CONCAT to get a comma-separated list of document_type_ids for each employee
    $stmt = $pdo->query("
        SELECT 
            e.employee_id AS empId,
            CONCAT(e.first_name, ' ', e.last_name) AS name,
            v.compliance_percentage AS progress,
            GROUP_CONCAT(ed.document_type_id) AS uploaded_ids
        FROM employees e
        JOIN v_employee_compliance_status v ON e.id = v.employee_id
        LEFT JOIN employee_documents ed ON e.id = ed.employee_id
        WHERE e.status = 'Active'
        GROUP BY e.id
        ORDER BY v.compliance_percentage ASC
    ");
    
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'docTypes' => $docTypes, 
        'employees' => $employees
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}