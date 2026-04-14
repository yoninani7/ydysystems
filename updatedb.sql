-- ============================================================
-- YDY HRM ENTERPRISE DATABASE SCHEMA
-- Optimized for PHP codebase compatibility
-- ============================================================

DROP DATABASE IF EXISTS ydy_hrm;
CREATE DATABASE ydy_hrm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ydy_hrm;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. COMPANY & STRUCTURE
-- ------------------------------------------------------------

CREATE TABLE company_profile (
    id                  INT             PRIMARY KEY AUTO_INCREMENT,
    legal_name          VARCHAR(200)    NOT NULL,
    trading_name        VARCHAR(200),
    ceo_name            VARCHAR(150),
    head_office         VARCHAR(255),
    entity_type         VARCHAR(100),
    establishment_date  DATE,
    registration_no     VARCHAR(100),
    tin                 VARCHAR(50),
    vat_reg_number      VARCHAR(100),
    trade_license_no    VARCHAR(100),
    work_week_desc      VARCHAR(200),
    probation_days      VARCHAR(200),
    retirement_age      VARCHAR(200),
    main_bank           VARCHAR(150),
    bank_account_primary VARCHAR(100),
    base_currency       VARCHAR(20)     DEFAULT 'ETB',
    fiscal_start        VARCHAR(100),
    website             VARCHAR(255),
    corporate_email      VARCHAR(150),
    corporate_phone     VARCHAR(50),
    telegram            VARCHAR(150),
    whatsapp            VARCHAR(150),
    linkedin            VARCHAR(150),
    updated_at          TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE employment_types (
    id          INT             PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,
    description TEXT,
    benefits    ENUM('Yes','No','Partial') DEFAULT 'Yes',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE branches (
    id          INT             PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(150)    NOT NULL,
    city        VARCHAR(100),
    address     TEXT,
    phone       VARCHAR(50),
    email       VARCHAR(150),
    manager_id  INT             NULL,
    status      ENUM('Active','Inactive') DEFAULT 'Active',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP       NULL DEFAULT NULL,  
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    name             VARCHAR(150) NOT NULL,
    head_employee_id INT          NULL,
    branch_id        INT          NULL,
    status           ENUM('Active','Inactive') DEFAULT 'Active',
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    deleted_at       TIMESTAMP    NULL DEFAULT NULL,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

CREATE TABLE job_positions (
    id            INT           PRIMARY KEY AUTO_INCREMENT,
    title         VARCHAR(200)  NOT NULL,
    department_id INT           NULL,
    headcount     INT           DEFAULT 0,
    status        ENUM('Active','Inactive') DEFAULT 'Active',
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    deleted_at    TIMESTAMP     NULL DEFAULT NULL,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 2. EMPLOYEES
-- ------------------------------------------------------------

CREATE TABLE employees (
    id                        INT          PRIMARY KEY AUTO_INCREMENT,
    employee_id               VARCHAR(20)  UNIQUE NOT NULL,
    first_name                VARCHAR(100),
    middle_name               VARCHAR(100),
    last_name                 VARCHAR(100),
    date_of_birth             DATE,
    gender                    ENUM('Male','Female'),
    nationality               VARCHAR(100) DEFAULT 'Ethiopian',
    marital_status            ENUM('Single','Married','Divorced','Widowed'),
    place_of_birth            VARCHAR(150),
    profile_photo             VARCHAR(255),
    personal_phone            VARCHAR(50),
    personal_email            VARCHAR(150),
    permanent_address         TEXT,
    city                      VARCHAR(100),
    postal_code               VARCHAR(20),
    department_id             INT          NULL,
    job_position_id           INT          NULL,
    employment_type_id        INT          NULL,
    branch_id                 INT          NULL,
    hire_date                 DATE,
    contract_end_date         DATE         NULL,
    hours_per_week            INT          NULL,
    probation_period          VARCHAR(50),
    project_name              VARCHAR(200) NULL,
    reports_to_id             INT          NULL,
    gross_salary              DECIMAL(15,2),
    tin                       VARCHAR(50),
    bank_name                 VARCHAR(100),
    bank_account              VARCHAR(100),
    emergency_contact_name    VARCHAR(150),
    emergency_contact_phone   VARCHAR(50),
    emergency_contact_relation VARCHAR(100),
    status                    ENUM('Active','Inactive','On Leave','Resigned','Terminated','Retired') DEFAULT 'Active',
    created_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    deleted_at                TIMESTAMP    NULL DEFAULT NULL,
    updated_at                TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id)      REFERENCES departments(id)      ON DELETE SET NULL,
    FOREIGN KEY (job_position_id)    REFERENCES job_positions(id)    ON DELETE SET NULL,
    FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id)          REFERENCES branches(id)         ON DELETE SET NULL,
    FOREIGN KEY (reports_to_id)      REFERENCES employees(id)        ON DELETE SET NULL
);

-- Back-fill FKs
ALTER TABLE branches
    ADD CONSTRAINT fk_branch_manager
    FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;

ALTER TABLE departments
    ADD CONSTRAINT fk_dept_head
    FOREIGN KEY (head_employee_id) REFERENCES employees(id) ON DELETE SET NULL;

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

CREATE TABLE former_employees (
    id                INT           PRIMARY KEY AUTO_INCREMENT,
    original_emp_id   INT           NOT NULL,
    employee_code     VARCHAR(20),
    full_name         VARCHAR(200),
    last_department   VARCHAR(150),
    last_job_position VARCHAR(200),
    last_salary       DECIMAL(15,2),
    exit_date         DATE,
    exit_type         ENUM('Resigned','Terminated','Retired','End of Contract','Deceased','Other'),
    exit_reason       TEXT,
    years_of_service  DECIMAL(5,2),
    rehire_eligible   ENUM('Yes','No') DEFAULT 'Yes',
    notes             TEXT,
    archived_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE document_types (
    id           INT          PRIMARY KEY AUTO_INCREMENT,
    code         VARCHAR(50)  UNIQUE NOT NULL,
    name         VARCHAR(200) NOT NULL,
    category     VARCHAR(100),
    is_mandatory BOOLEAN      DEFAULT TRUE,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

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

CREATE TABLE asset_categories (
    id         INT          PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE assets (
    id                     INT           PRIMARY KEY AUTO_INCREMENT,
    asset_code             VARCHAR(50)   UNIQUE NOT NULL,
    name                   VARCHAR(200),
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

CREATE TABLE asset_assignments (
    id               INT       PRIMARY KEY AUTO_INCREMENT,
    asset_id         INT       NOT NULL,
    assigned_from_id INT       NULL,
    assigned_to_id   INT       NULL,
    assigned_by      INT       NULL,
    assigned_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returned_at      TIMESTAMP NULL,
    notes            TEXT,
    FOREIGN KEY (asset_id)         REFERENCES assets(id)    ON DELETE CASCADE,
    FOREIGN KEY (assigned_from_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to_id)   REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by)      REFERENCES employees(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 3. TALENT ACQUISITION
-- ------------------------------------------------------------

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
    created_by         INT          NULL,
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id)      REFERENCES departments(id)      ON DELETE SET NULL,
    FOREIGN KEY (branch_id)          REFERENCES branches(id)         ON DELETE SET NULL,
    FOREIGN KEY (employment_type_id) REFERENCES employment_types(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)         REFERENCES employees(id)        ON DELETE SET NULL
);

CREATE TABLE candidates (
    id            INT          PRIMARY KEY AUTO_INCREMENT,
    vacancy_id    INT          NULL,
    full_name     VARCHAR(200) NOT NULL,
    email         VARCHAR(150),
    phone         VARCHAR(50),
    cv_path       VARCHAR(500),
    applied_date  DATE,
    current_stage ENUM('Applied','Screening','Interview','Assessment','Offer','Hired','Rejected') DEFAULT 'Applied',
    notes         TEXT,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vacancy_id) REFERENCES job_vacancies(id) ON DELETE SET NULL
);

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

CREATE TABLE internships (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    intern_code      VARCHAR(30)  UNIQUE,
    full_name        VARCHAR(200),
    institution      VARCHAR(200),
    department_id    INT          NULL,
    mentor_id        INT          NULL,
    start_date       DATE,
    end_date         DATE,
    evaluation_score DECIMAL(5,2) NULL,
    status           ENUM('Active','Completed','Terminated') DEFAULT 'Active',
    potential_hire   BOOLEAN      DEFAULT FALSE,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    deleted_at         TIMESTAMP    NULL DEFAULT NULL,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (mentor_id)     REFERENCES employees(id)   ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 4. EMPLOYEE MOVEMENT
-- ------------------------------------------------------------

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

-- ------------------------------------------------------------
-- 5. ATTENDANCE
-- ------------------------------------------------------------

CREATE TABLE attendance (
    id              INT            PRIMARY KEY AUTO_INCREMENT,
    employee_id     INT            NOT NULL,
    attendance_date DATE           NOT NULL,
    status          ENUM('P','H','A','L','O') NOT NULL DEFAULT 'P',
    check_in        TIME           NULL,
    check_out       TIME           NULL,
    hours_worked    DECIMAL(4,2)   NULL,
    overtime_hours  DECIMAL(4,2)   DEFAULT 0.00,
    is_late         BOOLEAN        DEFAULT FALSE,
    month           TINYINT        NOT NULL,
    year            SMALLINT       NOT NULL,
    recorded_by     INT            NULL,
    created_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_emp_date (employee_id, attendance_date),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES employees(id) ON DELETE SET NULL
); 

-- ------------------------------------------------------------
-- 6. LEAVE MANAGEMENT
-- ------------------------------------------------------------

CREATE TABLE leave_types (
    id               INT          PRIMARY KEY AUTO_INCREMENT,
    name             VARCHAR(150) NOT NULL,
    days_per_year    INT          NULL,
    carryover_days   INT          DEFAULT 0,
    is_paid          ENUM('Yes','No','Partial') DEFAULT 'Yes',
    requires_approval BOOLEAN     DEFAULT TRUE,
    description      TEXT,
    deleted_at       TIMESTAMP    NULL DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE leave_entitlements (
    id                 INT      PRIMARY KEY AUTO_INCREMENT,
    employee_id        INT      NOT NULL,
    leave_type_id      INT      NOT NULL,
    fiscal_year        SMALLINT NOT NULL,
    total_days         INT      DEFAULT 0,
    used_days          INT      DEFAULT 0,
    carried_over_days  INT      DEFAULT 0,
    balance_days       INT      AS (total_days + carried_over_days - used_days) STORED,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_emp_leave_year (employee_id, leave_type_id, fiscal_year),
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE CASCADE,
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
);

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

-- ------------------------------------------------------------
-- 7. BENEFITS
-- ------------------------------------------------------------

CREATE TABLE medical_claims (
    id             INT         PRIMARY KEY AUTO_INCREMENT,
    claim_code     VARCHAR(30) UNIQUE,
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

-- ------------------------------------------------------------
-- 8. COMPLIANCE & EXIT
-- ------------------------------------------------------------

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

-- ------------------------------------------------------------
-- 9. TRAINING & DEVELOPMENT
-- ------------------------------------------------------------

CREATE TABLE training_needs (
    id                   INT          PRIMARY KEY AUTO_INCREMENT,
    department_id        INT          NULL,
    skill_gap            VARCHAR(200) NOT NULL,
    priority             ENUM('High','Medium','Low') DEFAULT 'Medium',
    affected_employees   INT          DEFAULT 0,
    proposed_training    VARCHAR(200),
    status               ENUM('Pending','Approved','Ongoing') DEFAULT 'Pending',
    created_at           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

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

CREATE TABLE training_enrollments (
    id          INT       PRIMARY KEY AUTO_INCREMENT,
    training_id INT       NOT NULL,
    employee_id INT       NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_training_emp (training_id, employee_id),
    FOREIGN KEY (training_id) REFERENCES training_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id)          ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 10. PERFORMANCE
-- ------------------------------------------------------------

CREATE TABLE performance_reviews (
    id            INT         PRIMARY KEY AUTO_INCREMENT,
    employee_id   INT         NOT NULL,
    reviewer_id   INT         NULL,
    review_period VARCHAR(50),
    overall_score DECIMAL(4,2),
    rating        ENUM('Exceptional','Exceeds','Meets','Below') DEFAULT 'Meets',
    comments      TEXT,
    status        ENUM('Pending','Submitted','Acknowledged') DEFAULT 'Pending',
    created_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES employees(id) ON DELETE SET NULL
);

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

-- ------------------------------------------------------------
-- 11. ANALYTICS & SYSTEM
-- ------------------------------------------------------------

CREATE TABLE roles (
    id          INT          PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE modules (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    module_key  VARCHAR(60) UNIQUE NOT NULL,
    name        VARCHAR(100) NOT NULL,
    parent_id   INT DEFAULT NULL,
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE role_permissions (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    role_id     INT NOT NULL,
    module_id   INT NOT NULL,
    can_access  BOOLEAN DEFAULT TRUE,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_module (role_id, module_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
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
    deleted_at    TIMESTAMP    NULL DEFAULT NULL,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id)   REFERENCES employees(id)   ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

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
 

CREATE TABLE login_attempts (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    ip_address      VARCHAR(45) NOT NULL,
    device_id       VARCHAR(64) NOT NULL,
    login_identity  VARCHAR(150) NOT NULL,
    attempted_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_device (device_id, login_identity),
    INDEX idx_ip (ip_address)
);

CREATE TABLE remember_tokens (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    user_id     INT NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE audit_logs (
    id          INT           PRIMARY KEY AUTO_INCREMENT,
    user_id     INT           NULL,
    user_name   VARCHAR(200),
    action      ENUM('CREATE','UPDATE','DELETE','LOGIN','APPROVE','EXPORT','VIEW') NOT NULL,
    module      VARCHAR(100),
    record_ref  VARCHAR(100),
    old_value   JSON          NULL,
    new_value   JSON          NULL,
    ip_address  VARCHAR(45),
    logged_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- INDEXES (Performance Optimization)
-- ============================================================

-- Employees
CREATE INDEX idx_emp_status         ON employees(status);
CREATE INDEX idx_emp_hire_date      ON employees(hire_date);
CREATE INDEX idx_emp_names          ON employees(last_name, first_name);
CREATE INDEX idx_emp_department_id  ON employees(department_id);
CREATE INDEX idx_emp_job_position_id ON employees(job_position_id);
CREATE INDEX idx_emp_branch_id      ON employees(branch_id);
CREATE INDEX idx_emp_employment_type_id ON employees(employment_type_id);
CREATE INDEX idx_emp_contract_end   ON employees(contract_end_date);
CREATE INDEX idx_emp_dob            ON employees(date_of_birth);

-- Attendance
CREATE INDEX idx_att_month_year     ON attendance(year, month);
CREATE INDEX idx_att_employee_id    ON attendance(employee_id);
CREATE INDEX idx_att_date           ON attendance(attendance_date);
CREATE INDEX idx_att_stats          ON attendance(status, year, month);

-- Leave
CREATE INDEX idx_lr_employee        ON leave_requests(employee_id);
CREATE INDEX idx_lr_status          ON leave_requests(status);
CREATE INDEX idx_lr_dates           ON leave_requests(from_date, to_date);
CREATE INDEX idx_leave_overlap      ON leave_requests(employee_id, status, from_date, to_date);

-- Contracts
CREATE INDEX idx_con_emp            ON employee_contracts(employee_id);
CREATE INDEX idx_con_end            ON employee_contracts(end_date);

-- Assets
CREATE INDEX idx_ast_custodian      ON assets(current_custodian_id);
CREATE INDEX idx_ast_status         ON assets(status);

-- Audit Logs
CREATE INDEX idx_audit_user         ON audit_logs(user_id);
CREATE INDEX idx_audit_module       ON audit_logs(module);
CREATE INDEX idx_audit_logged       ON audit_logs(logged_at);

-- Documents
CREATE INDEX idx_doc_employee       ON employee_documents(employee_id);
CREATE INDEX idx_doc_status         ON employee_documents(status);
CREATE INDEX idx_doc_search         ON employee_documents(employee_id, status, document_type_id);

-- Candidates
CREATE INDEX idx_candidate_search   ON candidates(full_name, email);
CREATE INDEX idx_cand_vacancy_id    ON candidates(vacancy_id);

-- Users
CREATE INDEX idx_users_employee_id  ON users(employee_id);

-- Probation
CREATE INDEX idx_prob_employee_id   ON probation_records(employee_id);
CREATE INDEX idx_prob_end_date      ON probation_records(end_date);

-- Transfers & Promotions
CREATE INDEX idx_transfer_employee_id ON transfers(employee_id);
CREATE INDEX idx_promotion_employee_id ON promotions(employee_id);

-- Medical Claims & Overtime
CREATE INDEX idx_claims_employee_id ON medical_claims(employee_id);
CREATE INDEX idx_claims_status      ON medical_claims(status);
CREATE INDEX idx_overtime_employee_id ON overtime_requests(employee_id);

-- Performance
CREATE INDEX idx_perf_employee_id   ON performance_reviews(employee_id);
CREATE INDEX idx_disc_employee_id   ON disciplinary_actions(employee_id);

-- Vacancies
CREATE INDEX idx_vacancies_status_deadline ON job_vacancies(status, deadline_date);

CREATE INDEX idx_emp_deleted_at ON employees(deleted_at);
CREATE INDEX idx_dept_deleted_at ON departments(deleted_at);
CREATE INDEX idx_user_deleted_at ON users(deleted_at); 
-- ============================================================
-- VIEWS (Required by PHP fetch scripts)
-- ============================================================

CREATE OR REPLACE VIEW v_employee_master AS
SELECT 
    e.id AS internal_id,
    e.employee_id,
    CONCAT(e.first_name, ' ', COALESCE(e.middle_name, ''), ' ', e.last_name) AS full_name,
    e.first_name, 
    e.last_name, 
    u.username,
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
LEFT JOIN users u ON e.id = u.employee_id
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN job_positions jp ON e.job_position_id = jp.id
LEFT JOIN branches b ON e.branch_id = b.id
LEFT JOIN employment_types et ON e.employment_type_id = et.id
LEFT JOIN employees mgr ON e.reports_to_id = mgr.id
WHERE e.deleted_at IS NULL;

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
WHERE e.deleted_at IS NULL
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
JOIN leave_types lt ON le.leave_type_id = lt.id
WHERE e.deleted_at IS NULL;

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
LEFT JOIN branches b ON a.location_branch_id = b.id
WHERE e.deleted_at IS NULL;

CREATE OR REPLACE VIEW v_dept_structure_stats AS
SELECT 
    d.id AS dept_id,
    d.name AS department_name,
    CONCAT_WS(' ', e.first_name, e.last_name) AS head_of_dept,
    (SELECT COUNT(*) FROM employees WHERE department_id = d.id AND status = 'Active') AS active_headcount,
    (SELECT COUNT(*) FROM job_positions WHERE department_id = d.id) AS total_positions
FROM departments d
LEFT JOIN employees e ON d.head_employee_id = e.id
WHERE e.deleted_at IS NULL;

CREATE OR REPLACE VIEW v_retirement_forecast AS
SELECT 
    employee_id,
    full_name,
    department_name,
    current_age,
    years_of_service,
    DATE_ADD(date_of_birth, INTERVAL 60 YEAR) AS scheduled_retirement_date,
    DATEDIFF(DATE_ADD(date_of_birth, INTERVAL 60 YEAR), CURDATE()) AS days_until_retirement
FROM v_employee_master;

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
LEFT JOIN exit_clearances ec ON s.id = ec.separation_id
WHERE e.deleted_at IS NULL;

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
WHERE e.deleted_at IS NULL
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
WHERE e.contract_end_date IS NOT NULL AND e.status = 'Active' AND e.deleted_at IS NULL;

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
WHERE e.deleted_at IS NULL
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
WHERE pr.status = 'Active' AND e.deleted_at IS NULL;

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
FROM dual ;

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
AND e.status = 'Active'
AND e.deleted_at IS NULL;

CREATE OR REPLACE VIEW v_user_access_resolver AS
SELECT 
    u.id AS user_id,
    u.username,
    r.name AS role_name,
    m.module_key,
    m.name AS module_name,
    COALESCE(upo.can_access, rp.can_access, 0) AS final_access_allowed
FROM users u
JOIN roles r ON u.role_id = r.id
CROSS JOIN modules m
LEFT JOIN role_permissions rp ON r.id = rp.role_id AND m.id = rp.module_id
LEFT JOIN user_permission_overrides upo ON u.id = upo.user_id AND m.id = upo.module_id ;

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
WHERE status = 'Active' AND deleted_at IS NULL
GROUP BY gender, age_group, department_id;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Company Profile
INSERT INTO company_profile (
    legal_name, trading_name, ceo_name, head_office, entity_type, establishment_date,
    registration_no, tin, vat_reg_number, trade_license_no, work_week_desc,
    probation_days, retirement_age, main_bank, bank_account_primary, base_currency,
    fiscal_start, website, corporate_email, corporate_phone, telegram, whatsapp, linkedin
) VALUES (
    'YDY systems PLC', 'YDY', 'Abebe Kebede', 'Bole Sub-city, Woreda 03, Addis Ababa',
    'Private Limited Company', '2015-06-12', 'MT/AA/1/0012345/2007', '0043829104',
    '8829103347', 'TRADE/FED/778/2016', 'Mon-Fri (40 hrs) + Sat Half-day',
    '60 Days', '60 Years', 'Commercial Bank of Ethiopia (CBE)', '1000123456789', 'ETB',
    'Hamle 01 (July 08)', 'ydy.com', 'info@ydy-innovations.com', '+251 11 661 2345',
    '@ydy', '+251 91 122 3344', 'linkedin.com/company/ydy'
);

-- Roles
INSERT INTO roles (name, description) VALUES
('Super Admin', 'Full system authority — all modules unrestricted'),
('HRM User', 'Standard HR operations across all modules'),
('Department Manager', 'Limited to team data and approvals');

-- Employment Types
INSERT INTO employment_types (name, description, benefits) VALUES
('Permanent / Full-Time', 'Regular employee with full benefits', 'Yes'),
('Fixed-Term Contract', 'Time-bound employment agreement', 'Partial'),
('Part-Time', 'Less than 40 hours per week', 'Partial'),
('Internship', 'Student or graduate trainee', 'No'),
('Temporary / Casual', 'Short-term project-based assignment', 'No');

-- Leave Types
INSERT INTO leave_types (name, days_per_year, carryover_days, is_paid, requires_approval, description) VALUES
('Annual Leave', 20, 5, 'Yes', TRUE, 'Standard vacation leave'),
('Sick Leave', 10, 0, 'Yes', FALSE, 'Medical leave with doctor''s note'),
('Maternity Leave', 90, 0, 'Yes', TRUE, 'Full pay for 90 days'),
('Paternity Leave', 14, 0, 'Yes', TRUE, '5 days paid'),
('Bereavement Leave', 5, 0, 'Yes', TRUE, 'For immediate family'),
('Unpaid Leave', NULL, 0, 'No', TRUE, 'Case-by-case approval'),
('Study/Exam Leave', 5, 0, 'Partial', TRUE, 'For academic pursuits');

-- Document Types
INSERT INTO document_types (code, name, category, is_mandatory) VALUES
('contract', 'Signed Employment Contract', 'Legal', TRUE),
('cv', 'Curriculum Vitae (CV)', 'Identity', TRUE),
('academic', 'Academic Credentials', 'Education', TRUE),
('clearance', 'Clearance / Release Letter', 'History', TRUE),
('experience', 'Experience Letters', 'History', FALSE),
('coc', 'Certificate of Competence (COC)', 'Professional', FALSE),
('guarantor', 'Guarantor Form & ID', 'Legal', TRUE),
('nda', 'Confidentiality / NDA Agreement', 'Compliance', TRUE),
('handbook', 'Acknowledgments', 'Compliance', TRUE),
('national_id', 'National ID / Passport Copy', 'Identity', TRUE),
('tin', 'TIN Certification Document', 'Tax', TRUE),
('medical', 'Health & Fitness Clearance', 'Compliance', TRUE);

-- Asset Categories
INSERT INTO asset_categories (name) VALUES
('IT Hardware'), ('Fleet/Vehicles'), ('Office Furniture'), ('Mobile Devices'), ('Software Licenses');

-- Modules (Sidebar Hierarchy)
INSERT INTO modules (module_key, name, parent_id, sort_order) VALUES
('m-org', 'Company & Structure', NULL, 1),
('company-profile', 'Company Profile', 1, 1),
('org-chart', 'Organization Chart', 1, 2),
('departments', 'Departments', 1, 3),
('job-positions', 'Job Positions', 1, 4),
('branch-offices', 'Branch Offices', 1, 5),
('m-emp', 'Employees', NULL, 2),
('employee-directory', 'Employee Profile', 7, 1),
('add-employee', 'Add Employee', 7, 2),
('employment-types', 'Employment Types', 7, 3),
('probation-tracker', 'Probation Tracker', 7, 4),
('contract-renewals', 'Contract Renewals', 7, 5),
('former-employees', 'Former Employees', 7, 6),
('retirement-planner', 'Retirement Planner', 7, 7),
('document-vault', 'Attachment Vault', 7, 8),
('asset-tracking', 'Asset Tracking', 7, 9),
('m-move', 'Employee Movement', NULL, 3),
('Promote/Demote', 'Promote/Demote', 17, 1),
('transfers', 'Department Transfers', 17, 2),
('m-rec', 'Talent Acquisition', NULL, 4),
('job-vacancies', 'Job Vacancies', 20, 1),
('candidates', 'Candidates', 20, 2),
('interview-tracker', 'Interview Tracker', 20, 3),
('internship', 'Internship Management', 20, 4),
('m-att', 'Attendance', NULL, 5),
('attendance', 'Record Attendance', 25, 1),
('daily-attendance', 'Daily Attendance', 25, 2),
('attendance-reports', 'Attendance Reports', 25, 3),
('m-leave', 'Leave Management', NULL, 6),
('leave-types', 'Leave Types', 29, 1),
('leave-requests', 'Leave Requests', 29, 2),
('leave-entitlement', 'Leave Entitlement', 29, 3),
('m-ben', 'Benefits', NULL, 7),
('medical-claims', 'Medical Claims', 33, 1),
('overtime-requests', 'Overtime Requests', 33, 2),
('m-comp', 'Compliance & Exit', NULL, 8),
('disciplinary-actions', 'Disciplinary Actions', 36, 1),
('resignations', 'Resignations', 36, 2),
('termination', 'Separation & Exit', 36, 3),
('exit-clearance', 'Exit Clearance', 36, 4),
('m-train', 'Training & Development', NULL, 9),
('training-needs', 'Training Needs Analysis', 41, 1),
('training-schedule', 'Training Schedule', 41, 2),
('m-perf', 'Performance', NULL, 10),
('performance-reviews', 'Performance Reviews', 44, 1),
('360-feedback', '360° Feedback', 44, 2),
('m-rep', 'Reports & Analytics', NULL, 11),
('hr-analytics', 'HR Analytics', 47, 1),
('custom-reports', 'Custom Reports', 47, 2),
('m-sys', 'System Admin', NULL, 12),
('user-management', 'User Management', 50, 1),
('roles-permissions', 'Roles & Permissions', 50, 2),
('audit-logs', 'Audit Logs', 50, 3);

-- Role Permissions (Super Admin gets everything)
INSERT INTO role_permissions (role_id, module_id, can_access)
SELECT (SELECT id FROM roles WHERE name='Super Admin'), id, TRUE
FROM modules;

-- Default Admin User (password: Admin@123)
INSERT INTO users (username, email, password_hash, role_id, status)
SELECT 'admin', 'admin@ydy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', id, 'Active'
FROM roles WHERE name = 'Super Admin';

SET FOREIGN_KEY_CHECKS = 1;