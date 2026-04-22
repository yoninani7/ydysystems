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
$employee_id = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
$decision    = trim($_POST['decision'] ?? ''); // 'Hire', 'Extend', 'Reject'
$notes       = trim($_POST['notes'] ?? '');

// 5. Validation
if (!$employee_id) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Employee ID is required.']);
    exit;
}

$allowed_decisions = ['Hire', 'Extend', 'Reject'];
if (!in_array($decision, $allowed_decisions)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid decision.']);
    exit;
}

try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Find the latest probation record for this employee
    $stmt = $pdo->prepare("
        SELECT id, employee_id, start_date, end_date, status 
        FROM probation_records 
        WHERE employee_id = ? 
        ORDER BY end_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$employee_id]);
    $probation = $stmt->fetch();

    if (!$probation) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No probation record found for this employee.']);
        exit;
    }

    // Build notes addition
    $notesAddition = sprintf("[%s by %s on %s]\n%s", 
        $decision, 
        $_SESSION['username'] ?? 'System', 
        date('Y-m-d H:i'), 
        $notes ?: 'No additional notes provided.'
    );

    // 6. Handle each decision
    switch ($decision) {
        case 'Hire':
            // Mark probation as Completed
            $updateStmt = $pdo->prepare("
                UPDATE probation_records 
                SET status = 'Completed', 
                    notes = CONCAT(COALESCE(notes, ''), '\n\n', ?),
                    updated_by = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$notesAddition, $_SESSION['user_id'], $probation['id']]);
             
            $pdo->prepare("UPDATE employees SET status = 'Active', updated_by = ? WHERE id = ?")->execute([$_SESSION['user_id'], $employee_id]);

            break;

           case 'Extend':
            // Validate new end date
            $newEndDate = trim($_POST['new_end_date'] ?? '');
            if (!$newEndDate) {
                $pdo->rollBack();
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'New end date is required for extension.']);
                exit;
            }
            $newEndDateObj = DateTime::createFromFormat('Y-m-d', $newEndDate);
            if (!$newEndDateObj || $newEndDateObj->format('Y-m-d') !== $newEndDate) {
                $pdo->rollBack();
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
                exit;
            }
            if ($newEndDate < $probation['end_date']) {
                $pdo->rollBack();
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'New end date cannot be earlier than current end date.']);
                exit;
            }

            // Mark current as Extended
            $updateStmt = $pdo->prepare("
                UPDATE probation_records 
                SET status = 'Extended', 
                    notes = CONCAT(COALESCE(notes, ''), '\n\n', ?),
                    updated_by = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$notesAddition, $_SESSION['user_id'], $probation['id']]);

            // Create new probation record with custom end date
            $insertStmt = $pdo->prepare("
                INSERT INTO probation_records (employee_id, start_date, end_date, status, notes, updated_by, created_at)
                VALUES (?, CURDATE(), ?, 'Active', ?, ?, NOW())
            ");
            $insertStmt->execute([
                $employee_id,
                $newEndDate,
                "Extended from previous probation. " . $notesAddition,
                $_SESSION['user_id']
            ]);
            $pdo->prepare("UPDATE employees SET status = 'Active', updated_by = ? WHERE id = ?")->execute([$_SESSION['user_id'], $employee_id]);
            break;
        case 'Reject':
                // Mark probation as Failed
                $updateStmt = $pdo->prepare("
                    UPDATE probation_records 
                    SET status = 'Failed', 
                        notes = CONCAT(COALESCE(notes, ''), '\n\n', ?),
                        updated_by = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $updateStmt->execute([$notesAddition, $_SESSION['user_id'], $probation['id']]);
                
                // Update employee status to Terminated
                $pdo->prepare("UPDATE employees SET status = 'Terminated', updated_by = ? WHERE id = ?")
                    ->execute([$_SESSION['user_id'], $employee_id]);
                
                // Get the employee ID associated with the logged-in user (if any)
                $empStmt = $pdo->prepare("SELECT employee_id FROM users WHERE id = ?");
                $empStmt->execute([$_SESSION['user_id']]);
                $processedByEmpId = $empStmt->fetchColumn() ?: null;
                
                // Create separation record
                $sepStmt = $pdo->prepare("
                    INSERT INTO separations (employee_id, separation_type, notice_date, last_working_day, status, notes, processed_by, created_at)
                    VALUES (?, 'Involuntary', CURDATE(), CURDATE(), 'In Progress', ?, ?, NOW())
                ");
                $sepStmt->execute([
                    $employee_id,
                    "Terminated due to failed probation. " . $notesAddition,
                    $processedByEmpId
                ]);
                break;
    
                }

    // Audit Log
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at)
        VALUES (?, ?, 'UPDATE', 'Probation', ?, ?, NOW())
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        "Probation evaluation for Employee ID: $employee_id - Decision: $decision",
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Probation evaluation submitted successfully. Decision: $decision"
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