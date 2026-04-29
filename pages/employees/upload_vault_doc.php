<?php
declare(strict_types=1);
define('IS_API', true); // Prevents inactivity modal HTML from corrupting JSON

require_once '../../config.php';  

header('Content-Type: application/json');

// 1. Authentication Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in again.']);
    exit;
}

// 2. CSRF Verification (Uses your config.php function)
if (!csrf_verify()) {
    echo json_encode(['success' => false, 'message' => 'Security token invalid (CSRF).']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = get_pdo();
        
        $employee_id = (int)($_POST['employee_id'] ?? 0); // Primary Key ID
        $doc_type_id = (int)($_POST['doc_type_id'] ?? 0);
        $user_id     = (int)$_SESSION['user_id'];
        $user_name   = $_SESSION['username'] ?? 'System';

        if ($employee_id === 0 || $doc_type_id === 0 || !isset($_FILES['file'])) {
            throw new Exception("Required data or file is missing.");
        }

        $file = $_FILES['file'];
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed (Code: " . $file['error'] . ")");
        }
        if ($file['size'] > $max_size) throw new Exception("File exceeds 10MB limit.");
        if (!in_array($file['type'], $allowed_types)) throw new Exception("Invalid file type. Use PDF, JPG, or PNG.");

        // Storage path logic (ensure this directory exists and is writable)
        $upload_dir = __DIR__ . '/../../uploads/vault/'; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate filename: doc_EMP_TYPE_TIME.ext
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safe_name = "vault_" . $employee_id . "_" . $doc_type_id . "_" . time() . "." . $extension;
        $target_path = $upload_dir . $safe_name;

       if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Check for existing doc to update or insert
            $check = $pdo->prepare("SELECT id, file_path FROM employee_documents WHERE employee_id = ? AND document_type_id = ?");
            $check->execute([$employee_id, $doc_type_id]);
            $existing = $check->fetch();

           if ($existing) {
                // Delete old physical file if it exists
                if (file_exists($upload_dir . $existing['file_path'])) {
                    @unlink($upload_dir . $existing['file_path']);
                }

                $stmt = $pdo->prepare("UPDATE employee_documents SET file_name = ?, file_path = ?, file_size_kb = ?, mime_type = ?, status = 'Uploaded', updated_by = ? WHERE id = ?");
                $stmt->execute([$file['name'], $safe_name, round($file['size']/1024), $file['type'], $user_id, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO employee_documents (employee_id, document_type_id, file_name, file_path, file_size_kb, mime_type, status, updated_by) VALUES (?, ?, ?, ?, ?, ?, 'Uploaded', ?)");
                $stmt->execute([$employee_id, $doc_type_id, $file['name'], $safe_name, round($file['size']/1024), $file['type'], $user_id]);
            }
            
            // Audit Log (Uses your table structure)
            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address) 
                                   VALUES (?, ?, 'UPDATE', 'Document Vault', ?, ?)");
            $audit->execute([$user_id, $user_name, "EmpID: $employee_id | DocID: $doc_type_id", $_SERVER['REMOTE_ADDR']]);

            echo json_encode(['success' => true, 'file_name' => $file['name']]);
        } else {
            throw new Exception("Failed to save file to server storage.");
        }

    } catch (Exception $e) {
        error_log("Vault Upload Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}