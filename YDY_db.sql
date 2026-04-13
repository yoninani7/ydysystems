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
    probation_days      VARCHAR(200),
    retirement_age      VARCHAR(200),
    -- Treasury
    main_bank           VARCHAR(150),
    bank_account_primary VARCHAR(100),
    base_currency       VARCHAR(20)     DEFAULT 'ETB',
    fiscal_start        VARCHAR(100),                           -- e.g. 'Hamle 01 (July 08)'
    -- Digital Identity
    website             VARCHAR(255),
    corporate_email      VARCHAR(150),
    corporate_phone     VARCHAR(50),
    telegram     VARCHAR(150),
    whatsapp     VARCHAR(150),
    linkedin     VARCHAR(150), 
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
    name             VARCHAR(150) NOT NULL,
    head_employee_id INT          NULL,                         -- FK → employees (added below via ALTER)
    branch_id        INT          NULL, 
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
    first_name                VARCHAR(100),
    middle_name               VARCHAR(100),
    last_name                 VARCHAR(100) , 
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
    probation_period          VARCHAR(50),                      -- e.g. '60 Days (Standard)'
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
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE 
);


-- ── 2.4  Former Employees (archive on offboarding) ────────────
CREATE TABLE former_employees (
    id                INT           PRIMARY KEY AUTO_INCREMENT,
    original_emp_id   INT           NOT NULL,                   -- reference to employees.id
    employee_code     VARCHAR(20),
    full_name         VARCHAR(200),
    last_department   VARCHAR(150),
    last_job_position VARCHAR(200),
    last_salary       DECIMAL(15,2),
    exit_date         DATE,
    exit_type         ENUM('Resigned','Terminated','Retired','End of Contract','Deceased','Other'),
    exit_reason       TEXT,
    years_of_service     DECIMAL(5,2),
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
    status           ENUM('Uploaded','Missing') DEFAULT 'Uploaded',
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
    name                   VARCHAR(200)  ,
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
    assigned_to_id   INT       NULL,                        -- new custodian
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
    title              VARCHAR(200),
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
    full_name        VARCHAR(200)  ,
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
    month          TINYINT  NOT NULL,                           
    year           SMALLINT NOT NULL,
    recorded_by    INT      NULL,                               -- HR user
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_emp_date (employee_id, attendance_date),     -- one record per employee per day
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES employees(id) ON DELETE SET NULL
);


CREATE TABLE attendance_monthly_summary (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    employee_id     INT NOT NULL,
    att_year        SMALLINT NOT NULL,
    att_month       TINYINT NOT NULL, -- 1-12
    days_present    TINYINT DEFAULT 0,
    days_absent     TINYINT DEFAULT 0,
    days_late       TINYINT DEFAULT 0,
    total_ot_hours  DECIMAL(10,2) DEFAULT 0.00,
    attendance_rate DECIMAL(5,2), -- Percentage
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY uq_summary_period (employee_id, att_year, att_month)
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
    balance_days INT AS (total_days + carried_over_days - used_days) STORED,
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
    filters    LONGTEXT         NULL,                               -- serialized filter config
    created_by INT          NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    -- created_by FK is added after users table below
);


-- ── 11.2  System Users ────────────────────────────────────────

-- Track failed login attempts to prevent brute force
CREATE TABLE login_attempts (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    ip_address      VARCHAR(45) NOT NULL,
    device_id       VARCHAR(64) NOT NULL, -- The unique cookie ID
    login_identity  VARCHAR(150) NOT NULL, 
    attempted_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_device (device_id, login_identity),
    INDEX idx_ip (ip_address)
);
-- Store hashed tokens for "Remember Me" sessions
CREATE TABLE remember_tokens (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    token_hash  CHAR(64) NOT NULL, -- SHA-256 hash
    expires_at  DATETIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── 11.3  Roles (standard named roles) ───────────────────────
CREATE TABLE roles (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) UNIQUE NOT NULL, -- This MUST be unique
    description TEXT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- 1. Create the Modules Table with Hierarchy support
CREATE TABLE modules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    module_key  VARCHAR(60) UNIQUE NOT NULL, -- e.g. 'm-org' (Category) or 'company-profile' (Sub)
    name        VARCHAR(100) NOT NULL,
    parent_id   INT DEFAULT NULL,             -- NULL means this is a Sidebar Category (Group)
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES modules(id) ON DELETE CASCADE
);
-- ── 11.4  Role Permissions (module-level access per role) ────
CREATE TABLE role_permissions (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    role_id     INT NOT NULL,
    module_id   INT NOT NULL,
    can_access  BOOLEAN DEFAULT TRUE,        -- Single toggle instead of 4 checkboxes
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_module (role_id, module_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- ── 11.5  Individual User Permission Overrides ────────────────
--          Used when "Individual Roles" mode is selected in the UI
CREATE TABLE user_permission_overrides (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    module_id   INT NOT NULL,
    can_access  BOOLEAN DEFAULT TRUE,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_module (user_id, module_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    username      VARCHAR(100) UNIQUE,
    employee_id   INT          NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, 
    role_id       INT          NOT NULL,  
    department_id INT          NULL,
    status        ENUM('Active','Inactive') DEFAULT 'Active',
    last_login    TIMESTAMP    NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT, 
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Back-fill created_by FK on report_templates
ALTER TABLE report_templates
    ADD CONSTRAINT fk_report_user
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

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
CREATE INDEX idx_emp_status     ON employees(status);
CREATE INDEX idx_emp_hire_date  ON employees(hire_date);

-- Attendance
CREATE INDEX idx_att_month_year ON attendance(year, month); 

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

-- 1. Fast searching for employees by name (extremely common)
CREATE INDEX idx_emp_names ON employees(last_name, first_name);

-- 2. Fast searching for candidates by name/email in recruitment
CREATE INDEX idx_candidate_search ON candidates(full_name, email);

-- 3. Optimization for document "Missing/Uploaded" status checks
CREATE INDEX idx_doc_search ON employee_documents(employee_id, status, document_type_id);

-- 4. Optimization for Attendance Reports
-- We need to quickly find all 'Absent' or 'Late' records for a specific month
CREATE INDEX idx_att_stats ON attendance(status, year, month);

-- 5. Optimization for Leave requests (finding overlaps or pending items)
-- This speeds up the "Can this person take leave?" check
CREATE INDEX idx_leave_overlap ON leave_requests(employee_id, status, from_date, to_date);
-- =============================================================
-- PERFORMANCE INDEXES
-- Added to optimize JOIN queries and filtered lookups.
-- Run these after creating all tables.
-- =============================================================

-- Indexes on employees table
-- The employee list query JOINs on these four foreign key columns.
-- Without these, every JOIN does a full table scan.
CREATE INDEX IF NOT EXISTS idx_emp_department_id
    ON employees (department_id);

CREATE INDEX IF NOT EXISTS idx_emp_job_position_id
    ON employees (job_position_id);

CREATE INDEX IF NOT EXISTS idx_emp_branch_id
    ON employees (branch_id);

CREATE INDEX IF NOT EXISTS idx_emp_employment_type_id
    ON employees (employment_type_id);

-- Status filter — used in WHERE e.status = "Active"
CREATE INDEX IF NOT EXISTS idx_emp_status
    ON employees (status);

-- Search by name — used in WHERE first_name LIKE ? OR last_name LIKE ?
CREATE INDEX IF NOT EXISTS idx_emp_name
    ON employees (first_name, last_name);

-- Hire date — used in probation tracker and contract renewal queries
CREATE INDEX IF NOT EXISTS idx_emp_hire_date
    ON employees (hire_date);

-- Date of birth — used in retirement planner
CREATE INDEX IF NOT EXISTS idx_emp_dob
    ON employees (date_of_birth);

-- Index on users table
-- The employee query JOINs users ON e.id = u.employee_id
CREATE INDEX IF NOT EXISTS idx_users_employee_id
    ON users (employee_id);

-- Indexes on attendance_records table
-- Attendance is queried heavily by date and by employee
CREATE INDEX IF NOT EXISTS idx_att_employee_id
    ON attendance_records (employee_id);

CREATE INDEX IF NOT EXISTS idx_att_date
    ON attendance_records (date);

CREATE INDEX IF NOT EXISTS idx_att_emp_date
    ON attendance_records (employee_id, date);

-- Indexes on leave_requests table
CREATE INDEX IF NOT EXISTS idx_leave_employee_id
    ON leave_requests (employee_id);

CREATE INDEX IF NOT EXISTS idx_leave_status
    ON leave_requests (status);

CREATE INDEX IF NOT EXISTS idx_leave_type_id
    ON leave_requests (leave_type_id);

-- Indexes on audit_logs table
-- The dashboard query fetches the last 5 logs ORDER BY logged_at DESC
CREATE INDEX IF NOT EXISTS idx_audit_logged_at
    ON audit_logs (logged_at);

CREATE INDEX IF NOT EXISTS idx_audit_user
    ON audit_logs (user_name);

CREATE INDEX IF NOT EXISTS idx_audit_module
    ON audit_logs (module);

-- Indexes on medical_claims table
CREATE INDEX IF NOT EXISTS idx_claims_employee_id
    ON medical_claims (employee_id);

CREATE INDEX IF NOT EXISTS idx_claims_status
    ON medical_claims (status);

-- Indexes on overtime_requests table
CREATE INDEX IF NOT EXISTS idx_overtime_employee_id
    ON overtime_requests (employee_id);

-- Indexes on performance_reviews table
CREATE INDEX IF NOT EXISTS idx_perf_employee_id
    ON performance_reviews (employee_id);

-- Indexes on disciplinary_actions table
CREATE INDEX IF NOT EXISTS idx_disc_employee_id
    ON disciplinary_actions (employee_id);

-- Indexes on job_applications / candidates table
CREATE INDEX IF NOT EXISTS idx_cand_vacancy_id
    ON candidates (vacancy_id);

CREATE INDEX IF NOT EXISTS idx_cand_status
    ON candidates (status);
   
CREATE INDEX IF NOT EXISTS idx_emp_dept_id
    ON employees (department_id);

CREATE INDEX IF NOT EXISTS idx_emp_position_id
    ON employees (job_position_id);

CREATE INDEX IF NOT EXISTS idx_emp_branch_id
    ON employees (branch_id);

CREATE INDEX IF NOT EXISTS idx_emp_type_id
    ON employees (employment_type_id);

CREATE INDEX IF NOT EXISTS idx_emp_status
    ON employees (status);

CREATE INDEX IF NOT EXISTS idx_emp_name
    ON employees (first_name, last_name);

CREATE INDEX IF NOT EXISTS idx_emp_hire_date
    ON employees (hire_date);

CREATE INDEX IF NOT EXISTS idx_emp_contract_end
    ON employees (contract_end_date);

-- users table: joined to employees on every employee list query
CREATE INDEX IF NOT EXISTS idx_users_employee_id
    ON users (employee_id);

-- attendance table: queried by date and by employee constantly
CREATE INDEX IF NOT EXISTS idx_att_employee_id
    ON attendance (employee_id);

CREATE INDEX IF NOT EXISTS idx_att_date
    ON attendance (attendance_date);

CREATE INDEX IF NOT EXISTS idx_att_emp_date
    ON attendance (employee_id, attendance_date);

-- leave_requests table
CREATE INDEX IF NOT EXISTS idx_leave_employee_id
    ON leave_requests (employee_id);

CREATE INDEX IF NOT EXISTS idx_leave_status
    ON leave_requests (status);

-- audit_logs table: dashboard fetches last 5 sorted by date
CREATE INDEX IF NOT EXISTS idx_audit_logged_at
    ON audit_logs (logged_at);

-- probation_records table
CREATE INDEX IF NOT EXISTS idx_prob_employee_id
    ON probation_records (employee_id);

CREATE INDEX IF NOT EXISTS idx_prob_end_date
    ON probation_records (end_date);

-- employee_contracts table
CREATE INDEX IF NOT EXISTS idx_contract_employee_id
    ON employee_contracts (employee_id);

-- assets table
CREATE INDEX IF NOT EXISTS idx_asset_custodian
    ON assets (current_custodian_id);

-- transfers and promotions tables
CREATE INDEX IF NOT EXISTS idx_transfer_employee_id
    ON transfers (employee_id);

CREATE INDEX IF NOT EXISTS idx_promotion_employee_id
    ON promotions (employee_id);


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
-- ('Study/Exam Leave',   5,   0, 'Partial', TRUE);

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

-- Standard Roles (matches the roles-permissions UI)
INSERT INTO roles (name, description) VALUES
('Super Admin',       'Full system authority — all modules unrestricted'),
('HRM User',          'Standard HR operations across all modules') ;
INSERT INTO company_profile (
    legal_name, 
    trading_name, 
    ceo_name, 
    head_office, 
    entity_type, 
    establishment_date, 
    registration_no, 
    tin, 
    vat_reg_number, 
    trade_license_no,
    work_week_desc, 
    probation_days, 
    retirement_age,
    main_bank, 
    bank_account_primary, 
    base_currency, 
    fiscal_start,
    website, 
    corporate_email, 
    corporate_phone, 
    telegram, 
    whatsapp, 
    linkedin
) VALUES (
    'YDY systems PLC',                -- legal_name
    'YDY',                       -- trading_name
    'Abebe Kebede',                             -- ceo_name
    'Bole Sub-city, Woreda 03, Addis Ababa',    -- head_office
    'Private Limited Company',                  -- entity_type
    '2015-06-12',                               -- establishment_date
    'MT/AA/1/0012345/2007',                     -- registration_no
    '0043829104',                               -- tin
    '8829103347',                               -- vat_reg_number
    'TRADE/FED/778/2016',                       -- trade_license_no
    'Mon-Fri (40 hrs) + Sat Half-day',          -- work_week_desc
    '60 Days',                                  -- probation_days
    '60 Years',                                 -- retirement_age
    'Commercial Bank of Ethiopia (CBE)',        -- main_bank
    '1000123456789',                            -- bank_account_primary
    'ETB',                                      -- base_currency
    'Hamle 01 (July 08)',                       -- fiscal_start
    'ydy.com',                                  -- website
    'info@ydy-innovations.com',               -- corporate_email
    '+251 11 661 2345',                         -- corporate_phone
    '@ydy',                          -- telegram
    '+251 91 122 3344',                         -- whatsapp
    'linkedin.com/company/ydy'    -- linkedin
);


 
-- Views
CREATE OR REPLACE VIEW v_employee_master AS
SELECT 
    e.id AS internal_id,
    e.employee_id,
    CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) AS full_name,
    e.first_name, 
    e.last_name, 
    u.username,             -- Pulled from users table instead of employees
    e.personal_email, 
    e.personal_phone,
    e.gender, 
    e.date_of_birth, 
    e.hire_date, 
    e.status,
    d.name AS department_name,
    jp.title AS position_title,
    b.name AS branch_name,
    et.name AS employment_type,
    CONCAT(mgr.first_name, ' ', mgr.last_name) AS reports_to_name,
    e.gross_salary,
    e.tin,
    e.bank_name,
    e.bank_account,
    TIMESTAMPDIFF(YEAR, e.date_of_birth, CURDATE()) AS current_age,
    TIMESTAMPDIFF(YEAR, e.hire_date, CURDATE()) AS years_of_service
FROM employees e
LEFT JOIN users u ON e.id = u.employee_id              -- Added this join 
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN job_positions jp ON e.job_position_id = jp.id
LEFT JOIN branches b ON e.branch_id = b.id
LEFT JOIN employment_types et ON e.employment_type_id = et.id
LEFT JOIN employees mgr ON e.reports_to_id = mgr.id;

CREATE OR REPLACE VIEW v_employee_compliance_status AS
SELECT 
    e.id AS employee_id,
    e.employee_id AS emp_code,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    (SELECT COUNT(*) FROM document_types WHERE is_mandatory = 1) AS total_required,
    COUNT(ed.id) AS total_uploaded,
    ROUND((COUNT(ed.id) / (SELECT COUNT(*) FROM document_types WHERE is_mandatory = 1)) * 100, 2) AS compliance_percentage
FROM employees e
LEFT JOIN employee_documents ed ON e.id = ed.employee_id
GROUP BY e.id;


CREATE OR REPLACE VIEW v_leave_balances AS
SELECT 
    le.employee_id,
    e.employee_id AS emp_code,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    lt.name AS leave_type,
    le.fiscal_year,
    le.total_days,
    le.carried_over_days,
    le.used_days,
    (le.total_days + le.carried_over_days - le.used_days) AS remaining_balance
FROM leave_entitlements le
JOIN employees e ON le.employee_id = e.id
JOIN leave_types lt ON le.leave_type_id = lt.id;


CREATE OR REPLACE VIEW v_asset_registry AS
SELECT 
    a.id,
    a.asset_code,
    a.name AS asset_name,
    ac.name AS category_name,
    a.serial_number,
    a.asset_value,
    CONCAT(e.first_name, ' ', e.last_name) AS current_custodian,
    b.name AS location_name,
    a.warranty_expiry,
    a.status,
    DATEDIFF(a.warranty_expiry, CURDATE()) AS days_until_warranty_end
FROM assets a
LEFT JOIN asset_categories ac ON a.category_id = ac.id
LEFT JOIN employees e ON a.current_custodian_id = e.id
LEFT JOIN branches b ON a.location_branch_id = b.id;

 
CREATE OR REPLACE VIEW v_dept_structure_stats AS
SELECT 
    d.id AS dept_id,
    d.name AS department_name,
    CONCAT_WS(' ', e.first_name, e.last_name) AS head_of_dept,
    (SELECT COUNT(*) FROM employees WHERE department_id = d.id AND status = 'Active') AS active_headcount,
    (SELECT COUNT(*) FROM job_positions WHERE department_id = d.id) AS total_positions
FROM departments d
LEFT JOIN employees e ON d.head_employee_id = e.id;


CREATE OR REPLACE VIEW v_retirement_forecast AS
SELECT 
    employee_id,
    full_name,
    department_name,
    current_age,
    years_of_service,
    DATE_ADD(date_of_birth, INTERVAL 60 YEAR) AS scheduled_retirement_date,
    DATEDIFF(DATE_ADD(date_of_birth, INTERVAL 60 YEAR), CURDATE()) AS days_until_retirement
FROM v_employee_master
WHERE current_age >= 55; -- Show anyone within 5 years of retirement

CREATE OR REPLACE VIEW v_vacancy_pipeline AS
SELECT 
    jv.id AS vacancy_id,
    jv.title AS job_title,
    d.name AS department,
    jv.deadline_date,
    jv.status,
    COUNT(c.id) AS total_applicants,
    SUM(CASE WHEN c.current_stage = 'Interview' THEN 1 ELSE 0 END) AS in_interview,
    SUM(CASE WHEN c.current_stage = 'Offer' THEN 1 ELSE 0 END) AS offers_made
FROM job_vacancies jv
LEFT JOIN departments d ON jv.department_id = d.id
LEFT JOIN candidates c ON jv.id = c.vacancy_id
GROUP BY jv.id;

CREATE OR REPLACE VIEW v_exit_clearance_master AS
SELECT 
    s.id AS separation_id,
    e.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    s.separation_type,
    s.last_working_day,
    ec.it_cleared,
    ec.finance_cleared,
    ec.hr_cleared,
    ec.assets_cleared,
    ec.overall_status AS clearance_status,
    s.status AS separation_status
FROM separations s
JOIN employees e ON s.employee_id = e.id
LEFT JOIN exit_clearances ec ON s.id = ec.separation_id;

CREATE OR REPLACE VIEW v_attendance_monthly_ledger AS
SELECT 
    a.employee_id,
    a.year,
    a.month,
    e.first_name,
    e.last_name,
    d.name AS department_name,
    COUNT(CASE WHEN a.status = 'P' THEN 1 END) AS days_present,
    COUNT(CASE WHEN a.status = 'A' THEN 1 END) AS days_absent,
    COUNT(CASE WHEN a.status = 'H' THEN 1 END) AS half_days_sat,
    COUNT(CASE WHEN a.status = 'L' THEN 1 END) AS days_on_leave,
    SUM(a.overtime_hours) AS total_ot_hours,
    COUNT(CASE WHEN a.is_late = 1 THEN 1 END) AS total_late_arrivals
FROM attendance a
JOIN employees e ON a.employee_id = e.id
LEFT JOIN departments d ON e.department_id = d.id
GROUP BY a.employee_id, a.year, a.month;

CREATE OR REPLACE VIEW v_contract_renewal_alerts AS
SELECT 
    e.id AS employee_id,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    et.name AS employment_type,
    e.hire_date,
    e.contract_end_date,
    DATEDIFF(e.contract_end_date, CURDATE()) AS days_to_expiry,
    CASE 
        WHEN DATEDIFF(e.contract_end_date, CURDATE()) < 0 THEN 'Expired'
        WHEN DATEDIFF(e.contract_end_date, CURDATE()) <= 15 THEN 'Critical'
        WHEN DATEDIFF(e.contract_end_date, CURDATE()) <= 30 THEN 'Warning'
        ELSE 'Safe'
    END AS renewal_urgency_status
FROM employees e
JOIN employment_types et ON e.employment_type_id = et.id
WHERE e.contract_end_date IS NOT NULL AND e.status = 'Active';

CREATE OR REPLACE VIEW v_dept_financial_summary AS
SELECT 
    d.id AS department_id,
    d.name AS department_name,
    COUNT(e.id) AS active_headcount,
    SUM(e.gross_salary) AS total_monthly_payroll,
    AVG(e.gross_salary) AS average_salary,
    MIN(e.gross_salary) AS min_salary,
    MAX(e.gross_salary) AS max_salary
FROM departments d
LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
GROUP BY d.id;

CREATE OR REPLACE VIEW v_employee_career_path AS
SELECT 
    employee_id,
    'Promotion' AS movement_type,
    effective_date,
    CONCAT('Moved to ', (SELECT title FROM job_positions WHERE id = to_position_id)) AS details,
    status
FROM promotions
UNION ALL
SELECT 
    employee_id,
    'Transfer' AS movement_type,
    effective_date,
    CONCAT('Transferred to ', (SELECT name FROM departments WHERE id = to_department_id)) AS details,
    status
FROM transfers
ORDER BY effective_date DESC;

CREATE OR REPLACE VIEW v_probation_risk_assessment AS
SELECT 
    pr.employee_id,
    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
    pr.end_date AS probation_end,
    DATEDIFF(pr.end_date, CURDATE()) AS days_left,
    (SELECT COUNT(*) FROM disciplinary_actions WHERE employee_id = pr.employee_id) AS disciplinary_count,
    (SELECT AVG(overall_score) FROM performance_reviews WHERE employee_id = pr.employee_id) AS avg_review_score
FROM probation_records pr
JOIN employees e ON pr.employee_id = e.id
WHERE pr.status = 'Active';

CREATE OR REPLACE VIEW v_critical_audit_logs AS
SELECT 
    logged_at,
    user_name,
    action,
    module,
    record_ref,
    ip_address
FROM audit_logs
WHERE action IN ('DELETE', 'UPDATE') 
   OR module IN ('Settings', 'Roles & Permissions', 'User Management')
ORDER BY logged_at DESC;

CREATE OR REPLACE VIEW v_dashboard_hero_stats AS
SELECT 
    (SELECT COUNT(*) FROM employees WHERE status = 'Active') AS total_headcount,
    (SELECT COUNT(*) FROM departments WHERE status = 'Active') AS total_departments,
    (SELECT COUNT(*) FROM leave_requests WHERE status = 'Approved' AND CURDATE() BETWEEN from_date AND to_date) AS staff_on_leave,
    (SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending') AS pending_leave_approvals,
    (SELECT COUNT(*) FROM employees WHERE contract_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)) AS critical_expiring_contracts,
    (SELECT COUNT(*) FROM asset_categories) AS total_asset_categories
FROM dual;

    CREATE OR REPLACE VIEW v_vault_missing_documents AS
    SELECT 
        e.id AS employee_id,
        e.employee_id AS emp_code,
        CONCAT(e.first_name, ' ', e.last_name) AS full_name,
        dt.name AS missing_document_name,
        dt.category AS document_category
    FROM employees e
    CROSS JOIN document_types dt
    LEFT JOIN employee_documents ed ON e.id = ed.employee_id AND dt.id = ed.document_type_id
    WHERE dt.is_mandatory = 1 
    AND ed.id IS NULL 
    AND e.status = 'Active';

  CREATE OR REPLACE VIEW v_user_access_resolver AS
    SELECT 
        u.id AS user_id,
        u.username,
        r.name AS role_name,             -- FIX: Get the name from the roles table (r)
        m.module_key,
        m.name AS module_name,
        -- Check User Override first, then Role Permission, default to 0 if neither found
        COALESCE(upo.can_access, rp.can_access, 0) AS final_access_allowed
    FROM users u
    JOIN roles r ON u.role_id = r.id     -- FIX: Join using the actual FK (role_id)
    CROSS JOIN modules m                 -- Checks every user against every module
    LEFT JOIN role_permissions rp 
        ON r.id = rp.role_id AND m.id = rp.module_id
    LEFT JOIN user_permission_overrides upo 
        ON u.id = upo.user_id AND m.id = upo.module_id;

CREATE OR REPLACE VIEW v_analytics_demographics AS
SELECT 
    gender,
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25 THEN '18-24'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
        ELSE '55+' 
    END AS age_group,
    department_id,
    COUNT(*) AS head_count
FROM employees
WHERE status = 'Active'
GROUP BY gender, age_group, department_id;


SET FOREIGN_KEY_CHECKS = 1;  -- Re-enable after all tables are created

-- ============================================================
-- END OF SCHEMA
-- ============================================================
