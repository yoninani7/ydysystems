<?php
declare(strict_types=1);
require_once 'config.php'; 
 
if (empty($_SESSION['user_id'])) {
    redirect('login/login.php'); 
}

$pdo = get_pdo();

// 1. Fetch Hero Stats from the View you created in SQL
$statsQuery = $pdo->query("SELECT * FROM v_dashboard_hero_stats");
$hero = $statsQuery->fetch(PDO::FETCH_ASSOC);

// 2. Fetch Chart Data (Headcount per Department)
$chartQuery = $pdo->query("
    SELECT d.name, COUNT(e.id) as count 
    FROM departments d 
    LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
    GROUP BY d.id 
    ORDER BY count DESC
");
$chartData = $chartQuery->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Recent Activities from Audit Logs
$activityQuery = $pdo->query("
    SELECT action, module, record_ref, user_name, logged_at 
    FROM audit_logs 
    ORDER BY logged_at DESC 
    LIMIT 5
");
$activities = $activityQuery->fetchAll(PDO::FETCH_ASSOC);

$username = clean($_SESSION['username'] ?? 'User');
$userRole = clean($_SESSION['role'] ?? 'Guest Access');
?>