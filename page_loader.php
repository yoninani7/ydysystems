<?php
declare(strict_types=1);
define('IS_API', true);
require_once 'config.php';

// Security check: if user is not logged in, send them away
if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo "<div class='page-error'><p>Session expired. Please log in again.</p></div>";
    exit;
}

// THIS IS THE MAP: left side = page ID from sidebar, right side = your actual file path
$pageMap = [
    'company-profile'      => 'pages/company_structure/companyprofile.php',
    'org-chart'            => 'pages/company_structure/orgchart.php',
    'departments'          => 'pages/company_structure/departments.php',
    'job-positions'        => 'pages/company_structure/jobpositions.php',
    'branch-offices'       => 'pages/company_structure/branchoffices.php',
    'employee-directory'   => 'pages/employees/employee_profile.php',
    'add-employee'         => 'pages/employees/add_emp_wizard.php',
    'employment-types'     => 'pages/employees/employee_types.php',
    'probation-tracker'    => 'pages/employees/probation.php',
    'contract-renewals'    => 'pages/employees/contracts.php',
    'former-employees'     => 'pages/employees/former_employees.php',
    'retirement-planner'   => 'pages/employees/retirement_planner.php',
    'document-vault'       => 'pages/employees/attachment_vault.php',
    'asset-tracking'       => 'pages/employees/asset_tracking.php',
    'Promote/Demote'       => 'pages/employee_movement/promote_demote.php',
    'transfers'            => 'pages/employee_movement/department_transfers.php',
    'job-vacancies'        => 'pages/talent_acquisition/job_vacancies.php',
    'candidates'           => 'pages/talent_acquisition/applicant_list.php',
    'interview-tracker'    => 'pages/talent_acquisition/interview_tracker.php',
    'internship'           => 'pages/talent_acquisition/internship_mgt.php',
    'attendance'           => 'pages/attendance/record_attendance.php',
    'daily-attendance'     => 'pages/attendance/daily_attendance.php',
    'attendance-reports'   => 'pages/attendance/attendance_reports.php',
    'leave-types'          => 'pages/leave_management/leave_types.php',
    'leave-requests'       => 'pages/leave_management/leave_requests.php',
    'leave-entitlement'    => 'pages/leave_management/leave_entitlement.php',
    'medical-claims'       => 'pages/benefits/medical_claims.php',
    'overtime-requests'    => 'pages/benefits/overtime_requests.php',
    'training-needs'       => 'pages/training_dev/training_needs.php',
    'training-schedule'    => 'pages/training_dev/training_schedule.php',
    'performance-reviews'  => 'pages/performance/performance_reviews.php',
    '360-feedback'         => 'pages/performance/360_feedback.php',
    'disciplinary-actions' => 'pages/compliance_exit/disciplinary.php',
    'resignations'         => 'pages/compliance_exit/resignations.php',
    'termination'          => 'pages/compliance_exit/termination.php',
    'exit-clearance'       => 'pages/compliance_exit/exit_clearance.php',
    'hr-analytics'         => 'pages/reports_analytics/hr_analytics.php',
    'custom-reports'       => 'pages/reports_analytics/custom_reports.php',
    'user-management'      => 'pages/system_admin/user_management.php',
    'roles-permissions'    => 'pages/system_admin/roles_permission.php',
    'audit-logs'           => 'pages/system_admin/audit_logs.php',
];

$requested = trim($_GET["page"] ?? "");

// Check if the requested page is in our map
if (!isset($pageMap[$requested])) {
    http_response_code(404);
    echo "<div class='page-error'><p>Page not found.</p></div>";
    exit;
}

$filePath = $pageMap[$requested];

// Check if the file actually exists in your pages/ folder
if (!file_exists($filePath)) {
    echo "<div class='under-construction'><p>This section is under construction.</p></div>";
    exit;
}

// Load the page
require $filePath;
