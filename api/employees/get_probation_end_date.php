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

$employee_id = (int)($_GET['employee_id'] ?? 0);
if (!$employee_id) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT end_date FROM probation_records WHERE employee_id = ? ORDER BY end_date DESC LIMIT 1");
    $stmt->execute([$employee_id]);
    $data = $stmt->fetch();
    if ($data) {
        echo json_encode(['success' => true, 'data' => ['end_date' => $data['end_date']]]);
    } else {
        echo json_encode(['success' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false]);
}