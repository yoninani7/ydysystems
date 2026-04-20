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

// ─────────────────────────────────────────────────────────────────────────────
// 3. Initialize Data & Errors
// ─────────────────────────────────────────────────────────────────────────────
$errors = [];
$data = [
    'first_name'                 => null,
    'middle_name'                => null,
    'last_name'                  => null,
    'date_of_birth'              => null,
    'gender'                     => null,
    'marital_status'             => null,
    'nationality'                => 'Ethiopian',
    'place_of_birth'             => null,
    'personal_phone'             => null,
    'personal_email'             => null,
    'permanent_address'          => null,
    'city'                       => null,
    'postal_code'                => null,
    'department_id'              => null,
    'branch_id'                  => null,
    'job_position_id'            => null,
    'employment_type_id'         => null,
    'hire_date'                  => null,
    'contract_end_date'          => null,
    'probation_period'           => null,
    'reports_to_id'              => null,
    'hours_per_week'             => null,
    'project_name'               => null,
    'gross_salary'               => null,
    'bank_name'                  => null,
    'bank_account'               => null,
    'tin'                        => null,
    'emergency_contact_name'     => null,
    'emergency_contact_phone'    => null,
    'emergency_contact_relation' => null,
];

try {
    $pdo = get_pdo();

    // ─────────────────────────────────────────────────────────────────────────────
    // 4. Required fields validation
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

    // Get employment type name for conditional rules
    $empTypeId   = !empty($_POST['employment_type_id']) ? (int)$_POST['employment_type_id'] : null;
    $empTypeName = '';

    if ($empTypeId) {
        $stmt = $pdo->prepare("SELECT name FROM employment_types WHERE id = ?");
        $stmt->execute([$empTypeId]);
        $empType = $stmt->fetch();
        if ($empType) {
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

    // Conditional required fields
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
    // 5. Populate $data with all fields
    // ─────────────────────────────────────────────────────────────────────────────

    // Personal
    $data['first_name']      = trim($_POST['first_name']  ?? '');
    $data['middle_name']     = trim($_POST['middle_name'] ?? '');
    $data['last_name']       = trim($_POST['last_name']   ?? '');
    $data['personal_phone']  = trim($_POST['personal_phone'] ?? '') ?: null;
    $data['personal_email']  = trim($_POST['personal_email'] ?? '') ?: null;

    $dob = trim($_POST['date_of_birth'] ?? '');
    if ($dob) {
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

    $gender = trim($_POST['gender'] ?? '');
    if ($gender !== '' && !in_array($gender, ['Male', 'Female'])) {
        $errors['gender'] = 'Invalid gender selected.';
    } else {
        $data['gender'] = $gender ?: null;
    }

    $marital = trim($_POST['marital_status'] ?? '');
    $data['marital_status'] = $marital !== '' ? $marital : null;

    $data['nationality']    = trim($_POST['nationality'] ?? 'Ethiopian') ?: 'Ethiopian';
    $data['place_of_birth'] = trim($_POST['place_of_birth'] ?? '') ?: null;

    // Contact
    $data['permanent_address'] = trim($_POST['address'] ?? '') ?: null;
    $data['city']              = trim($_POST['city'] ?? '') ?: null;
    $data['postal_code']       = trim($_POST['postal_code'] ?? '') ?: null;

    // Employment IDs
    $data['department_id']      = (int)($_POST['department_id'] ?? 0);
    $data['branch_id']          = (int)($_POST['branch_id'] ?? 0) ?: null;
    $data['job_position_id']    = (int)($_POST['job_position_id'] ?? 0);
    $data['employment_type_id'] = $empTypeId;

    // Verify FKs
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
            $data['branch_id'] = null;
        }
    }
    if ($data['job_position_id']) {
        $stmt = $pdo->prepare("SELECT id FROM job_positions WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$data['job_position_id']]);
        if (!$stmt->fetch()) $errors['job_position_id'] = 'Selected job position does not exist.';
    }

    // Dynamic fields
    $hireDate = trim($_POST['hire_date'] ?? '');
    if ($hireDate !== '') {
        $dObj = DateTime::createFromFormat('Y-m-d', $hireDate);
        if (!$dObj || $dObj->format('Y-m-d') !== $hireDate) {
            $errors['hire_date'] = 'Invalid hire date format.';
        } else {
            $data['hire_date'] = $hireDate;
        }
    }

    $endDate = trim($_POST['contract_end_date'] ?? '');
    if ($endDate !== '') {
        $dObj = DateTime::createFromFormat('Y-m-d', $endDate);
        if (!$dObj || $dObj->format('Y-m-d') !== $endDate) {
            $errors['contract_end_date'] = 'Invalid contract end date format.';
        } else {
            $data['contract_end_date'] = $endDate;
        }
    }

    $data['probation_period'] = (isset($_POST['probation_days']) && $_POST['probation_days'] !== '') ? $_POST['probation_days'] : (trim($_POST['probation_period'] ?? '') ?: null);

    $reportsToRaw = trim($_POST['reports_to_id'] ?? '');
    if ($reportsToRaw !== '') {
        $reportsToId = (int)$reportsToRaw;
        if ($reportsToId > 0) {
            $data['reports_to_id'] = $reportsToId;
        }
    }

    $hours = trim($_POST['hours_per_week'] ?? '');
    if ($hours !== '') {
        $data['hours_per_week'] = (int)$hours;
    }

    $data['project_name'] = trim($_POST['project_name'] ?? '') ?: null;
    $data['institution'] = trim($_POST['institution'] ?? '') ?: null;

    // Finance
    $salary = trim($_POST['salary'] ?? '');
    if ($salary !== '') {
        $salaryVal = (float)$salary;
        if ($salaryVal <= 0) {
            $errors['salary'] = 'Salary must be a positive amount.';
        } else {
            $data['gross_salary'] = $salaryVal;
        }
    }

    $data['bank_name']    = trim($_POST['bank_name'] ?? '') ?: null;
    $data['bank_account'] = trim($_POST['bank_account'] ?? '') ?: null;
    $data['tin']          = trim($_POST['tin'] ?? '') ?: null;

    // Emergency contact
    $data['emergency_contact_name']     = trim($_POST['emergency_name'] ?? '') ?: null;
    $data['emergency_contact_relation'] = trim($_POST['emergency_relation'] ?? '') ?: null;

    $emergPhone = trim($_POST['emergency_phone'] ?? '');
    if ($emergPhone && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $emergPhone)) {
        $errors['emergency_phone'] = 'Invalid emergency phone number.';
    } else {
        $data['emergency_contact_phone'] = $emergPhone ?: null;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 6. Length validation (after all $data is populated)
    // ─────────────────────────────────────────────────────────────────────────────
    $lengthRules = [
        'first_name'              => ['max' => 100, 'label' => 'First Name'],
        'middle_name'             => ['max' => 100, 'label' => 'Father Name'],
        'last_name'               => ['max' => 100, 'label' => 'Last Name'],
        'gender'                  => ['max' => 10,  'label' => 'Gender'],
        'marital_status'          => ['max' => 20,  'label' => 'Marital Status'],
        'nationality'             => ['max' => 100, 'label' => 'Nationality'],
        'place_of_birth'          => ['max' => 150, 'label' => 'Place of Birth'],
        'personal_phone'          => ['max' => 50,  'label' => 'Personal Phone'],
        'personal_email'          => ['max' => 150, 'label' => 'Personal Email'],
        'permanent_address'       => ['max' => 65535,'label' => 'Permanent Address'],
        'city'                    => ['max' => 100, 'label' => 'City'],
        'postal_code'             => ['max' => 20,  'label' => 'Postal Code'],
        'probation_period'        => ['max' => 50,  'label' => 'Probation Period'],
        'project_name'            => ['max' => 200, 'label' => 'Project Name'],
        'bank_name'               => ['max' => 100, 'label' => 'Bank Name'],
        'bank_account'            => ['max' => 100, 'label' => 'Bank Account'],
        'tin'                     => ['max' => 50,  'label' => 'TIN'],
        'emergency_contact_name'  => ['max' => 150, 'label' => 'Emergency Contact Name'],
        'emergency_contact_phone' => ['max' => 50,  'label' => 'Emergency Phone'],
        'emergency_contact_relation' => ['max' => 100, 'label' => 'Emergency Relationship'],
    ];

    foreach ($lengthRules as $field => $rule) {
        if (isset($data[$field]) && is_string($data[$field])) {
            if (mb_strlen($data[$field]) > $rule['max']) {
                $errors[$field] = $rule['label'] . ' must not exceed ' . $rule['max'] . ' characters.';
            }
        }
    }

    // Return validation errors before touching files/DB
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 7. Insert into database
    // ─────────────────────────────────────────────────────────────────────────────
    $profilePhoto = null;
    $pdo->beginTransaction();

    $sql = "
        INSERT INTO employees (
            first_name,       middle_name,      last_name,
            date_of_birth,    gender,           marital_status,
            nationality,      place_of_birth,
            personal_phone,   personal_email,
            permanent_address, city,            postal_code,
            department_id,    branch_id,        job_position_id,   employment_type_id,
            hire_date,        contract_end_date, probation_period, reports_to_id,
            hours_per_week,   project_name,
            gross_salary,     bank_name,        bank_account,      tin,
            emergency_contact_name, emergency_contact_phone, emergency_contact_relation,
            profile_photo,
            status,           created_at,       updated_at
        ) VALUES (
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
            NULL,
            'Active',         NOW(),             NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
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
    ]);

    $newEmployeeRowId = $pdo->lastInsertId();

    // ─────────────────────────────────────────────────────────────────────────────
    // 8. Generate Official Employee ID
    // ─────────────────────────────────────────────────────────────────────────────
    $year = date('Y');
    $paddedId = str_pad((string)$newEmployeeRowId, 4, '0', STR_PAD_LEFT);
    $employeeId = "EMP-{$year}-{$paddedId}";

    // ─────────────────────────────────────────────────────────────────────────────
    // 9. Handle Avatar Upload (Now we have the official ID)
    // ─────────────────────────────────────────────────────────────────────────────
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['avatar'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if ($file['size'] > $maxSize) {
            throw new Exception("Avatar file size must be under 5 MB.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            throw new Exception("Avatar must be JPEG, PNG, WebP, or GIF.");
        }

        // ─────────────────────────────────────────────────────────────────────
        // 8a. Process Avatar (Resizing requires GD library)
        // ─────────────────────────────────────────────────────────────────────
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Use formal employeeId for the filename
        $filename = $employeeId . '_' . time() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (extension_loaded('gd')) {
            // Create image resource from uploaded file
            switch ($mime) {
                case 'image/jpeg': $sourceImage = imagecreatefromjpeg($file['tmp_name']); break;
                case 'image/png':  $sourceImage = imagecreatefrompng($file['tmp_name']);  break;
                case 'image/webp': $sourceImage = imagecreatefromwebp($file['tmp_name']); break;
                case 'image/gif':  $sourceImage = imagecreatefromgif($file['tmp_name']);  break;
                default: $sourceImage = false;
            }

            if (!$sourceImage) {
                throw new Exception("Unable to process image resource.");
            }

            $originalWidth  = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);
            $maxWidth = 800; $maxHeight = 800;
            $newWidth = $originalWidth; $newHeight = $originalHeight;

            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $ratio = $originalWidth / $originalHeight;
                if ($ratio > 1) {
                    $newWidth  = $maxWidth;
                    $newHeight = (int)($maxWidth / $ratio);
                } else {
                    $newHeight = $maxHeight;
                    $newWidth  = (int)($maxHeight * $ratio);
                }
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            $saved = false;
            switch ($mime) {
                case 'image/jpeg': $saved = imagejpeg($resizedImage, $uploadDir . $filename, 85); break;
                case 'image/png':  $saved = imagepng($resizedImage, $uploadDir . $filename, 6);   break;
                case 'image/webp': $saved = imagewebp($resizedImage, $uploadDir . $filename, 85); break;
                case 'image/gif':  $saved = imagegif($resizedImage, $uploadDir . $filename);      break;
            }

            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            if ($saved) {
                $profilePhoto = 'uploads/avatars/' . $filename;
            } else {
                throw new Exception("Failed to save resized avatar.");
            }
        } else {
            // Fallback: GD is not enabled, just move the file as-is
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $profilePhoto = 'uploads/avatars/' . $filename;
            } else {
                throw new Exception("GD library missing and fallback upload failed.");
            }
        }
    }

    $updateStmt = $pdo->prepare("UPDATE employees SET employee_id = ?, profile_photo = ? WHERE id = ?");
    $updateStmt->execute([$employeeId, $profilePhoto, $newEmployeeRowId]);

    // ─────────────────────────────────────────────────────────────────────────────
    // 10. Record Contract & Internship (If applicable)
    // ─────────────────────────────────────────────────────────────────────────────
    if ($empTypeName === 'contract') {
        $contractStmt = $pdo->prepare("
            INSERT INTO employee_contracts (employee_id, employment_type_id, start_date, end_date, status, notes)
            VALUES (?, ?, ?, ?, 'Active', ?)
        ");
        $contractStmt->execute([
            $newEmployeeRowId,
            $data['employment_type_id'],
            $data['hire_date'],
            $data['contract_end_date'],
            "Initial " . ucfirst($empTypeName) . " contract created during onboarding."
        ]);
    }

    if ($empTypeName === 'internship') {
        // Build full name for the intern table
        $fullName = trim($data['first_name'] . ' ' . ($data['middle_name'] ?? '') . ' ' . $data['last_name']);
        
        $internStmt = $pdo->prepare("
            INSERT INTO internships (intern_code, full_name, institution, department_id, mentor_id, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')
        ");
        $internStmt->execute([
            $employeeId, 
            $fullName,
            $data['institution'] ?? null,
            $data['department_id'],
            $data['reports_to_id'],
            $data['hire_date'],
            $data['contract_end_date']
        ]);
    }

        // ─────────────────────────────────────────────────────────────────────────────
    // 10b. Record Probation (If applicable)
    // ─────────────────────────────────────────────────────────────────────────────
    $probationPeriodString = trim($_POST['probation_period'] ?? '');
    // Check both probation_days (numeric) and probation_period (string)
    if (isset($_POST['probation_days']) && $_POST['probation_days'] !== '') {
        $probationDays = (int)$_POST['probation_days'];
    } elseif ($probationPeriodString !== '' && !str_contains(strtolower($probationPeriodString), 'no probation')) {
        if (preg_match('/(\d+)\s*Days?/i', $probationPeriodString, $matches)) {
            $probationDays = (int)$matches[1];
        }
    }
    
    if ($probationDays !== null && !empty($data['hire_date'])) {
        $startDate = $data['hire_date'];
        $endDate = date('Y-m-d', strtotime($startDate . " + $probationDays days"));
        
        $probStmt = $pdo->prepare("
            INSERT INTO probation_records (employee_id, start_date, end_date, status)
            VALUES (?, ?, ?, 'Active')
        ");
        $probStmt->execute([$newEmployeeRowId, $startDate, $endDate]);
    }

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

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Clean up uploaded avatar if it exists
    if (isset($profilePhoto) && $profilePhoto && file_exists(__DIR__ . '/../../' . $profilePhoto)) {
        unlink(__DIR__ . '/../../' . $profilePhoto);
    }
    
    http_response_code(500); 
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again or contact your administrator.'
    ]);
    exit;
}