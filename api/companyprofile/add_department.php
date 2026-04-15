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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

$dept_name = trim($_POST['dept_name'] ?? '');
$head_id   = !empty($_POST['dept_head_id']) ? (int)$_POST['dept_head_id'] : null;
$status    = trim($_POST['dept_status'] ?? 'Active');

if ($dept_name === '') {
    echo json_encode(['success' => false, 'message' => 'Department name is required']);
    exit;
}

try {
    $pdo = get_pdo();

    // Duplicate check
    $checkStmt = $pdo->prepare("SELECT id FROM departments WHERE name = :name AND deleted_at IS NULL");
    $checkStmt->execute([':name' => $dept_name]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A department with this name already exists']);
        exit;
    }

    // Insert (branch_id NULL as modal doesn't ask for branch)
    $sql = "INSERT INTO departments (name, head_employee_id, branch_id, status, created_at, updated_at)
            VALUES (:name, :head_id, NULL, :status, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'    => $dept_name,
        ':head_id' => $head_id,
        ':status'  => $status
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Department created successfully',
        'department' => ['id' => $newId, 'name' => $dept_name]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>