<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';
header('Content-Type: application/json');

// 1. Authentication
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 3. CSRF Verification
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
    exit;
}

// 4. Input Processing
$docTypeId   = !empty($_POST['doc_type_id']) ? (int)$_POST['doc_type_id'] : null;
$employeeId  = trim($_POST['employee_id'] ?? '');
$file        = $_FILES['file'] ?? null;

if (!$docTypeId || $employeeId === '' || !$file) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload error. Code: ' . $file['error']]);
    exit;
}

$maxSize = 10 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10 MB limit.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
if (!in_array($mime, $allowedMimes)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Only PDF, JPG, and PNG files are allowed.']);
    exit;
}

try {
    $pdo = get_pdo();
    
    // Find internal employee ID and full name
    $stmt = $pdo->prepare("SELECT id, first_name, middle_name, last_name FROM employees WHERE employee_id = ? AND deleted_at IS NULL");
    $stmt->execute([$employeeId]);
    $emp = $stmt->fetch();
    if (!$emp) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
        exit;
    }
    $internalEmpId = (int)$emp['id'];
    
    // Build safe folder name: First_Middle_Last_EMP-ID
    $fullName = trim($emp['first_name'] . ' ' . ($emp['middle_name'] ?? '') . ' ' . $emp['last_name']);
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fullName) . '_' . $employeeId;
    
    // Verify document type exists
    $stmt = $pdo->prepare("SELECT id FROM document_types WHERE id = ?");
    $stmt->execute([$docTypeId]);
    if (!$stmt->fetch()) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid document type.']);
        exit;
    }
    
    // Create employee-specific folder
    $baseVaultDir = __DIR__ . '/../../uploads/vault/';
    $empDir = $baseVaultDir . $safeName . '/';
    if (!is_dir($empDir)) {
        if (!mkdir($empDir, 0755, true)) {
            throw new Exception("Cannot create employee vault folder. Check permissions.");
        }
    }
    
    // Build file name: documentCode_timestamp.ext
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $docStmt = $pdo->prepare("SELECT code FROM document_types WHERE id = ?");
    $docStmt->execute([$docTypeId]);
    $docCode = $docStmt->fetchColumn() ?: 'doc';
    $safeFileName = $docCode . '_' . time() . '.' . $ext;
    $destPath = $empDir . $safeFileName;
    
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new Exception("Failed to save uploaded file.");
    }
    
    // Relative path stored in DB: uploads/vault/FolderName/filename.ext
    $relativePath = 'uploads/vault/' . $safeName . '/' . $safeFileName;
    $fileSizeKb   = round($file['size'] / 1024, 2);
    
    // Update or insert in employee_documents
    $existing = $pdo->prepare("SELECT id FROM employee_documents WHERE employee_id = ? AND document_type_id = ?");
    $existing->execute([$internalEmpId, $docTypeId]);
    $existingRow = $existing->fetch();
    
    $pdo->beginTransaction();
    
    if ($existingRow) {
        $updateStmt = $pdo->prepare("UPDATE employee_documents SET 
            file_name = :fname, file_path = :fpath, file_size_kb = :fsize, 
            mime_type = :mime, status = 'Uploaded', updated_by = :uid, 
            uploaded_at = NOW(), updated_at = NOW() 
            WHERE id = :id");
        $updateStmt->execute([
            ':fname' => $file['name'],
            ':fpath' => $relativePath,
            ':fsize' => $fileSizeKb,
            ':mime'  => $mime,
            ':uid'   => $_SESSION['user_id'],
            ':id'    => $existingRow['id']
        ]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO employee_documents 
            (employee_id, document_type_id, file_name, file_path, file_size_kb, mime_type, status, updated_by, uploaded_at, updated_at) 
            VALUES (:eid, :dtid, :fname, :fpath, :fsize, :mime, 'Uploaded', :uid, NOW(), NOW())");
        $insertStmt->execute([
            ':eid'   => $internalEmpId,
            ':dtid'  => $docTypeId,
            ':fname' => $file['name'],
            ':fpath' => $relativePath,
            ':fsize' => $fileSizeKb,
            ':mime'  => $mime,
            ':uid'   => $_SESSION['user_id']
        ]);
    }
    
    // Audit log
    $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at) 
                                VALUES (?, ?, 'UPLOAD', 'Document Vault', ?, ?, NOW())");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        "Employee: $employeeId, Document Type ID: $docTypeId",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Document uploaded successfully.',
        'file_name' => $file['name']
    ]);
    
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (isset($destPath) && file_exists($destPath)) {
        unlink($destPath);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}