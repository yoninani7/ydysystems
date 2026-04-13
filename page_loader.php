<?php
declare(strict_types=1);
define('IS_API', true);
require_once 'config.php';

// Security: reject unauthenticated requests immediately
if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo "<p class='page-error'>Session expired. Please log in again.</p>";
    exit;
}

// This is the master whitelist of every navigable page in your sidebar.
// The array key is the page ID (used in goPage() calls in sidebar.php)
// The array value is the path to the partial file inside your pages/ folder.
$pageMap = [
    "dashboard"            => "pages/dashboard.php",

    // Company Structure
    "company-profile"      => "pages/company_structure/company_profile.php",
    "org-chart"            => "pages/company_structure/org_chart.php",
    "departments"          => "pages/company_structure/departments.php",
    "job-positions"        => "pages/company_structure/job_positions.php",
    "branch-offices"       => "pages/company_structure/branch_offices.php",

    // Employees
    "employee-directory"   => "pages/employees/employee_directory.php",
    "add-employee"         => "pages/employees/add_employee.php",
    "employment-types"     => "pages/employees/employment_types.php",
    "probation-tracker"    => "pages/employees/probation_tracker.php",
    "contract-renewals"    => "pages/employees/contract_renewals.php",
    "former-employees"     => "pages/employees/former_employees.php",
    "retirement-planner"   => "pages/employees/retirement_planner.php",
    "document-vault"       => "pages/employees/document_vault.php",
    "asset-tracking"       => "pages/employees/asset_tracking.php",

    // Employee Movement
    "Promote/Demote"       => "pages/employee_movement/promote_demote.php",
    "transfers"            => "pages/employee_movement/transfers.php",

    // Talent Acquisition
    "job-vacancies"        => "pages/talent_acquisition/job_vacancies.php",
    "candidates"           => "pages/talent_acquisition/candidates.php",
    "interview-tracker"    => "pages/talent_acquisition/interview_tracker.php",
    "internship"           => "pages/talent_acquisition/internship.php",

    // Attendance
    "attendance"           => "pages/attendance/attendance.php",
    "daily-attendance"     => "pages/attendance/daily_attendance.php",
    "attendance-reports"   => "pages/attendance/attendance_reports.php",

    // Leave
    "leave-types"          => "pages/leave_management/leave_types.php",
    "leave-requests"       => "pages/leave_management/leave_requests.php",
    "leave-entitlement"    => "pages/leave_management/leave_entitlement.php",

    // Benefits
    "medical-claims"       => "pages/benefits/medical_claims.php",
    "overtime-requests"    => "pages/benefits/overtime_requests.php",

    // Compliance
    "disciplinary-actions" => "pages/compliance_exit/disciplinary_actions.php",
    "resignations"         => "pages/compliance_exit/resignations.php",
    "termination"          => "pages/compliance_exit/termination.php",
    "exit-clearance"       => "pages/compliance_exit/exit_clearance.php",

    // Training
    "training-needs"       => "pages/training_dev/training_needs.php",
    "training-schedule"    => "pages/training_dev/training_schedule.php",

    // Performance
    "performance-reviews"  => "pages/performance/performance_reviews.php",
    "360-feedback"         => "pages/performance/feedback_360.php",

    // Analytics
    "hr-analytics"         => "pages/reports_analytics/hr_analytics.php",
    "custom-reports"       => "pages/reports_analytics/custom_reports.php",

    // System Admin
    "user-management"      => "pages/system_admin/user_management.php",
    "roles-permissions"    => "pages/system_admin/roles_permissions.php",
    "audit-logs"           => "pages/system_admin/audit_logs.php",
];

$requested = trim($_GET["page"] ?? "");

if (!isset($pageMap[$requested])) {
    http_response_code(404);
    echo "<div class='under-construction'><p>Page not found or still under construction.</p></div>";
    exit;
}

$filePath = $pageMap[$requested];

if (!file_exists($filePath)) {
    echo "<div class='under-construction'><p>This section is under construction.</p></div>";
    exit;
}

require $filePath;
