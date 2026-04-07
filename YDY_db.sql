CREATE DATABASE IF NOT EXISTS ydy_hrm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ydy_hrm;

SET FOREIGN_KEY_CHECKS = 0;  -- Allow forward-refs during setup


-- ============================================================
-- SECTION 1 : COMPANY & STRUCTURE
-- (Sidebar → Organization → Company Profile / Departments /
--  Job Positions / Branch Offices)
-- ============================================================

-- ── 1.1  Company Profile (single-row settings table) ─────────
CREATE TABLE company_profile (
    id                  INT             PRIMARY KEY AUTO_INCREMENT,
    legal_name          VARCHAR(200)    NOT NULL,
    trading_name        VARCHAR(200),
    ceo_name            VARCHAR(150),
    head_office         VARCHAR(255),
    entity_type         VARCHAR(100),                           -- 'Private Ltd. Co', 'PLC', etc.
    establishment_date  DATE,
    registration_no     VARCHAR(100),
    tin                 VARCHAR(50),                            -- Tax Identification Number
    vat_reg_number      VARCHAR(100),
    trade_license_no    VARCHAR(100),
    -- Operational Policies
    work_week_desc      VARCHAR(200),                           -- e.g. 'Mon-Fri (40 hrs) + Sat Half-day'
    probation_days      INT             DEFAULT 90,
    retirement_age      INT             DEFAULT 60,
    -- Treasury
    main_bank           VARCHAR(150),
    bank_account_primary VARCHAR(100),
    base_currency       VARCHAR(20)     DEFAULT 'ETB',
    fiscal_start        VARCHAR(100),                           -- e.g. 'Hamle 01 (July 08)'
    -- Digital Identity
    website             VARCHAR(255),
    helpdesk_email      VARCHAR(150),
    corporate_phone     VARCHAR(50),
    linkedin_handle     VARCHAR(150),
    telegram_handle     VARCHAR(150),
    facebook_handle     VARCHAR(150),
    software_version    VARCHAR(50)     DEFAULT 'v1.0.0-YDY',
    updated_at          TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ── 1.2  Employment Types (needed before employees FK) ────────
--         Referenced by both employees and job_vacancies
CREATE TABLE employment_types (
    id          INT             PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,                       -- 'Permanent / Full-Time', 'Fixed-Term Contract', etc.
    description TEXT,
    benefits    ENUM('Yes','No','Partial') DEFAULT 'Yes',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);


-- ── 1.3  Branches (manager FK added after employees table) ────
CREATE TABLE branches (
    id          INT             PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,
    city        VARCHAR(100),
    address     TEXT,
    phone       VARCHAR(50),
    email       VARCHAR(150),
    manager_id  INT             NULL,                           -- FK → employees (added below via ALTER)
    status      ENUM('Active','Inactive') DEFAULT 'Active',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ── 1.4  Departments (head FK added after employees table) ────
CREATE TABLE departments (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    code             VARCHAR(20),                               -- e.g. 'D001'
    name             VARCHAR(150) NOT NULL,
    head_employee_id INT          NULL,                         -- FK → employees (added below via ALTER)
    branch_id        INT          NULL,
    location         VARCHAR(150),
    status           ENUM('Active','Inactive') DEFAULT 'Active',
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);


-- ── 1.5  Job Positions ────────────────────────────────────────
CREATE TABLE job_positions (
    id            INT           PRIMARY KEY AUTO_INCREMENT,
    title         VARCHAR(200)  NOT NULL,
    department_id INT           NULL,
    grade         VARCHAR(20),                                  -- L1, L2, M1, S1 etc.
    min_salary    DECIMAL(15,2),
    max_salary    DECIMAL(15,2),
    headcount     INT           DEFAULT 0,                      -- approved headcount slots
    status        ENUM('Active','Inactive') DEFAULT 'Active',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);


-- ============================================================
-- SECTION 2 : EMPLOYEES
-- (Sidebar → Employees → Employee Profile / Employment Types /
--  Probation / Contract Renewals / Former Employees /
--  Retirement Planner / Attachment Vault / Asset Tracking)
-- ============================================================

-- ── 2.1  Employees (main master table) ───────────────────────
CREATE TABLE employees (
    id                        INT          PRIMARY KEY AUTO_INCREMENT,
    employee_id               VARCHAR(20)  UNIQUE NOT NULL,     -- e.g. 'E0001'

    -- Step 1 : Personal Identity
    first_name                VARCHAR(100) NOT NULL,
    middle_name               VARCHAR(100),
    last_name                 VARCHAR(100) NOT NULL,
    username                  VARCHAR(100) UNIQUE,
    date_of_birth             DATE,
    gender                    ENUM('Male','Female'),
    nationality               VARCHAR(100) DEFAULT 'Ethiopian',
    marital_status            ENUM('Single','Married','Divorced','Widowed'),
    place_of_birth            VARCHAR(150),
    profile_photo             VARCHAR(255),                     -- relative file path

    -- Step 2 : Contact
    personal_phone            VARCHAR(50),
    personal_email            VARCHAR(150),
    permanent_address         TEXT,
    city                      VARCHAR(100),
    postal_code               VARCHAR(20),

    -- Step 3 : Employment Placement
    department_id             INT          NULL,
    job_position_id           INT          NULL,
    employment_type_id        INT          NULL,
    branch_id                 INT          NULL,
    hire_date                 DATE,
    contract_end_date         DATE         NULL,                -- for fixed-term / internship
    hours_per_week            INT          NULL,                -- for part-time
    probation_period          VARCHAR(50),                      -- e.g. '90 Days (Standard)'
    project_name              VARCHAR(200) NULL,                -- for temporary/casual type
    reports_to_id             INT          NULL,                -- self-join → manager

    -- Step 4 : Finance & Treasury
    gross_salary              DECIMAL(15,2),
    tin                       VARCHAR(50),
    bank_name                 VARCHAR(100),
    bank_account              VARCHAR(100),

    -- Step 5 : Compliance / Emergency Contact
    emergency_contact_name    VARCHAR(150),
    emergency_contact_phone   VARCHAR(50),
    emergency_contact_relation VARCHAR(100),

    -- General Status
    status                    ENUM('Active','Inactive','On Leave','Resigned','Terminated','Retired')
                                           DEFAULT 'Active',
    created_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (department_id)      REFERENCES departments(id)      ON DELETE SET NULL,
    FOREIGN KEY (job_position_id)    REFERENCES job_positions(id)    ON DELETE SET NULL,
    FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id)          REFERENCES branches(id)         ON DELETE SET NULL,
    FOREIGN KEY (reports_to_id)      REFERENCES employees(id)        ON DELETE SET NULL
);

-- Back-fill FKs that needed the employees table first
ALTER TABLE branches
    ADD CONSTRAINT fk_branch_manager
    FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;

ALTER TABLE departments
    ADD CONSTRAINT fk_dept_head
    FOREIGN KEY (head_employee_id) REFERENCES employees(id) ON DELETE SET NULL;


-- ── 2.2  Employee Contracts (for Contract Renewals page) ──────
CREATE TABLE employee_contracts (
    id                INT          PRIMARY KEY AUTO_INCREMENT,
    employee_id       INT          NOT NULL,
    employment_type_id INT         NOT NULL,
    start_date        DATE         NOT NULL,
    end_date          DATE,
    renewal_count     INT          DEFAULT 0,
    status            ENUM('Active','Expired','Renewed','Terminated') DEFAULT 'Active',
    notes             TEXT,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)        REFERENCES employees(id)        ON DELETE CASCADE,
    FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE RESTRICT
);


-- ── 2.3  Probation Records (for Probation Tracker page) ───────
CREATE TABLE probation_records (
    id             INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id    INT       NOT NULL,
    start_date     DATE      NOT NULL,
    end_date       DATE      NOT NULL,
    status         ENUM('Active','Completed','Extended','Failed') DEFAULT 'Active',
    reviewed_by    INT       NULL,
    review_notes   TEXT,
    confirmed_at   DATE      NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 2.4  Former Employees (archive on offboarding) ────────────
CREATE TABLE former_employees (
    id                INT           PRIMARY KEY AUTO_INCREMENT,
    original_emp_id   INT           NOT NULL,                   -- reference to employees.id
    employee_code     VARCHAR(20),
    full_name         VARCHAR(200)  NOT NULL,
    last_department   VARCHAR(150),
    last_job_position VARCHAR(200),
    last_salary       DECIMAL(15,2),
    exit_date         DATE,
    exit_type         ENUM('Resigned','Terminated','Retired','End of Contract','Deceased','Other'),
    exit_reason       TEXT,
    service_years     DECIMAL(5,2),
    rehire_eligible   ENUM('Yes','No') DEFAULT 'Yes',
    notes             TEXT,
    archived_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
    -- No FK to employees; record must survive employee deletion
);


-- ── 2.5  Document Types (vault schema, company-defined list) ──
CREATE TABLE document_types (
    id           INT          PRIMARY KEY AUTO_INCREMENT,
    code         VARCHAR(50)  UNIQUE NOT NULL,                  -- 'contract', 'nda', 'tin', etc.
    name         VARCHAR(200) NOT NULL,
    category     VARCHAR(100),                                  -- 'Legal', 'Identity', 'Tax', etc.
    is_mandatory BOOLEAN      DEFAULT TRUE,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);


-- ── 2.6  Employee Documents (Attachment Vault) ────────────────
CREATE TABLE employee_documents (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    employee_id      INT          NOT NULL,
    document_type_id INT          NOT NULL,
    file_name        VARCHAR(255),
    file_path        VARCHAR(500),
    file_size_kb     INT,
    mime_type        VARCHAR(100),
    status           ENUM('Uploaded','Verified','Expired','Missing') DEFAULT 'Uploaded',
    uploaded_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)      REFERENCES employees(id)     ON DELETE CASCADE,
    FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE RESTRICT
);


-- ── 2.7  Asset Categories ─────────────────────────────────────
CREATE TABLE asset_categories (
    id         INT          PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,                           -- 'IT Hardware', 'Fleet/Vehicles', etc.
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);


-- ── 2.8  Assets (Asset Tracking) ─────────────────────────────
CREATE TABLE assets (
    id                     INT           PRIMARY KEY AUTO_INCREMENT,
    asset_code             VARCHAR(50)   UNIQUE NOT NULL,       -- 'AST-2001'
    name                   VARCHAR(200)  NOT NULL,
    category_id            INT           NULL,
    serial_number          VARCHAR(150),
    asset_value            DECIMAL(15,2),
    current_custodian_id   INT           NULL,
    previous_custodian_id  INT           NULL,
    location_branch_id     INT           NULL,
    warranty_expiry        DATE,
    status                 ENUM('Active','In Repair','Disposed','Lost') DEFAULT 'Active',
    created_at             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id)           REFERENCES asset_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (current_custodian_id)  REFERENCES employees(id)        ON DELETE SET NULL,
    FOREIGN KEY (previous_custodian_id) REFERENCES employees(id)        ON DELETE SET NULL,
    FOREIGN KEY (location_branch_id)    REFERENCES branches(id)         ON DELETE SET NULL
);


-- ── 2.9  Asset Assignment History (for Reassign modal) ────────
CREATE TABLE asset_assignments (
    id               INT       PRIMARY KEY AUTO_INCREMENT,
    asset_id         INT       NOT NULL,
    assigned_from_id INT       NULL,                            -- previous custodian
    assigned_to_id   INT       NOT NULL,                        -- new custodian
    assigned_by      INT       NULL,                            -- HR user who performed it
    assigned_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returned_at      TIMESTAMP NULL,
    notes            TEXT,
    FOREIGN KEY (asset_id)         REFERENCES assets(id)    ON DELETE CASCADE,
    FOREIGN KEY (assigned_from_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to_id)   REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by)      REFERENCES employees(id) ON DELETE SET NULL
);


-- ============================================================
-- SECTION 3 : TALENT ACQUISITION
-- (Sidebar → Talent Acquisition → Job Vacancies / Candidates /
--  Interview Tracker / Internship Management)
-- ============================================================

-- ── 3.1  Job Vacancies ───────────────────────────────────────
CREATE TABLE job_vacancies (
    id                 INT          PRIMARY KEY AUTO_INCREMENT,
    title              VARCHAR(200) NOT NULL,
    department_id      INT          NULL,
    branch_id          INT          NULL,
    employment_type_id INT          NULL,
    description        TEXT,
    requirements       TEXT,
    posted_date        DATE,
    deadline_date      DATE,
    openings           INT          DEFAULT 1,
    status             ENUM('Open','On Hold','Filled','Closed') DEFAULT 'Open',
    created_by         INT          NULL,                       -- FK → employees (HR user)
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id)      REFERENCES departments(id)      ON DELETE SET NULL,
    FOREIGN KEY (branch_id)          REFERENCES branches(id)         ON DELETE SET NULL,
    FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)         REFERENCES employees(id)        ON DELETE SET NULL
);


-- ── 3.2  Candidates / Job Applicants ─────────────────────────
CREATE TABLE candidates (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    vacancy_id    INT          NULL,
    full_name     VARCHAR(200) NOT NULL,
    email         VARCHAR(150),
    phone         VARCHAR(50),
    cv_path       VARCHAR(500),
    applied_date  DATE,
    current_stage ENUM('Applied','Screening','Interview','Assessment','Offer','Hired','Rejected')
                               DEFAULT 'Applied',
    source        VARCHAR(100),                                 -- LinkedIn, Referral, Walk-in, etc.
    notes         TEXT,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vacancy_id) REFERENCES job_vacancies(id) ON DELETE SET NULL
);


-- ── 3.3  Interview Tracker ────────────────────────────────────
CREATE TABLE interviews (
    id             INT       PRIMARY KEY AUTO_INCREMENT,
    candidate_id   INT       NOT NULL,
    vacancy_id     INT       NULL,
    interviewer_id INT       NULL,
    interview_date DATE,
    interview_time TIME,
    mode           ENUM('In-Person','Video Call','Phone') DEFAULT 'In-Person',
    result         ENUM('Scheduled','Passed','Failed','On Hold','No Show') DEFAULT 'Scheduled',
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id)   REFERENCES candidates(id)  ON DELETE CASCADE,
    FOREIGN KEY (vacancy_id)     REFERENCES job_vacancies(id) ON DELETE SET NULL,
    FOREIGN KEY (interviewer_id) REFERENCES employees(id)   ON DELETE SET NULL
);


-- ── 3.4  Internship Management ───────────────────────────────
CREATE TABLE internships (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    intern_code      VARCHAR(30)  UNIQUE,                       -- 'INT-26-001'
    full_name        VARCHAR(200) NOT NULL,
    institution      VARCHAR(200),
    department_id    INT          NULL,
    mentor_id        INT          NULL,
    start_date       DATE,
    end_date         DATE,
    evaluation_score DECIMAL(5,2) NULL,
    status           ENUM('Active','Completed','Terminated') DEFAULT 'Active',
    potential_hire   BOOLEAN      DEFAULT FALSE,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (mentor_id)     REFERENCES employees(id)   ON DELETE SET NULL
);


-- ============================================================
-- SECTION 4 : EMPLOYEE MOVEMENT
-- (Sidebar → Employee Movement → Promote/Demote / Transfers)
-- ============================================================

-- ── 4.1  Promotions & Demotions ──────────────────────────────
CREATE TABLE promotions (
    id                  INT           PRIMARY KEY AUTO_INCREMENT,
    employee_id         INT           NOT NULL,
    change_type         ENUM('Promotion','Demotion') NOT NULL,
    from_position_id    INT           NULL,
    to_position_id      INT           NULL,
    from_department_id  INT           NULL,
    to_department_id    INT           NULL,
    old_salary          DECIMAL(15,2),
    new_salary          DECIMAL(15,2),
    effective_date      DATE,
    reason              TEXT,
    approved_by         INT           NULL,
    status              ENUM('Pending','Approved','Rejected','Processing') DEFAULT 'Pending',
    created_at          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)        REFERENCES employees(id)    ON DELETE CASCADE,
    FOREIGN KEY (from_position_id)   REFERENCES job_positions(id) ON DELETE SET NULL,
    FOREIGN KEY (to_position_id)     REFERENCES job_positions(id) ON DELETE SET NULL,
    FOREIGN KEY (from_department_id) REFERENCES departments(id)  ON DELETE SET NULL,
    FOREIGN KEY (to_department_id)   REFERENCES departments(id)  ON DELETE SET NULL,
    FOREIGN KEY (approved_by)        REFERENCES employees(id)    ON DELETE SET NULL
);


-- ── 4.2  Department Transfers ─────────────────────────────────
CREATE TABLE transfers (
    id                  INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id         INT       NOT NULL,
    from_department_id  INT       NULL,
    to_department_id    INT       NULL,
    from_branch_id      INT       NULL,
    to_branch_id        INT       NULL,
    request_date        DATE,
    effective_date      DATE,
    reason              TEXT,
    approved_by         INT       NULL,
    status              ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)        REFERENCES employees(id)   ON DELETE CASCADE,
    FOREIGN KEY (from_department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (to_department_id)   REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (from_branch_id)     REFERENCES branches(id)   ON DELETE SET NULL,
    FOREIGN KEY (to_branch_id)       REFERENCES branches(id)   ON DELETE SET NULL,
    FOREIGN KEY (approved_by)        REFERENCES employees(id)   ON DELETE SET NULL
);


-- ============================================================
-- SECTION 5 : ATTENDANCE
-- (Sidebar → Attendance → Record Attendance /
--  Daily Attendance / Attendance Reports)
-- ============================================================

-- ── 5.1  Attendance Records ───────────────────────────────────
--         P=Present  H=Half-day(Sat)  A=Absent  L=Leave  O=Off(Sun)
CREATE TABLE attendance (
    id             INT      PRIMARY KEY AUTO_INCREMENT,
    employee_id    INT      NOT NULL,
    attendance_date DATE    NOT NULL,
    status         ENUM('P','H','A','L','O') NOT NULL DEFAULT 'P',
    check_in       TIME     NULL,
    check_out      TIME     NULL,
    hours_worked   DECIMAL(4,2) NULL,
    overtime_hours DECIMAL(4,2) DEFAULT 0.00,
    is_late        BOOLEAN  DEFAULT FALSE,
    month          TINYINT  NOT NULL,                           -- 0–11 matching JS Date.getMonth()
    year           SMALLINT NOT NULL,
    recorded_by    INT      NULL,                               -- HR user
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_emp_date (employee_id, attendance_date),     -- one record per employee per day
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES employees(id) ON DELETE SET NULL
);


-- ============================================================
-- SECTION 6 : LEAVE MANAGEMENT
-- (Sidebar → Leave Management → Leave Types /
--  Leave Requests / Leave Entitlement)
-- ============================================================

-- ── 6.1  Leave Types ─────────────────────────────────────────
CREATE TABLE leave_types (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    name             VARCHAR(150) NOT NULL,                     -- 'Annual Leave', 'Sick Leave', etc.
    days_per_year    INT          NULL,                         -- NULL = unlimited / case-by-case
    carryover_days   INT          DEFAULT 0,
    is_paid          ENUM('Yes','No','Partial') DEFAULT 'Yes',
    requires_approval BOOLEAN     DEFAULT TRUE,
    description      TEXT,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);


-- ── 6.2  Leave Entitlements (per employee, per fiscal year) ──
CREATE TABLE leave_entitlements (
    id                 INT      PRIMARY KEY AUTO_INCREMENT,
    employee_id        INT      NOT NULL,
    leave_type_id      INT      NOT NULL,
    fiscal_year        SMALLINT NOT NULL,                       -- e.g. 2026
    total_days         INT      DEFAULT 0,
    used_days          INT      DEFAULT 0,
    carried_over_days  INT      DEFAULT 0,
    -- balance_days is calculated in PHP: total + carried - used
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_emp_leave_year (employee_id, leave_type_id, fiscal_year),
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
);


-- ── 6.3  Leave Requests ──────────────────────────────────────
CREATE TABLE leave_requests (
    id            INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id   INT       NOT NULL,
    leave_type_id INT       NOT NULL,
    from_date     DATE      NOT NULL,
    to_date       DATE      NOT NULL,
    total_days    INT       NOT NULL,
    reason        TEXT,
    approved_by   INT       NULL,
    status        ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    reviewed_at   TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by)   REFERENCES employees(id)   ON DELETE SET NULL
);


-- ============================================================
-- SECTION 7 : BENEFITS
-- (Sidebar → Benefits → Medical Claims / Overtime Requests)
-- ============================================================

-- ── 7.1  Medical Claims ──────────────────────────────────────
CREATE TABLE medical_claims (
    id             INT         PRIMARY KEY AUTO_INCREMENT,
    claim_code     VARCHAR(30) UNIQUE,                          -- 'MC-001'
    employee_id    INT         NOT NULL,
    category       ENUM('Doctor Visit','Specialist','Prescription','Dental','Vision','Hospital') NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    receipt_attached BOOLEAN   DEFAULT FALSE,
    receipt_path   VARCHAR(500),
    submitted_date DATE,
    approved_by    INT         NULL,
    status         ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    reviewed_at    TIMESTAMP   NULL,
    notes          TEXT,
    created_at     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 7.2  Overtime Requests ───────────────────────────────────
CREATE TABLE overtime_requests (
    id             INT         PRIMARY KEY AUTO_INCREMENT,
    employee_id    INT         NOT NULL,
    overtime_date  DATE        NOT NULL,
    hours          DECIMAL(4,2) NOT NULL,
    reason         TEXT,
    submitted_date DATE,
    approved_by    INT         NULL,
    status         ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    reviewed_at    TIMESTAMP   NULL,
    created_at     TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
);


-- ============================================================
-- SECTION 8 : COMPLIANCE & EXIT
-- (Sidebar → Compliance & Exit → Disciplinary Actions /
--  Resignations / Separation & Exit / Exit Clearance)
-- ============================================================

-- ── 8.1  Disciplinary Actions ────────────────────────────────
CREATE TABLE disciplinary_actions (
    id            INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id   INT       NOT NULL,
    action_type   ENUM('Verbal Warning','Written Warning','Final Warning','Suspension','Demotion') NOT NULL,
    incident_date DATE,
    issued_date   DATE,
    issued_by     INT       NULL,
    description   TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by)   REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 8.2  Resignations ────────────────────────────────────────
CREATE TABLE resignations (
    id           INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id  INT       NOT NULL,
    reason_type  ENUM('Harassment','Unfair Treatment','Pay Dispute','Safety Concern',
                       'Discrimination','Work Conditions','Personal','Other') NOT NULL,
    filed_date   DATE,
    assigned_to  INT       NULL,
    priority     ENUM('High','Medium','Low') DEFAULT 'Medium',
    status       ENUM('Pending','Under Review','Resolved') DEFAULT 'Pending',
    notes        TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 8.3  Separations / Terminations ─────────────────────────
CREATE TABLE separations (
    id               INT           PRIMARY KEY AUTO_INCREMENT,
    employee_id      INT           NOT NULL,
    separation_type  ENUM('Resignation','Involuntary','Retirement','End of Contract','Deceased') NOT NULL,
    notice_date      DATE,
    last_working_day DATE,
    final_settlement DECIMAL(15,2) NULL,
    clearance_status ENUM('Pending','Done') DEFAULT 'Pending',
    status           ENUM('In Progress','Complete') DEFAULT 'In Progress',
    notes            TEXT,
    processed_by     INT           NULL,
    created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)  REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 8.4  Exit Clearance ──────────────────────────────────────
CREATE TABLE exit_clearances (
    id              INT       PRIMARY KEY AUTO_INCREMENT,
    employee_id     INT       NOT NULL,
    separation_id   INT       NULL,
    it_cleared      BOOLEAN   DEFAULT FALSE,
    finance_cleared BOOLEAN   DEFAULT FALSE,
    hr_cleared      BOOLEAN   DEFAULT FALSE,
    admin_cleared   BOOLEAN   DEFAULT FALSE,
    assets_cleared  BOOLEAN   DEFAULT FALSE,
    overall_status  ENUM('Pending','In Progress','Cleared') DEFAULT 'Pending',
    cleared_at      TIMESTAMP NULL,
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)   REFERENCES employees(id)  ON DELETE CASCADE,
    FOREIGN KEY (separation_id) REFERENCES separations(id) ON DELETE SET NULL
);


-- ============================================================
-- SECTION 9 : TRAINING & DEVELOPMENT
-- (Sidebar → Training & Dev → Training Needs Analysis /
--  Training Schedule)
-- ============================================================

-- ── 9.1  Training Needs Analysis ─────────────────────────────
CREATE TABLE training_needs (
    id                   INT          PRIMARY KEY AUTO_INCREMENT,
    department_id        INT          NULL,
    skill_gap            VARCHAR(200) NOT NULL,
    priority             ENUM('High','Medium','Low') DEFAULT 'Medium',
    affected_employees   INT          DEFAULT 0,
    proposed_training    VARCHAR(200),                          -- 'Workshop', 'Online Course', etc.
    status               ENUM('Pending','Approved','Ongoing') DEFAULT 'Pending',
    created_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);


-- ── 9.2  Training Schedules ──────────────────────────────────
CREATE TABLE training_schedules (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    course_name   VARCHAR(200) NOT NULL,
    department_id INT          NULL,
    trainer_id    INT          NULL,
    training_date DATE,
    training_time TIME,
    venue         VARCHAR(200),
    total_seats   INT          DEFAULT 0,
    enrolled_seats INT         DEFAULT 0,
    status        ENUM('Confirmed','Open','Cancelled') DEFAULT 'Open',
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (trainer_id)    REFERENCES employees(id)   ON DELETE SET NULL
);


-- ── 9.3  Training Enrollments (employees per session) ─────────
CREATE TABLE training_enrollments (
    id          INT       PRIMARY KEY AUTO_INCREMENT,
    training_id INT       NOT NULL,
    employee_id INT       NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_training_emp (training_id, employee_id),
    FOREIGN KEY (training_id) REFERENCES training_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id)          ON DELETE CASCADE
);


-- ============================================================
-- SECTION 10 : PERFORMANCE
-- (Sidebar → Performance → Performance Reviews / 360° Feedback)
-- ============================================================

-- ── 10.1  Performance Reviews ────────────────────────────────
CREATE TABLE performance_reviews (
    id            INT         PRIMARY KEY AUTO_INCREMENT,
    employee_id   INT         NOT NULL,
    reviewer_id   INT         NULL,
    review_period VARCHAR(50),                                  -- e.g. 'Q1 2026'
    overall_score DECIMAL(4,2),                                 -- 0.0 – 10.0
    rating        ENUM('Exceptional','Exceeds','Meets','Below') DEFAULT 'Meets',
    comments      TEXT,
    status        ENUM('Pending','Submitted','Acknowledged') DEFAULT 'Pending',
    created_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES employees(id) ON DELETE SET NULL
);


-- ── 10.2  360° Feedback (header record per subject) ───────────
CREATE TABLE feedback_360 (
    id                    INT         PRIMARY KEY AUTO_INCREMENT,
    subject_employee_id   INT         NOT NULL,
    review_period         VARCHAR(50),
    total_respondents     INT         DEFAULT 0,
    completed_respondents INT         DEFAULT 0,
    average_score         DECIMAL(4,2) NULL,
    status                ENUM('Open','In Progress','Closed') DEFAULT 'Open',
    created_at            TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_employee_id) REFERENCES employees(id) ON DELETE CASCADE
);


-- ── 10.3  360° Feedback Responses (one row per respondent) ────
CREATE TABLE feedback_360_responses (
    id           INT       PRIMARY KEY AUTO_INCREMENT,
    feedback_id  INT       NOT NULL,
    respondent_id INT      NULL,
    score        DECIMAL(4,2),
    comments     TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (feedback_id)   REFERENCES feedback_360(id) ON DELETE CASCADE,
    FOREIGN KEY (respondent_id) REFERENCES employees(id)    ON DELETE SET NULL
);


-- ============================================================
-- SECTION 11 : ANALYTICS & SYSTEM
-- (Sidebar → Reports & Analytics / System Admin)
-- ============================================================

-- ── 11.1  Custom Report Templates ────────────────────────────
CREATE TABLE report_templates (
    id         INT          PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(200) NOT NULL,
    module     VARCHAR(100),                                    -- 'Employees', 'Payroll', etc.
    date_from  DATE         NULL,
    date_to    DATE         NULL,
    filters    JSON         NULL,                               -- serialized filter config
    created_by INT          NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    -- created_by FK is added after users table below
);


-- ── 11.2  System Users ────────────────────────────────────────
CREATE TABLE users (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    user_code     VARCHAR(30)  UNIQUE,                          -- 'USR-001'
    employee_id   INT          NULL,                            -- linked employee record
    name          VARCHAR(200) NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('Super Admin','HR Manager','Finance Officer','Department Head','Employee')
                               DEFAULT 'Employee',
    department_id INT          NULL,
    status        ENUM('Active','Inactive') DEFAULT 'Active',
    last_login    TIMESTAMP    NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Back-fill created_by FK on report_templates
ALTER TABLE report_templates
    ADD CONSTRAINT fk_report_user
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;


-- ── 11.3  Roles (standard named roles) ───────────────────────
CREATE TABLE roles (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) UNIQUE NOT NULL,                   -- 'Super Admin', 'HRM User', etc.
    description TEXT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);


-- ── 11.4  Role Permissions (module-level access per role) ────
CREATE TABLE role_permissions (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    role_id     INT          NOT NULL,
    module_id   VARCHAR(50)  NOT NULL,                          -- 'm-org', 'm-emp', etc.
    module_name VARCHAR(150),
    submodule   VARCHAR(150) NULL,                              -- specific sub-page or NULL = whole module
    can_access  BOOLEAN      DEFAULT TRUE,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_module_sub (role_id, module_id, submodule),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);


-- ── 11.5  Individual User Permission Overrides ────────────────
--          Used when "Individual Roles" mode is selected in the UI
CREATE TABLE user_permission_overrides (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    user_id     INT          NOT NULL,
    module_id   VARCHAR(50)  NOT NULL,
    module_name VARCHAR(150),
    submodule   VARCHAR(150) NULL,
    can_access  BOOLEAN      DEFAULT TRUE,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_module_sub (user_id, module_id, submodule),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- ── 11.6  Audit Logs ─────────────────────────────────────────
CREATE TABLE audit_logs (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    user_id     INT           NULL,
    user_name   VARCHAR(200),                                   -- snapshot in case user is deleted
    action      ENUM('CREATE','UPDATE','DELETE','LOGIN','APPROVE','EXPORT','VIEW') NOT NULL,
    module      VARCHAR(100),
    record_ref  VARCHAR(100),                                   -- e.g. 'E-0042', 'AST-2001'
    old_value   JSON          NULL,                             -- before state
    new_value   JSON          NULL,                             -- after state
    ip_address  VARCHAR(45),
    logged_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


-- ============================================================
-- PERFORMANCE INDEXES
-- Commonly filtered / joined columns
-- ============================================================

-- Employees
CREATE INDEX idx_emp_dept       ON employees(department_id);
CREATE INDEX idx_emp_branch     ON employees(branch_id);
CREATE INDEX idx_emp_type       ON employees(employment_type_id);
CREATE INDEX idx_emp_status     ON employees(status);
CREATE INDEX idx_emp_hire_date  ON employees(hire_date);

-- Attendance
CREATE INDEX idx_att_month_year ON attendance(year, month);
CREATE INDEX idx_att_emp_date   ON attendance(employee_id, attendance_date);

-- Leave
CREATE INDEX idx_lr_employee    ON leave_requests(employee_id);
CREATE INDEX idx_lr_status      ON leave_requests(status);
CREATE INDEX idx_lr_dates       ON leave_requests(from_date, to_date);

-- Contracts
CREATE INDEX idx_con_emp        ON employee_contracts(employee_id);
CREATE INDEX idx_con_end        ON employee_contracts(end_date);

-- Assets
CREATE INDEX idx_ast_custodian  ON assets(current_custodian_id);
CREATE INDEX idx_ast_status     ON assets(status);

-- Audit Logs
CREATE INDEX idx_audit_user     ON audit_logs(user_id);
CREATE INDEX idx_audit_module   ON audit_logs(module);
CREATE INDEX idx_audit_logged   ON audit_logs(logged_at);

-- Documents
CREATE INDEX idx_doc_employee   ON employee_documents(employee_id);
CREATE INDEX idx_doc_status     ON employee_documents(status);


-- -- ============================================================
-- -- SEED DATA — Core lookup tables
-- -- (Minimum data required for the app to function)
-- -- ============================================================

-- -- Employment Types (matches the UI dropdown exactly)
-- INSERT INTO employment_types (name, description, benefits) VALUES
-- ('Permanent / Full-Time', 'Regular employee with full benefits',    'Yes'),
-- ('Fixed-Term Contract',   'Time-bound employment agreement',        'Partial'),
-- ('Part-Time',             'Less than 40 hours per week',            'Partial'),
-- ('Internship',            'Student or graduate trainee',            'No'),
-- ('Temporary / Casual',    'Short-term project-based assignment',     'No');

-- -- Leave Types (matches the UI table)
-- INSERT INTO leave_types (name, days_per_year, carryover_days, is_paid, requires_approval) VALUES
-- ('Annual Leave',      20,   5, 'Yes',     TRUE),
-- ('Sick Leave',        10,   0, 'Yes',     FALSE),
-- ('Maternity Leave',   90,   0, 'Yes',     TRUE),
-- ('Paternity Leave',   14,   0, 'Yes',     TRUE),
-- ('Bereavement Leave',  5,   0, 'Yes',     TRUE),
-- ('Unpaid Leave',      NULL, 0, 'No',      TRUE),
-- ('Study/Exam Leave',   5,   0, 'Partial', TRUE),
-- ('Public Holiday',    12,   0, 'Yes',     FALSE);

-- -- Document Types (matches VAULT_SCHEMA in the JS)
-- INSERT INTO document_types (code, name, category, is_mandatory) VALUES
-- ('contract',   'Signed Employment Contract',      'Legal',        TRUE),
-- ('cv',         'Curriculum Vitae (CV)',            'Identity',     TRUE),
-- ('academic',   'Academic Credentials',             'Education',    TRUE),
-- ('clearance',  'Clearance / Release Letter',       'History',      TRUE),
-- ('experience', 'Experience Letters',               'History',      FALSE),
-- ('coc',        'Certificate of Competence (COC)', 'Professional', FALSE),
-- ('guarantor',  'Guarantor Form & ID',              'Legal',        TRUE),
-- ('nda',        'Confidentiality / NDA Agreement',  'Compliance',   TRUE),
-- ('handbook',   'Acknowledgments',                  'Compliance',   TRUE),
-- ('national_id','National ID / Passport Copy',      'Identity',     TRUE),
-- ('tin',        'TIN Certification Document',       'Tax',          TRUE),
-- ('medical',    'Health & Fitness Clearance',       'Compliance',   TRUE);

-- -- Asset Categories
-- INSERT INTO asset_categories (name) VALUES
-- ('IT Hardware'),
-- ('Fleet / Vehicles'),
-- ('Office Furniture'),
-- ('Networking Equipment'),
-- ('Security Equipment');

-- -- Standard Roles (matches the roles-permissions UI)
-- INSERT INTO roles (name, description) VALUES
-- ('Super Admin',       'Full system authority — all modules unrestricted'),
-- ('HRM User',          'Standard HR operations across all modules'),
-- ('Department Manager','Limited visibility to own team data only');

-- -- Company Profile (single default row)
-- INSERT INTO company_profile (
--     legal_name, trading_name, ceo_name, head_office, entity_type,
--     establishment_date, registration_no, tin, vat_reg_number, trade_license_no,
--     work_week_desc, probation_days, retirement_age,
--     main_bank, bank_account_primary, base_currency, fiscal_start,
--     website, helpdesk_email, corporate_phone,
--     linkedin_handle, telegram_handle, facebook_handle
-- ) VALUES (
--     'YDY HRM Enterprise Ltd.', 'YDY Systems', 'YDY Systems',
--     'Mexico, Lideta, Addis Ababa', 'Private Ltd. Co',
--     '2010-10-12', 'MT/AA/14/667/09', '0019283746', '9928374-VAT-01', '01/01/14/19283',
--     'Mon — Fri (40 hrs) + Sat (Half day)', 90, 60,
--     'CBE (Commercial)', '1000192837465', 'ETB', 'Hamle 01 (July 08)',
--     'www.ydy-hrm.com', 'support@ydyhrm.com', '+251 11 667 89',
--     '/ydy-systems', '@YDY_Systems', '/YDY.Enterprise'
-- );


SET FOREIGN_KEY_CHECKS = 1;  -- Re-enable after all tables are created

-- ============================================================
-- END OF SCHEMA
-- ============================================================
