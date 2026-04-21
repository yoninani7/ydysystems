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
// 4. Input Processing & Validation
// ─────────────────────────────────────────────────────────────────────────────
$errors = [];
$data = [
    'legal_name'          => trim($_POST['legal_name'] ?? ''),
    'trading_name'        => trim($_POST['trading_name'] ?? '') ?: null,
    'ceo_name'            => trim($_POST['ceo_name'] ?? '') ?: null,
    'head_office'         => trim($_POST['head_office'] ?? '') ?: null,
    'entity_type'         => trim($_POST['entity_type'] ?? '') ?: null,
    'establishment_date'  => trim($_POST['establishment_date'] ?? '') ?: null,
    'registration_no'     => trim($_POST['registration_no'] ?? '') ?: null,
    'tin'                 => trim($_POST['tin'] ?? '') ?: null,
    'vat_reg_number'      => trim($_POST['vat_reg_number'] ?? '') ?: null,
    'trade_license_no'    => trim($_POST['trade_license_no'] ?? '') ?: null,
    'work_week_desc'      => trim($_POST['work_week_desc'] ?? '') ?: null,
    'probation_days'      => trim($_POST['probation_days'] ?? '') ?: null,
    'retirement_age'      => trim($_POST['retirement_age'] ?? '') ?: null,
    'main_bank'           => trim($_POST['main_bank'] ?? '') ?: null,
    'bank_account_primary'=> trim($_POST['bank_account_primary'] ?? '') ?: null,
    'base_currency'       => trim($_POST['base_currency'] ?? '') ?: 'ETB',
    'fiscal_start'        => trim($_POST['fiscal_start'] ?? '') ?: null,
    'website'             => trim($_POST['website'] ?? '') ?: null,
    'corporate_email'     => trim($_POST['corporate_email'] ?? '') ?: null,
    'corporate_phone'     => trim($_POST['corporate_phone'] ?? '') ?: null,
    'telegram'            => trim($_POST['telegram'] ?? '') ?: null,
    'whatsapp'            => trim($_POST['whatsapp'] ?? '') ?: null,
    'linkedin'            => trim($_POST['linkedin'] ?? '') ?: null,
];

// Required fields
if (empty($data['legal_name'])) {
    $errors['legal_name'] = 'Legal name is required.';
}

// Validate establishment date if provided
if (!empty($data['establishment_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $data['establishment_date']);
    if (!$d || $d->format('Y-m-d') !== $data['establishment_date']) {
        $errors['establishment_date'] = 'Invalid date format.';
    } else {
        $data['establishment_date'] = $d->format('Y-m-d');
    }
} else {
    $data['establishment_date'] = null;
}

// Validate numeric fields
if ($data['probation_days'] !== null && (!is_numeric($data['probation_days']) || (int)$data['probation_days'] < 0)) {
    $errors['probation_days'] = 'Probation days must be a positive number.';
} else {
    $data['probation_days'] = $data['probation_days'] !== null ? (int)$data['probation_days'] : null;
}

if ($data['retirement_age'] !== null && (!is_numeric($data['retirement_age']) || (int)$data['retirement_age'] < 0)) {
    $errors['retirement_age'] = 'Retirement age must be a positive number.';
} else {
    $data['retirement_age'] = $data['retirement_age'] !== null ? (int)$data['retirement_age'] : null;
}

// Validate email if provided
if (!empty($data['corporate_email']) && !filter_var($data['corporate_email'], FILTER_VALIDATE_EMAIL)) {
    $errors['corporate_email'] = 'Invalid email format.';
}

// Website: prepend https:// if missing (optional but helpful)
if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
    $data['website'] = 'https://' . $data['website'];
}

// Return validation errors
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. Database Update
// ─────────────────────────────────────────────────────────────────────────────
try {
    $pdo = get_pdo();
    $pdo->beginTransaction();

    // Check if a record exists
        $stmt = $pdo->query("SELECT id FROM company_profile LIMIT 1");
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing record
            $sql = "UPDATE company_profile SET
                legal_name = :legal_name,
                trading_name = :trading_name,
                ceo_name = :ceo_name,
                head_office = :head_office,
                entity_type = :entity_type,
                establishment_date = :establishment_date,
                registration_no = :registration_no,
                tin = :tin,
                vat_reg_number = :vat_reg_number,
                trade_license_no = :trade_license_no,
                work_week_desc = :work_week_desc,
                probation_days = :probation_days,
                retirement_age = :retirement_age,
                main_bank = :main_bank,
                bank_account_primary = :bank_account_primary,
                base_currency = :base_currency,
                fiscal_start = :fiscal_start,
                website = :website,
                corporate_email = :corporate_email,
                corporate_phone = :corporate_phone,
                telegram = :telegram,
                whatsapp = :whatsapp,
                linkedin = :linkedin,
                updated_at = NOW()
                WHERE id = :id";
            $params = array_merge($data, [':id' => $existing['id']]);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // Insert initial record with created_by
            $sql = "INSERT INTO company_profile (
                legal_name, trading_name, ceo_name, head_office, entity_type,
                establishment_date, registration_no, tin, vat_reg_number, trade_license_no,
                work_week_desc, probation_days, retirement_age, main_bank, bank_account_primary,
                base_currency, fiscal_start, website, corporate_email, corporate_phone,
                telegram, whatsapp, linkedin, created_by, updated_at
            ) VALUES (
                :legal_name, :trading_name, :ceo_name, :head_office, :entity_type,
                :establishment_date, :registration_no, :tin, :vat_reg_number, :trade_license_no,
                :work_week_desc, :probation_days, :retirement_age, :main_bank, :bank_account_primary,
                :base_currency, :fiscal_start, :website, :corporate_email, :corporate_phone,
                :telegram, :whatsapp, :linkedin, :created_by, NOW()
            )";
            $data['created_by'] = $_SESSION['user_id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
        }

    // Audit log
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, action, module, record_ref, ip_address, logged_at)
        VALUES (?, ?, 'UPDATE', 'Company Profile', ?, ?, NOW())
    ");
    $auditStmt->execute([
        $_SESSION['user_id'],
        $_SESSION['username'],
        'company_profile',
        $_SERVER['REMOTE_ADDR'],
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Company profile updated successfully.'
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