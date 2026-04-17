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

// 3. CSRF
if (!csrf_verify()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
    exit;
}

$pdo = get_pdo();
$errors = [];

// ─────────────────────────────────────────────────────────────────────────────
// 4. Required fields validation
// NOTE: branch_id is intentionally excluded — it is optional in the form
// ─────────────────────────────────────────────────────────────────────────────
$required = [
    'first_name'         => 'First Name',
    'middle_name'        => 'Father Name',
    'last_name'          => 'Last Name',
    'date_of_birth'      => 'Date of Birth',
    'gender'             => 'Gender',
    'department_id'      => 'Department',
    'job_position_id'    => 'Job Position',
    'employment_type_id' => 'Employment Type',
];

foreach ($required as $field => $label) {
    if (empty($_POST[$field])) {
        $errors[$field] = "$label is required.";
    }
}

// Get employment type name to apply conditional rules
$empTypeId   = !empty($_POST['employment_type_id']) ? (int)$_POST['employment_type_id'] : null;
$empTypeName = '';

if ($empTypeId) {
    $stmt = $pdo->prepare("SELECT name FROM employment_types WHERE id = ?");
    $stmt->execute([$empTypeId]);
    $empType = $stmt->fetch();
    if ($empType) {
        // Normalise: "Permanent / Full-Time" → "full-time", "Fixed-Term Contract" → "contract", etc.
        $raw = strtolower($empType['name']);
        if (str_contains($raw, 'full') || str_contains($raw, 'permanent')) {
            $empTypeName = 'full-time';
        } elseif (str_contains($raw, 'contract')) {
            $empTypeName = 'contract';
        } elseif (str_contains($raw, 'part')) {
            $empTypeName = 'part-time';
        } elseif (str_contains($raw, 'intern')) {
            $empTypeName = 'internship';
        } elseif (str_contains($raw, 'temp') || str_contains($raw, 'casual')) {
            $empTypeName = 'temporary';
        }
    }
}

// Conditional required fields based on employment type
if (in_array($empTypeName, ['full-time', 'part-time', 'contract', 'internship'])) {
    if (empty($_POST['hire_date'])) {
        $errors['hire_date'] = 'Hire date is required for this employment type.';
    }
}
if (in_array($empTypeName, ['contract', 'internship'])) {
    if (empty($_POST['contract_end_date'])) {
        $errors['contract_end_date'] = 'End date is required for contract / internship.';
    }
} 
if ($empTypeName === 'temporary' && empty($_POST['project_name'])) {
    $errors['project_name'] = 'Project name is required for temporary assignment.';
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. Format and business-rule validation
// ─────────────────────────────────────────────────────────────────────────────
$data = [];

// Personal
$data['first_name']  = trim($_POST['first_name']  ?? '');
$data['middle_name'] = trim($_POST['middle_name'] ?? '');
$data['last_name']   = trim($_POST['last_name']   ?? '');

// Date of Birth
$dob = trim($_POST['date_of_birth'] ?? '');
if ($dob && !isset($errors['date_of_birth'])) {
    $dobObj = DateTime::createFromFormat('Y-m-d', $dob);
    if (!$dobObj || $dobObj->format('Y-m-d') !== $dob) {
        $errors['date_of_birth'] = 'Invalid date format.';
    } else {
        $age = (new DateTime())->diff($dobObj)->y;
        if ($age < 16 || $age > 100) {
            $errors['date_of_birth'] = 'Age must be between 16 and 100.';
        } else {
            $data['date_of_birth'] = $dob;
        }
    }
}

// Gender
$gender = trim($_POST['gender'] ?? '');
if ($gender && !in_array($gender, ['Male', 'Female'])) {
    $errors['gender'] = 'Gender must be Male or Female.';
} else {
    $data['gender'] = $gender;
}

// Marital Status (optional)
$marital         = trim($_POST['marital_status'] ?? '');
$allowedMarital  = ['Single', 'Married', 'Divorced', 'Widowed'];
$data['marital_status'] = ($marital && in_array($marital, $allowedMarital)) ? $marital : null;

$data['nationality']    = trim($_POST['nationality']    ?? 'Ethiopian') ?: 'Ethiopian';
$data['place_of_birth'] = trim($_POST['place_of_birth'] ?? '') ?: null;
 

// DB column: permanent_address (JS sends key "address")
$data['permanent_address'] = trim($_POST['address'] ?? '') ?: null;
$data['city']              = trim($_POST['city']    ?? '') ?: null;
$data['postal_code']       = trim($_POST['postal_code'] ?? '') ?: null;

// Employment IDs
$data['department_id']      = (int)($_POST['department_id']      ?? 0);
$data['branch_id']          = (int)($_POST['branch_id']          ?? 0) ?: null; // nullable
$data['job_position_id']    = (int)($_POST['job_position_id']    ?? 0);
$data['employment_type_id'] = $empTypeId;

// Verify FKs exist
if ($data['department_id']) {
    $stmt = $pdo->prepare("SELECT id FROM departments WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$data['department_id']]);
    if (!$stmt->fetch()) $errors['department_id'] = 'Selected department does not exist.';
}
if ($data['branch_id']) {
    $stmt = $pdo->prepare("SELECT id FROM branches WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$data['branch_id']]);
    if (!$stmt->fetch()) {
        $errors['branch_id'] = 'Selected branch does not exist.';
        $data['branch_id']   = null;
    }
}
if ($data['job_position_id']) {
    $stmt = $pdo->prepare("SELECT id FROM job_positions WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$data['job_position_id']]);
    if (!$stmt->fetch()) $errors['job_position_id'] = 'Selected job position does not exist.';
}

// Hire date
$hireDate = trim($_POST['hire_date'] ?? '');
if ($hireDate && !isset($errors['hire_date'])) {
    $hireObj = DateTime::createFromFormat('Y-m-d', $hireDate);
    if (!$hireObj) {
        $errors['hire_date'] = 'Invalid hire date format.';
    } else {
        $today       = new DateTime();
        $oneYearAgo  = (clone $today)->sub(new DateInterval('P1Y'));
        $oneMonthAhead = (clone $today)->add(new DateInterval('P1M'));
        if ($hireObj < $oneYearAgo) {
            $errors['hire_date'] = 'Hire date cannot be more than 1 year in the past.';
        } elseif ($hireObj > $oneMonthAhead) {
            $errors['hire_date'] = 'Hire date cannot be more than 1 month in the future.';
        } else {
            $data['hire_date'] = $hireDate;
        }
    }
} else {
    $data['hire_date'] = null;
}

// Contract end date
$endDate = trim($_POST['contract_end_date'] ?? '');
if ($endDate && !isset($errors['contract_end_date'])) {
    $endObj = DateTime::createFromFormat('Y-m-d', $endDate);
    if (!$endObj) {
        $errors['contract_end_date'] = 'Invalid end date format.';
    } elseif (!empty($data['hire_date']) && $endObj <= new DateTime($data['hire_date'])) {
        $errors['contract_end_date'] = 'End date must be after start date.';
    } else {
        $data['contract_end_date'] = $endDate;
    }
} else {
    $data['contract_end_date'] = null;
}

// DB column: probation_period (VARCHAR 50) — store the human-readable string
// JS sends the full dropdown text e.g. "60 Days (Standard)"
$data['probation_period'] = trim($_POST['probation_period'] ?? '') ?: null;

// Reporting manager — DB column: reports_to_id
$reportsToRaw = trim($_POST['reports_to_id'] ?? '');
if ($reportsToRaw !== '') {
    $data['reports_to_id'] = (int)$reportsToRaw;
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$data['reports_to_id']]);
    if (!$stmt->fetch()) {
        $errors['reports_to_id'] = 'Selected manager does not exist.';
        $data['reports_to_id']   = null;
    }
} else {
    $data['reports_to_id'] = null;
}

// Hours per week (DB: INT)
$hours = trim($_POST['hours_per_week'] ?? '');
if ($hours !== '') {
    $hoursInt = (int)$hours;
    if ($hoursInt < 1 || $hoursInt > 40) {
        $errors['hours_per_week'] = 'Hours must be between 1 and 40.';
    } else {
        $data['hours_per_week'] = $hoursInt;
    }
} else {
    $data['hours_per_week'] = null;
}

$data['project_name'] = trim($_POST['project_name'] ?? '') ?: null;

// Finance — DB column: gross_salary
$salary = trim($_POST['salary'] ?? '');
if ($salary !== '') {
    $salaryVal = (float)$salary;
    if ($salaryVal <= 0) {
        $errors['salary'] = 'Salary must be a positive amount.';
    } else {
        $data['gross_salary'] = $salaryVal;
    }
} else {
    $data['gross_salary'] = null;
}

$data['bank_name'] = trim($_POST['bank_name'] ?? '') ?: null;

 
$tin = trim($_POST['tin'] ?? '');
if ($tin && !preg_match('/^\d{9,12}$/', $tin)) {
    $errors['tin'] = 'TIN should be 9–12 digits.';
} else {
    $data['tin'] = $tin ?: null;
}

// Emergency contact
$data['emergency_contact_name']     = trim($_POST['emergency_name']     ?? '') ?: null;
$data['emergency_contact_relation'] = trim($_POST['emergency_relation'] ?? '') ?: null;

$emergPhone = trim($_POST['emergency_phone'] ?? '');
if ($emergPhone && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $emergPhone)) {
    $errors['emergency_phone'] = 'Invalid emergency phone number.';
} else {
    $data['emergency_contact_phone'] = $emergPhone ?: null;
}

// Return validation errors before touching files/DB
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 6. Handle Avatar Upload (optional) — DB column: profile_photo
// ─────────────────────────────────────────────────────────────────────────────
$profilePhoto = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['avatar'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    if ($file['size'] > $maxSize) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Avatar file size must be under 5 MB.', 'errors' => ['avatar' => 'File too large.']]);
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Avatar must be JPEG, PNG, WebP, or GIF.', 'errors' => ['avatar' => 'Invalid image type.']]);
        exit;
    }

    $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename  = 'emp_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . strtolower($ext);
    $uploadDir = __DIR__ . '/../../uploads/avatars/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        $profilePhoto = 'uploads/avatars/' . $filename;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save avatar image.']);
        exit;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 7. Generate Employee ID  e.g. EMP-2025-0001
// ─────────────────────────────────────────────────────────────────────────────
$year  = date('Y');
$stmt  = $pdo->prepare("SELECT MAX(employee_id) FROM employees WHERE employee_id LIKE ?");
$stmt->execute(["EMP-$year-%"]);
$maxId = $stmt->fetchColumn();
$num   = $maxId ? ((int)substr($maxId, -4) + 1) : 1;
$employeeId = sprintf("EMP-%s-%04d", $year, $num);

// ─────────────────────────────────────────────────────────────────────────────
// 8. Insert — column names match the schema exactly
// ─────────────────────────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    $sql = "
        INSERT INTO employees (
            employee_id,
            first_name,       middle_name,      last_name,
            date_of_birth,    gender,            marital_status,
            nationality,      place_of_birth,
            personal_phone,   personal_email,
            permanent_address, city,             postal_code,
            department_id,    branch_id,         job_position_id,   employment_type_id,
            hire_date,        contract_end_date, probation_period,  reports_to_id,
            hours_per_week,   project_name,
            gross_salary,     bank_name,         bank_account,      tin,
            emergency_contact_name, emergency_contact_phone, emergency_contact_relation,
            profile_photo,
            status,           created_at,        updated_at
        ) VALUES (
            :employee_id,
            :first_name,      :middle_name,      :last_name,
            :date_of_birth,   :gender,           :marital_status,
            :nationality,     :place_of_birth,
            :personal_phone,  :personal_email,
            :permanent_address, :city,           :postal_code,
            :department_id,   :branch_id,        :job_position_id,  :employment_type_id,
            :hire_date,       :contract_end_date, :probation_period, :reports_to_id,
            :hours_per_week,  :project_name,
            :gross_salary,    :bank_name,        :bank_account,     :tin,
            :emergency_contact_name, :emergency_contact_phone, :emergency_contact_relation,
            :profile_photo,
            'Active',         NOW(),             NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':employee_id'                => $employeeId,
        ':first_name'                 => $data['first_name'],
        ':middle_name'                => $data['middle_name'],
        ':last_name'                  => $data['last_name'],
        ':date_of_birth'              => $data['date_of_birth'],
        ':gender'                     => $data['gender'],
        ':marital_status'             => $data['marital_status'],
        ':nationality'                => $data['nationality'],
        ':place_of_birth'             => $data['place_of_birth'],
        ':personal_phone'             => $data['personal_phone'],
        ':personal_email'             => $data['personal_email'],
        ':permanent_address'          => $data['permanent_address'],
        ':city'                       => $data['city'],
        ':postal_code'                => $data['postal_code'],
        ':department_id'              => $data['department_id'],
        ':branch_id'                  => $data['branch_id'],
        ':job_position_id'            => $data['job_position_id'],
        ':employment_type_id'         => $data['employment_type_id'],
        ':hire_date'                  => $data['hire_date'],
        ':contract_end_date'          => $data['contract_end_date'],
        ':probation_period'           => $data['probation_period'],
        ':reports_to_id'              => $data['reports_to_id'],
        ':hours_per_week'             => $data['hours_per_week'],
        ':project_name'               => $data['project_name'],
        ':gross_salary'               => $data['gross_salary'],
        ':bank_name'                  => $data['bank_name'],
        ':bank_account'               => $data['bank_account'],
        ':tin'                        => $data['tin'],
        ':emergency_contact_name'     => $data['emergency_contact_name'],
        ':emergency_contact_phone'    => $data['emergency_contact_phone'],
        ':emergency_contact_relation' => $data['emergency_contact_relation'],
        ':profile_photo'              => $profilePhoto,
    ]);

    $newEmployeeRowId = $pdo->lastInsertId();

    // Audit log
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at)
        VALUES (?, ?, 'CREATE', 'Employees', ?, ?, NOW())
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        $employeeId,
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success'  => true,
        'message'  => "Employee $employeeId created successfully.",
        'employee' => [
            'id'          => $newEmployeeRowId,
            'employee_id' => $employeeId,
            'name'        => $data['first_name'] . ' ' . $data['last_name'],
        ],
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    // Clean up avatar if the DB insert failed
    if ($profilePhoto && file_exists(__DIR__ . '/../../' . $profilePhoto)) {
        unlink(__DIR__ . '/../../' . $profilePhoto);
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
