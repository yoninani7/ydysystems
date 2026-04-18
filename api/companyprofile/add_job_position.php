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
$job_title = trim($_POST['job_title'] ?? '');
$dept_id   = !empty($_POST['job_dept_id']) ? (int)$_POST['job_dept_id'] : null;
$status    = trim($_POST['job_status'] ?? 'Active');

// 5. Validation
if ($job_title === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Job Title is required.']);
    exit;
}

if (!$dept_id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please select a Department.']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Duplicate check
    $checkStmt = $pdo->prepare("SELECT id FROM job_positions WHERE title = :title AND department_id = :dept_id AND deleted_at IS NULL");
    $checkStmt->execute([':title' => $job_title, ':dept_id' => $dept_id]);
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'This job position already exists in the selected department.']);
        exit;
    }

    $sql = "INSERT INTO job_positions (title, department_id, status, created_at, updated_at)
            VALUES (:title, :dept_id, :status, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title'   => $job_title,
        ':dept_id' => $dept_id,
        ':status'  => $status
    ]);

    $newId = $pdo->lastInsertId();

    // 6. Audit Logging
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at)
        VALUES (?, ?, 'CREATE', 'Company Structure', ?, ?, NOW())
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        "Job Position: $job_title",
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Job position created successfully.',
        'position' => ['id' => $newId, 'title' => $job_title]
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
?>