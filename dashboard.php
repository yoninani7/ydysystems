<?php
include 'includes/header.php';

// Allowed pages mapping
$allowed_pages = [
    'dashboard'           => 'pages/dashboardinner.php',
    'company-profile'     => 'pages/company_structure/companyprofile.php',
    'org-chart'           => 'pages/company_structure/orgchart.php',
    'departments'         => 'pages/company_structure/departments.php',
    'job-positions'       => 'pages/company_structure/jobpositions.php',
    'branch-offices'      => 'pages/company_structure/branchoffices.php',
    'add-employee'        => 'pages/employees/add_emp_wizard.php',
    'employee-directory'  => 'pages/employees/employee_profile.php',
    'employment-types'    => 'pages/employees/employee_types.php',
    'probation-tracker'   => 'pages/employees/probation.php',
    'contract-renewals'   => 'pages/employees/contracts.php',
    'former-employees'    => 'pages/employees/former_employees.php',
    'retirement-planner'  => 'pages/employees/retirement_planner.php',
    'document-vault'      => 'pages/employees/attachment_vault.php',
    'employee-vault'     => 'pages/employees/attachment_emp_vault.php',
    'asset-tracking'      => 'pages/employees/asset_tracking.php',
    'job-vacancies'       => 'pages/talent_acquisition/job_vacancies.php',
    'candidates'          => 'pages/talent_acquisition/applicant_list.php',
    'interview-tracker'   => 'pages/talent_acquisition/interview_tracker.php',
    'internship'          => 'pages/talent_acquisition/internship_mgt.php',
    'Promote/Demote'      => 'pages/employee_movement/promote_demote.php',
    'transfers'           => 'pages/employee_movement/department_transfers.php',
    'attendance'          => 'pages/attendance/record_attendance.php',
    'daily-attendance'    => 'pages/attendance/daily_attendance.php',
    'attendance-reports'  => 'pages/attendance/attendance_reports.php',
    'leave-types'         => 'pages/leave_management/leave_types.php',
    'leave-requests'      => 'pages/leave_management/leave_requests.php',
    'leave-entitlement'   => 'pages/leave_management/leave_entitlement.php',
    'medical-claims'      => 'pages/benefits/medical_claims.php',
    'overtime-requests'   => 'pages/benefits/overtime_requests.php',
    'disciplinary-actions'=> 'pages/compliance_exit/disciplinary.php',
    'resignations'        => 'pages/compliance_exit/resignations.php',
    'termination'         => 'pages/compliance_exit/termination.php',
    'exit-clearance'      => 'pages/compliance_exit/exit_clearance.php',
    'training-needs'      => 'pages/training_dev/training_needs.php',
    'training-schedule'   => 'pages/training_dev/training_schedule.php',
    'performance-reviews' => 'pages/performance/performance_reviews.php',
    '360-feedback'        => 'pages/performance/360_feedback.php',
    'hr-analytics'        => 'pages/reports_analytics/hr_analytics.php',
    'custom-reports'      => 'pages/reports_analytics/custom_reports.php',
    'user-management'     => 'pages/system_admin/user_management.php',
    'roles-permissions'   => 'pages/system_admin/roles_permission.php',
    'audit-logs'          => 'pages/system_admin/audit_logs.php',
];

$page = $_GET['page'] ?? 'dashboard';
if (!isset($allowed_pages[$page])) {
    $page = 'dashboard';
}
$page_file = $allowed_pages[$page];
// ── AJAX page request: return only the inner content ──
if (!empty($_GET['ajax'])) {
    include $page_file;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YDY HRM Enterprise</title>
    <script src="assets/js/chart.js" defer></script>
    <script src="assets/js/lucide.min.js" defer></script>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileSidebar()"></div>
<?php include 'includes/sidebar.php'; ?>

<main class="main" id="main-content">
    <?php include 'includes/topbar.php'; ?>
    <div class="content-area" id="content-area">
        <?php include $page_file; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
<script src="assets/js/core.js"></script>
</body>
</html>