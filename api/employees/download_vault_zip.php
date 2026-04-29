<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';

// 1. Authentication
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$empId = $_GET['emp_id'] ?? '';
if (!$empId) {
    http_response_code(400);
    echo 'Missing employee ID.';
    exit;
}

try {
    $pdo = get_pdo();
    
    // 2. Find employee and build folder name exactly like upload_vault_doc.php
    $stmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name FROM employees WHERE employee_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empId]);
    $emp = $stmt->fetch();
    if (!$emp) {
        http_response_code(404);
        echo 'Employee not found.';
        exit;
    }
    
    $fullName = trim($emp['first_name'] . ' ' . ($emp['middle_name'] ?? '') . ' ' . $emp['last_name']);
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fullName) . '_' . $empId;
    $empDir = __DIR__ . '/../../uploads/vault/' . $safeName;
    
    if (!is_dir($empDir)) {
        http_response_code(404);
        echo 'No vault folder found for this employee.';
        exit;
    }
    
    // 3. Collect all files (exclude sub‑directories for safety)
    $allEntries = scandir($empDir);
    $validFiles = [];
    foreach ($allEntries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $fullPath = $empDir . '/' . $entry;
        if (is_file($fullPath)) {
            $validFiles[] = $fullPath;
        }
    }
    
    if (empty($validFiles)) {
        http_response_code(404);
        echo 'No uploaded documents found for this employee.';
        exit;
    }
    
    // 4. Create the zip archive
    $zipFileName = 'vault_' . $empId . '_' . date('Ymd_His') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipFileName;
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Could not create zip file.');
    }
    
    $added = 0;
    foreach ($validFiles as $filePath) {
        $zip->addFile($filePath, basename($filePath));
        $added++;
    }
    $zip->close();
    
    if ($added === 0) {
        unlink($zipPath);
        http_response_code(500);
        echo 'No files could be added to zip.';
        exit;
    }
    
    // 5. Audit log
    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at) 
                                VALUES (?, ?, 'EXPORT', 'Document Vault', ?, ?, NOW())");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'] ?? 'System',
        "Download zip for $empId",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // 6. Send zip to browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    
    // Clean any previous output buffers to avoid corruption
    if (ob_get_level()) {
        ob_end_clean();
    }
    flush();
    
    readfile($zipPath);
    unlink($zipPath);
    exit;
    
} catch (Exception $e) {
    if (isset($zipPath) && file_exists($zipPath)) {
        unlink($zipPath);
    }
    http_response_code(500);
    echo 'Server error: ' . $e->getMessage();
    exit;
}