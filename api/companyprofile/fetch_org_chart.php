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

    // 1. Fetch Departments
    $deptSql = "SELECT id, name FROM departments WHERE status = 'Active' AND deleted_at IS NULL ORDER BY name";
    $departments = $pdo->query($deptSql)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch all Job Positions (Global list)
    $jobSql = "
        SELECT jp.id, jp.title, jp.department_id, jp.parent_id,
               COUNT(e.id) AS headcount
        FROM job_positions jp
        LEFT JOIN employees e ON e.job_position_id = jp.id AND e.status = 'Active'
        WHERE jp.status = 'Active' AND jp.deleted_at IS NULL
        GROUP BY jp.id
    ";
    $allJobs = $pdo->query($jobSql)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Build ONE global map for the entire company[cite: 7]
    $allJobsMap = [];
    foreach ($allJobs as $job) {
        $job['headcount'] = (int)$job['headcount'];
        $job['children'] = [];
        $allJobsMap[$job['id']] = $job;
    }

    // 4. Link children to parents globally (Cross-department support)[cite: 7]
    $rootNodes = [];
    foreach ($allJobsMap as $id => &$job) {
        $parentId = $job['parent_id'];
        // Logic: If it has a parent and that parent exists in our map, attach it
        if (!empty($parentId) && $parentId != $id && isset($allJobsMap[$parentId])) {
            $allJobsMap[$parentId]['children'][] = &$job;
        } else {
            // Otherwise, it's a top-level node for its branch
            $rootNodes[] = &$job;
        }
    }
    unset($job); // Clean up reference

    // 5. Total company headcount
    $total = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn();

    // 6. Final Assembly: Filter the global roots into their respective departments
    $finalDepartments = [];
    foreach ($departments as $dept) {
        $deptId = $dept['id'];
        
        // Find which root nodes belong to this department
        $deptRoots = array_filter($rootNodes, function($node) use ($deptId) {
            return (int)$node['department_id'] === (int)$deptId;
        });

        // Calculate total department headcount (including nested children)
        $deptTotal = 0;
        foreach ($allJobs as $j) {
            if ((int)$j['department_id'] === (int)$deptId) {
                $deptTotal += (int)$j['headcount'];
            }
        }

        $finalDepartments[] = [
            'id' => $deptId,
            'name' => $dept['name'],
            'headcount' => $deptTotal,
            'jobs' => array_values($deptRoots) // Reset array keys for JSON
        ];
    }

    echo json_encode([
        'success' => true, 
        'data' => [
            'total' => (int)$total,
            'departments' => $finalDepartments
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}