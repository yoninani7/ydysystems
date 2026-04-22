<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';
header('Content-Type: application/json');

// 1. Authentication
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
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
$dept_id     = !empty($_POST['dept_id']) ? (int)$_POST['dept_id'] : null;
$dept_name   = trim($_POST['dept_name'] ?? '');
$head_id     = !empty($_POST['dept_head_id']) ? (int)$_POST['dept_head_id'] : null;
$status      = trim($_POST['dept_status'] ?? 'Active');

// 5. Validation
if (!$dept_id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Department ID is required.']);
    exit;
}
if ($dept_name === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Department name is required.']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Check for duplicate name (excluding current department)
    $checkStmt = $pdo->prepare("SELECT id FROM departments WHERE name = :name AND id != :dept_id AND deleted_at IS NULL");
    $checkStmt->execute([':name' => $dept_name, ':dept_id' => $dept_id]);
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'A department with this name already exists.']);
        exit;
    }

    // Update
    $sql = "UPDATE departments 
            SET name = :name, head_employee_id = :head_id, status = :status, updated_at = NOW()
            WHERE id = :dept_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'    => $dept_name,
        ':head_id' => $head_id,
        ':status'  => $status,
        ':dept_id' => $dept_id
    ]);

    // Audit Log
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at)
        VALUES (?, ?, 'UPDATE', 'Company Structure', ?, ?, NOW())
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        "Department: $dept_name",
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Department updated successfully.'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
    ]);
}