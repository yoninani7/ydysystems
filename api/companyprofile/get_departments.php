<?php
require_once '../../config.php';

header('Content-Type: application/json');

try {
    $pdo = get_pdo();
    // Use full view name
    $sql = "SELECT department_name, head_of_dept, active_headcount FROM v_dept_structure_stats";
    $stmt = $pdo->query($sql);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);
} catch (Exception $e) {
    // If the DB fails, return JSON, not HTML!
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;