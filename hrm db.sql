-- =============================================================================
-- DATABASE: YDY HRM ENTERPRISE
-- =============================================================================

CREATE DATABASE IF NOT EXISTS ydy_hrm_enterprise;
USE ydy_hrm_enterprise;

-- -----------------------------------------------------------------------------
-- 1. SYSTEM ACCESS & SECURITY
-- -----------------------------------------------------------------------------

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- This table manages the System Roles (e.g., 'Super Admin', 'HR Manager', 'Employee'). 
-- It is the foundation for the "Roles & Permissions" page, allowing you to categorize 
-- users by their access level.

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) NOT NULL UNIQUE,
    module_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- This table lists every granular action possible in the system (e.g., 'edit_payroll', 'view_attendance'). 
-- Modules are grouped (Employees, Recruitment, Payroll) to match your sidebar navigation.

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    can_view BOOLEAN DEFAULT FALSE,
    can_create BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    can_approve BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_perm_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_perm_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- This is the "logic engine" for your Permissions Grid. It determines exactly 
-- which checkboxes are ticked for each role, controlling whether a user can 
-- just "View" a report or actually "Approve" a leave request.

-- -----------------------------------------------------------------------------
-- 2. ORGANIZATION STRUCTURE
-- -----------------------------------------------------------------------------

CREATE TABLE company_profile (
    id INT PRIMARY KEY DEFAULT 1,
    legal_name VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100) NULL,
    tin_number VARCHAR(100) NULL,
    vat_reg VARCHAR(100) NULL,
    establishment_date DATE NULL,
    currency VARCHAR(10) DEFAULT 'ETB',
    fiscal_year_start VARCHAR(50) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC+3',
    work_week VARCHAR(100) DEFAULT 'Mon - Fri',
    std_hours DECIMAL(4,2) DEFAULT 8.00,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    address_street TEXT NULL,
    city_country VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Stores the global master data seen on your "Company Profile" bento-grid. 
-- It holds legal identities, tax numbers, and system-wide settings like 
-- the default currency and work-week hours.

CREATE TABLE branch_offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_code VARCHAR(20) NOT NULL UNIQUE,
    branch_name VARCHAR(100) NOT NULL,
    city VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    address TEXT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active'
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Supports multi-location operations. Every employee and asset is linked 
-- to a branch, allowing for localized reporting and office management.

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_code VARCHAR(20) NOT NULL UNIQUE,
    dept_name VARCHAR(100) NOT NULL,
    parent_dept_id INT NULL,
    branch_id INT NULL,
    cost_center_code VARCHAR(50) NULL,
    headcount_budget INT NULL,
    status ENUM('Active', 'Inactive', 'Under Setup') DEFAULT 'Active',
    description TEXT NULL,
    CONSTRAINT fk_dept_parent FOREIGN KEY (parent_dept_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_dept_branch FOREIGN KEY (branch_id) REFERENCES branch_offices(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- This table creates the "Org Chart" hierarchy. The 'parent_dept_id' allows 
-- for sub-departments (e.g., 'QA' is a child of 'Engineering'), while 
-- cost centers link the department to financial budgets.

CREATE TABLE job_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pos_code VARCHAR(20) NOT NULL UNIQUE,
    job_title VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    grade_level VARCHAR(20) NULL,
    salary_min DECIMAL(15,2) NULL,
    salary_max DECIMAL(15,2) NULL,
    CONSTRAINT fk_job_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Defines the roles available in the company. It stores pay-grades and 
-- salary bands, ensuring that recruitment and payroll follow pre-defined 
-- compensation policies.

-- -----------------------------------------------------------------------------
-- 3. EMPLOYEES & COMPLIANCE
-- -----------------------------------------------------------------------------

CREATE TABLE employment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(10) NOT NULL UNIQUE,
    type_name VARCHAR(50) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Categorizes staff into groups like 'Full-Time', 'Contract', or 'Intern'. 
-- This is critical for the "Employee Composition" doughnut chart in your Dashboard.

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female') NULL,
    dob DATE NULL,
    marital_status VARCHAR(50) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    hire_date DATE NOT NULL,
    department_id INT NULL,
    job_position_id INT NULL,
    branch_id INT NULL,
    employment_type_id INT NULL,
    manager_id INT NULL,
    role_id INT NULL,
    status ENUM('Active', 'Inactive', 'On Leave', 'Terminated', 'Probation') DEFAULT 'Probation',
    probation_end_date DATE NULL,
    bank_account VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emp_dept FOREIGN KEY (department_id) REFERENCES departments(id),
    CONSTRAINT fk_emp_job FOREIGN KEY (job_position_id) REFERENCES job_positions(id),
    CONSTRAINT fk_emp_branch FOREIGN KEY (branch_offices_id) REFERENCES branch_offices(id),
    CONSTRAINT fk_emp_type FOREIGN KEY (employment_type_id) REFERENCES employment_types(id),
    CONSTRAINT fk_emp_manager FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL,
    CONSTRAINT fk_emp_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- The "Heart" of the system. This table stores all personnel data. 
-- The 'manager_id' allows the system to generate the visual "Organization Chart", 
-- while the 'status' drives the "Probation Tracker" and "Employee Directory".

CREATE TABLE document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doc_name VARCHAR(100) NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Defines what documents are required (e.g., 'National ID', 'NDA'). 
-- This table provides the labels for your "Attachment Vault" matrix.

CREATE TABLE employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    document_type_id INT NOT NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('Missing', 'Uploaded', 'Expired', 'Verified') DEFAULT 'Missing',
    expiry_date DATE NULL,
    uploaded_at TIMESTAMP NULL,
    CONSTRAINT fk_doc_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_type FOREIGN KEY (document_type_id) REFERENCES document_types(id)
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Tracks the actual files uploaded by employees. It powers the red/green 
-- "Vault Slots" in your UI, showing who is compliant and whose ID has expired.

-- -----------------------------------------------------------------------------
-- 4. ASSETS & OPERATIONS
-- -----------------------------------------------------------------------------

CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id_code VARCHAR(50) NOT NULL UNIQUE,
    asset_name VARCHAR(255) NOT NULL,
    serial_number VARCHAR(100) NULL,
    current_custodian_id INT NULL,
    previous_custodian_id INT NULL,
    branch_id INT NULL,
    status ENUM('Available', 'Assigned', 'Damaged', 'Lost') DEFAULT 'Available',
    CONSTRAINT fk_asset_curr FOREIGN KEY (current_custodian_id) REFERENCES employees(id) ON DELETE SET NULL,
    CONSTRAINT fk_asset_prev FOREIGN KEY (previous_custodian_id) REFERENCES employees(id) ON DELETE SET NULL,
    CONSTRAINT fk_asset_branch FOREIGN KEY (branch_offices_id) REFERENCES branch_offices(id)
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Powers the "Asset Registry". It tracks company property (laptops, cars) 
-- and maintains a history of who had the asset previously versus who has it now.

CREATE TABLE shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_code VARCHAR(10) NOT NULL UNIQUE,
    shift_name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT NULL DEFAULT 60,
    shift_type ENUM('Fixed', 'Split', 'Flexible') DEFAULT 'Fixed'
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Defines the "Shift Management" rules. It tells the system when 
-- an employee is expected to check in and out for attendance calculations.

CREATE TABLE daily_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    shift_id INT NULL,
    clock_in DATETIME NULL,
    clock_out DATETIME NULL,
    ot_hours DECIMAL(4,2) DEFAULT 0.00,
    status ENUM('Present', 'Absent', 'Late', 'On Leave') DEFAULT 'Present',
    CONSTRAINT fk_att_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_shift FOREIGN KEY (shift_id) REFERENCES shifts(id)
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- The logs for the "Daily Attendance" page. It compares clock-in times 
-- against the assigned shift to automatically mark someone as 'Late'.

-- -----------------------------------------------------------------------------
-- 5. RECRUITMENT & PERFORMANCE
-- -----------------------------------------------------------------------------

CREATE TABLE job_vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vacancy_ref VARCHAR(50) NOT NULL UNIQUE,
    job_position_id INT NOT NULL,
    branch_id INT NULL,
    status ENUM('Open', 'On Hold', 'Filled', 'Closed') DEFAULT 'Open',
    posted_date DATE NOT NULL,
    deadline_date DATE NULL,
    CONSTRAINT fk_vac_job FOREIGN KEY (job_position_id) REFERENCES job_positions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Used in the "Add Job Vacancies" page. It creates a record for an open 
-- role that candidates can apply for.

CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    source VARCHAR(100) NULL,
    resume_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Stores data for the "Job Applicant's List". It tracks potential hires 
-- before they become employees.

CREATE TABLE performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    period_name VARCHAR(50) NOT NULL,
    overall_score DECIMAL(3,1) NULL,
    rating_label VARCHAR(50) NULL,
    status ENUM('Draft', 'Submitted', 'Signed-Off') DEFAULT 'Draft',
    CONSTRAINT fk_rev_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
    CONSTRAINT fk_rev_rev FOREIGN KEY (reviewer_id) REFERENCES employees(id)
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Drives the "Performance Reviews" module. It links a subject (Employee) 
-- to a Reviewer and stores the final score used for "HR Analytics".

-- -----------------------------------------------------------------------------
-- 6. EXIT & SEPARATION
-- -----------------------------------------------------------------------------

CREATE TABLE exit_clearance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    it_clearance BOOLEAN DEFAULT FALSE,
    finance_clearance BOOLEAN DEFAULT FALSE,
    hr_clearance BOOLEAN DEFAULT FALSE,
    admin_clearance BOOLEAN DEFAULT FALSE,
    assets_returned BOOLEAN DEFAULT FALSE,
    final_settlement_amt DECIMAL(15,2) NULL,
    status ENUM('In Progress', 'Cleared') DEFAULT 'In Progress',
    CONSTRAINT fk_exit_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- Powers the "Exit Clearance" page. It acts as a digital checklist 
-- ensuring that IT, Finance, and HR have all signed off before an 
-- employee's final settlement is paid.

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id INT NULL,
    ip_address VARCHAR(45) NULL,
    details TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- DESCRIPTION: 
-- The system's "Black Box". Every time a user edits a salary, deletes a 
-- record, or logs in, it is recorded here for the "Audit Logs" page 
-- to ensure security and accountability.

-- -----------------------------------------------------------------------------
-- END OF SCRIPT
-- -----------------------------------------------------------------------------