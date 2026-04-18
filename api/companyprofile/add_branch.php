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
$branch_name = trim($_POST['branch_name'] ?? '');
$manager_id  = !empty($_POST['branch_manager_id']) ? (int)$_POST['branch_manager_id'] : null;
$status      = trim($_POST['branch_status'] ?? 'Active');
$phone       = trim($_POST['branch_phone'] ?? '');
$email       = trim($_POST['branch_email'] ?? '');
$city        = trim($_POST['branch_city'] ?? '');
$address     = trim($_POST['branch_address'] ?? '');

// 5. Validation
if ($branch_name === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Branch name is required.']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Duplicate check
    $checkStmt = $pdo->prepare("SELECT id FROM branches WHERE name = :name AND deleted_at IS NULL");
    $checkStmt->execute([':name' => $branch_name]);
    if ($checkStmt->fetch()) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'A branch with this name already exists.']);
        exit;
    }

    $sql = "INSERT INTO branches (name, manager_id, status, phone, email, city, address, created_at, updated_at)
            VALUES (:name, :manager_id, :status, :phone, :email, :city, :address, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'       => $branch_name,
        ':manager_id' => $manager_id,
        ':status'     => $status,
        ':phone'      => $phone,
        ':email'      => $email,
        ':city'       => $city,
        ':address'    => $address
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
        "Branch: $branch_name",
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Branch created successfully.',
        'branch' => ['id' => $newId, 'name' => $branch_name]
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