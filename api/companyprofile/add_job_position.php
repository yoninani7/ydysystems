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

$job_title = trim($_POST['job_title'] ?? '');
$dept_id   = !empty($_POST['job_dept_id']) ? (int)$_POST['job_dept_id'] : null;
$status    = trim($_POST['job_status'] ?? 'Active');

if ($job_title === '') {
    echo json_encode(['success' => false, 'message' => 'Job Title is required']);
    exit;
}

if (!$dept_id) {
    echo json_encode(['success' => false, 'message' => 'Please select a Department']);
    exit;
}

try {
    $pdo = get_pdo();

    // Check for duplicate job title within the same department (optional)
    $checkStmt = $pdo->prepare("SELECT id FROM job_positions WHERE title = :title AND department_id = :dept_id AND deleted_at IS NULL");
    $checkStmt->execute([':title' => $job_title, ':dept_id' => $dept_id]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This job position already exists in the selected department']);
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

    echo json_encode([
        'success' => true,
        'message' => 'Job position created successfully',
        'position' => ['id' => $newId, 'title' => $job_title]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>