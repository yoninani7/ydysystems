<?php
declare(strict_types=1);
require_once '../../config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

echo json_encode(['success' => true, 'token' => csrf_token()]);