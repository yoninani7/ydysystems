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

try {
    $pdo = get_pdo();

    // Total active employees
    $total = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn();

    // Departments with headcount and position count
    $deptSql = "
        SELECT 
            d.id,
            d.name,
            COUNT(DISTINCT e.id) AS headcount,
            COUNT(DISTINCT jp.id) AS position_count
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
        LEFT JOIN job_positions jp ON d.id = jp.department_id AND jp.status = 'Active'
        WHERE d.status = 'Active' AND d.deleted_at IS NULL
        GROUP BY d.id
        ORDER BY d.name ASC
    ";
    $departments = $pdo->query($deptSql)->fetchAll();

    // Job positions with headcounts
    $jobSql = "
        SELECT 
            jp.department_id,
            jp.title,
            COUNT(e.id) AS employee_count
        FROM job_positions jp
        LEFT JOIN employees e ON jp.id = e.job_position_id AND e.status = 'Active'
        WHERE jp.status = 'Active' AND jp.deleted_at IS NULL
        GROUP BY jp.department_id, jp.id
    ";
    $jobs = $pdo->query($jobSql)->fetchAll();

    // Group jobs by department
    $jobsByDept = [];
    foreach ($jobs as $job) {
        $jobsByDept[$job['department_id']][] = [
            'title' => $job['title'],
            'count' => (int)$job['employee_count']
        ];
    }

    // Build response
    $orgData = [
        'total' => (int)$total,
        'departments' => []
    ];
    foreach ($departments as $dept) {
        $orgData['departments'][] = [
            'name' => $dept['name'],
            'headcount' => (int)$dept['headcount'],
            'position_count' => (int)$dept['position_count'],
            'jobs' => $jobsByDept[$dept['id']] ?? []
        ];
    }

    echo json_encode(['success' => true, 'data' => $orgData]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}