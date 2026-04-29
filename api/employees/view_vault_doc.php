<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$empId     = $_GET['emp_id'] ?? '';
$docTypeId = (int)($_GET['doc_type_id'] ?? 0);

if (!$empId || !$docTypeId) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

try {
    $pdo = get_pdo();
    
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empId]);
    $emp = $stmt->fetch();
    if (!$emp) {
        http_response_code(404);
        echo 'Employee not found';
        exit;
    }
    $internalEmpId = (int)$emp['id'];
    
    $stmt = $pdo->prepare("SELECT file_path, file_name, mime_type FROM employee_documents 
                           WHERE employee_id = ? AND document_type_id = ? AND status = 'Uploaded' 
                           ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$internalEmpId, $docTypeId]);
    $doc = $stmt->fetch();
    
    if (!$doc) {
        http_response_code(404);
        echo 'Document not found';
        exit;
    }
    
    $fullPath = __DIR__ . '/../../' . $doc['file_path'];
    
    if (!file_exists($fullPath)) {
        http_response_code(404);
        echo 'File does not exist on disk';
        exit;
    }
    
    $fileName = $doc['file_name'] ?: basename($fullPath);
    $mimeType = $doc['mime_type'] ?: mime_content_type($fullPath);
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: private, max-age=3600');
    
    readfile($fullPath);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Internal server error';
    exit;
}