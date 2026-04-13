<?php
session_start();
// For debugging: Remove the if block below temporarily to see if it fixes the "blank" issue. 
// If it does, your login session is the problem.
if(!isset($_SESSION['user_id'])) { 
    // die("Unauthorized"); 
}

$page = $_GET['page'] ?? '';

// FIX: Normalize the page ID (lowercase, replace / and spaces with -)
$page = strtolower(trim($page));
$page = str_replace(['/', ' ', '_'], '-', $page);

$allowed_pages = [
    // Dashboard
    'dashboard'            => 'pages/dashboardinner.php',

    // Company Structure
    'company-profile'      => 'pages/company_structure/companyprofile.php',
    'org-chart'            => 'pages/company_structure/orgchart.php',
    'departments'          => 'pages/company_structure/departments.php',
    'job-positions'        => 'pages/company_structure/jobpositions.php',
    'branch-offices'       => 'pages/company_structure/branchoffices.php',

    // Employees
    'add-emp-wizard'       => 'pages/employees/add_emp_wizard.php',
    'employee-directory'   => 'pages/employees/employee_profile.php',
    'employment-types'     => 'pages/employees/employee_types.php',
    'probation-tracker'    => 'pages/employees/probation.php',
    'contract-renewals'    => 'pages/employees/contracts.php',
    'former-employees'     => 'pages/employees/former_employees.php',
    'retirement-planner'   => 'pages/employees/retirement_planner.php',
    'document-vault'       => 'pages/employees/attachment_vault.php',
    'asset-tracking'       => 'pages/employees/asset_tracking.php',
    'employee-vault'       => 'pages/employees/attachment_vault.php',

    // Talent
    'job-vacancies'        => 'pages/talent_acquisition/job_vacancies.php',
    'candidates'           => 'pages/talent_acquisition/applicant_list.php',
    'interview-tracker'    => 'pages/talent_acquisition/interview_tracker.php',
    'internship'           => 'pages/talent_acquisition/internship_mgt.php',

    // Movement - FIX: Changed from promote-demote to match JS
    'promote-demote'       => 'pages/employee_movement/promote_demote.php',
    'transfers'            => 'pages/employee_movement/department_transfers.php',

    // Attendance
    'record-attendance'    => 'pages/attendance/record_attendance.php',
    'daily-attendance'     => 'pages/attendance/daily_attendance.php',
    'attendance-reports'   => 'pages/attendance/attendance_reports.php',

    // Leave
    'leave-types'          => 'pages/leave_management/leave_types.php',
    'leave-requests'       => 'pages/leave_management/leave_requests.php',
    'leave-entitlement'    => 'pages/leave_management/leave_entitlement.php',

    // Benefits
    'medical-claims'       => 'pages/benefits/medical_claims.php',
    'overtime-requests'    => 'pages/benefits/overtime_requests.php',

    // Training
    'training-needs'       => 'pages/training_dev/training_needs.php',
    'training-schedule'    => 'pages/training_dev/training_schedule.php',

    // Performance
    'performance-reviews'  => 'pages/performance/performance_reviews.php',
    '360-feedback'         => 'pages/performance/360_feedback.php',

    // Compliance
    'disciplinary-actions' => 'pages/compliance_exit/disciplinary.php',
    'resignations'         => 'pages/compliance_exit/resignations.php',
    'termination'          => 'pages/compliance_exit/termination.php',
    'exit-clearance'       => 'pages/compliance_exit/exit_clearance.php',

    // Admin
    'user-management'      => 'pages/system_admin/user_management.php',
    'roles-permissions'    => 'pages/system_admin/roles_permission.php',
    'audit-logs'           => 'pages/system_admin/audit_logs.php',
    
    // Reports
    'hr-analytics'         => 'pages/reports/hr_analytics.php',
    'custom-reports'       => 'pages/reports/custom_reports.php',
];

if (array_key_exists($page, $allowed_pages)) {
    $filePath = $allowed_pages[$page];
    if (file_exists($filePath)) {
        include $filePath;
    } else {
        echo "<div style='padding:20px; color:red; border:2px solid red; background:#fee2e2;'>";
        echo "<strong>Error:</strong> File not found at <code>$filePath</code>";
        echo "</div>";
    }
} else {
    echo "<div style='padding:20px; color:orange; border:2px solid orange; background:#fff7ed;'>";
    echo "<strong>Module Error:</strong> '$page' not recognized in router.<br>";
    echo "<small>Available: " . implode(', ', array_slice(array_keys($allowed_pages), 0, 5)) . "...</small>";
    echo "</div>";
}
?>