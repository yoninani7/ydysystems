<?php
declare(strict_types=1);
define('IS_API', true);
require_once '../../config.php';   // Adjust path if needed
header('Content-Type: application/json');

// 1. Authentication check
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 3. CSRF protection (optional but recommended)
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

// 4. Get POST data (from the modal)
$branch_name   = trim($_POST['branch_name'] ?? '');
$manager_name  = trim($_POST['branch_manager'] ?? '');   // this comes from the autocomplete input
$status        = trim($_POST['branch_status'] ?? 'Active');
$phone         = trim($_POST['branch_phone'] ?? '');
$email         = trim($_POST['branch_email'] ?? '');
$city          = trim($_POST['branch_city'] ?? '');
$address       = trim($_POST['branch_address'] ?? '');

// 5. Validate required fields
if ($branch_name === '') {
    echo json_encode(['success' => false, 'message' => 'Branch Name is required']);
    exit;
}

try {
    $pdo = get_pdo(); 
    // --- NEW: Check for duplicate branch name ---
    $checkStmt = $pdo->prepare("SELECT id FROM branches WHERE name = :name AND deleted_at IS NULL");
    $checkStmt->execute([':name' => $branch_name]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A branch with this name already exists.']);
        exit;
    }

    // 6. (Optional) Resolve manager name to employee_id
    $manager_id = null;
    if ($manager_name !== '') {
        $stmt = $pdo->prepare("
            SELECT id FROM employees 
            WHERE CONCAT(first_name, ' ', last_name) = :name 
               OR CONCAT(first_name, ' ', middle_name, ' ', last_name) = :name
            LIMIT 1
        ");
        $stmt->execute([':name' => $manager_name]);
        $manager = $stmt->fetch();
        if ($manager) {
            $manager_id = $manager['id'];
        } else {
            // Optionally warn but still allow creation without manager
            // error_log("Manager not found: " . $manager_name);
        }
    }

    // 7. Insert new branch
    $sql = "
        INSERT INTO branches (
            name, city, address, phone, email, manager_id, status, created_at, updated_at
        ) VALUES (
            :name, :city, :address, :phone, :email, :manager_id, :status, NOW(), NOW()
        )
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'       => $branch_name,
        ':city'       => $city,
        ':address'    => $address,
        ':phone'      => $phone,
        ':email'      => $email,
        ':manager_id' => $manager_id,
        ':status'     => $status
    ]);

    $newId = $pdo->lastInsertId();

    // 8. Return success
    echo json_encode([
        'success' => true,
        'message' => 'Branch created successfully',
        'branch'  => [
            'id'   => $newId,
            'name' => $branch_name
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}