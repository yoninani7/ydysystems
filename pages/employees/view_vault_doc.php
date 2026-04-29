<?php
declare(strict_types=1);
define('IS_API', true);  
require_once '../../config.php';

// 1. Basic Session Auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Unauthorized access.");
}

$emp_id = (int)($_GET['emp_id'] ?? 0);
$doc_type_id = (int)($_GET['doc_type_id'] ?? 0);

if ($emp_id === 0 || $doc_type_id === 0) {
    die("Invalid parameters.");
}

try {
    $pdo = get_pdo();
    
    // Fetch file path
    $stmt = $pdo->prepare("SELECT file_path, mime_type, file_name 
                          FROM employee_documents 
                          WHERE employee_id = ? AND document_type_id = ? LIMIT 1");
    $stmt->execute([$emp_id, $doc_type_id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        die("File record not found.");
    }

    $file_path = __DIR__ . '/../../uploads/vault/' . $doc['file_path'];

    if (file_exists($file_path)) {
        // Log the view action to audit logs
        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address) 
                                VALUES (?, ?, 'VIEW', 'Document Vault', ?, ?)");
        $audit->execute([$_SESSION['user_id'], $_SESSION['username'], "EmpID: $emp_id DocID: $doc_type_id", $_SERVER['REMOTE_ADDR']]);

        // Clean buffers to ensure only file data is sent
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: inline; filename="' . $doc['file_name'] . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        
        readfile($file_path);
        exit;
    } else {
        die("Physical file not found on server.");
    }

} catch (Exception $e) {
    error_log("Vault View Error: " . $e->getMessage());
    die("An error occurred while retrieving the file.");
}