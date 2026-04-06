CREATE DATABASE IF NOT EXISTS ydy_hrm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ydy_hrm;

-- Temporarily disable FK checks so tables can be created in any order
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+03:00'; -- East Africa Time (Addis Ababa)


-- ============================================================
-- SECTION 1 — COMPANY MASTER
-- ============================================================

-- Stores the single company record (designed for single-tenant use,
-- but UNIQUE(registration_no) allows multi-company expansion later)
CREATE TABLE companies (
  id                  INT UNSIGNED    NOT NULL DEFAULT 1,
  legal_name          VARCHAR(200)    NOT NULL,  
  trading_name        VARCHAR(200)        NULL , 
  ceo_name            VARCHAR(150)        NULL,
  entity_type         ENUM('Private Ltd. Co','Public Ltd. Co','Sole Proprietorship','Partnership','NGO','Government') NOT NULL DEFAULT 'Private Ltd. Co',
  registration_no     VARCHAR(150)         NULL,  
  tin                 VARCHAR(150)         NULL, 
  vat_number          VARCHAR(150)         NULL,
  trade_license_no    VARCHAR(150)         NULL,
  establishment_date  DATE                 NULL,

  -- Address
  head_office_address VARCHAR(255)        NULL ,

  -- Policies
  work_week_desc      VARCHAR(150)        NULL ,
  probation_days      VARCHAR(150)        NULL ,
  retirement_age      VARCHAR(150)        NULL ,

  -- Treasury
  main_bank           VARCHAR(150)        NULL,
  bank_account        VARCHAR(150)         NULL,
  base_currency       CHAR(3)             NULL,
  fiscal_year_start   VARCHAR(150)         NULL,

  -- Digital
  website             VARCHAR(200)        NULL,
  support_email       VARCHAR(150)        NULL,
  corporate_phone     VARCHAR(150)         NULL,

  created_at          DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_registration_no (registration_no)
) ENGINE=InnoDB COMMENT='Company master record — single-tenant';


-- ============================================================
-- SECTION 2 — ORGANIZATIONAL STRUCTURE
-- ============================================================

-- Stores all branch / regional offices
CREATE TABLE branch_offices (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT, 
  branch_name     VARCHAR(150)    NOT NULL,
  city            VARCHAR(100)        NULL,
  country         VARCHAR(80)         NULL DEFAULT 'Ethiopia',
  address         VARCHAR(255)        NULL,
  phone           VARCHAR(30)         NULL,
  email           VARCHAR(150)        NULL,
  -- manager_id added later as FK after employees table exists (ALTER below)
  status          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at      DATETIME     NULL COMMENT 'Soft delete timestamp',

  PRIMARY KEY (id), 
  KEY idx_branch_status    (status),
  CONSTRAINT fk_branch_company  REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Corporate branch / satellite offices';


-- Departments (organizational units)
CREATE TABLE departments (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  branch_id       INT UNSIGNED        NULL COMMENT 'Primary branch location',
  dept_name       VARCHAR(150)    NOT NULL,
  dept_code       VARCHAR(20)         NULL COMMENT 'Short code, e.g. ENG, FIN',
  -- head_employee_id added later via ALTER
  status          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at      DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_dept_code_company (company_id, dept_code),
  KEY idx_dept_company    (company_id),
  KEY idx_dept_branch     (branch_id),
  KEY idx_dept_status     (status),
  CONSTRAINT fk_dept_company FOREIGN KEY (company_id) REFERENCES companies(id),
  CONSTRAINT fk_dept_branch  FOREIGN KEY (branch_id)  REFERENCES branch_offices(id)
) ENGINE=InnoDB COMMENT='Organizational departments / units';


-- Job positions (titles that live inside departments)
CREATE TABLE job_positions (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  department_id   INT UNSIGNED        NULL COMMENT 'NULL = cross-department role',
  job_title       VARCHAR(150)    NOT NULL,
  job_code        VARCHAR(30)         NULL COMMENT 'Internal grade / level code e.g. L3, M2',
  min_salary      DECIMAL(15,2)       NULL,
  max_salary      DECIMAL(15,2)       NULL,
  headcount_target SMALLINT UNSIGNED  NULL COMMENT 'Planned headcount for this position',
  status          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at      DATETIME     NULL,

  PRIMARY KEY (id),
  KEY idx_jp_company    (company_id),
  KEY idx_jp_department (department_id),
  KEY idx_jp_status     (status),
  CONSTRAINT fk_jp_company    FOREIGN KEY (company_id)    REFERENCES companies(id),
  CONSTRAINT fk_jp_department FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB COMMENT='Defined job titles and grade ranges';


-- Employment types (Full-Time, Contract, Part-Time, Internship, Temporary)
CREATE TABLE employment_types (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  type_name       VARCHAR(100)    NOT NULL,
  description     VARCHAR(255)        NULL,
  has_benefits    TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=Yes, 0=No',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_type_company (company_id, type_name),
  KEY idx_etype_company (company_id),
  CONSTRAINT fk_etype_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Employment contract categories';


-- ============================================================
-- SECTION 3 — EMPLOYEE MASTER
-- ============================================================

-- Central employee record — identity + employment placement only.
-- Sensitive data (salary, bank) lives in employee_financials for ACL separation.
-- Contact data lives in employee_contacts for the same reason.
CREATE TABLE employees (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id          INT UNSIGNED    NOT NULL,
  employee_no         VARCHAR(20)     NOT NULL COMMENT 'Public-facing ID e.g. E-0001',

  -- Personal identity (Step 1 of onboarding wizard)
  first_name          VARCHAR(80)     NOT NULL,
  middle_name         VARCHAR(80)         NULL,
  last_name           VARCHAR(80)     NOT NULL,
  date_of_birth       DATE            NOT NULL,
  gender              ENUM('Male','Female','Other') NOT NULL,
  nationality         VARCHAR(80)     NOT NULL DEFAULT 'Ethiopian',
  marital_status      ENUM('Single','Married','Divorced','Widowed') NOT NULL DEFAULT 'Single',
  place_of_birth      VARCHAR(120)        NULL,
  photo_url           VARCHAR(500)        NULL COMMENT 'Path or URL to profile photo',

  -- Employment placement (Step 3 of onboarding wizard)
  department_id       INT UNSIGNED        NULL,
  job_position_id     INT UNSIGNED        NULL,
  branch_id           INT UNSIGNED        NULL,
  employment_type_id  INT UNSIGNED        NULL,
  manager_id          INT UNSIGNED        NULL COMMENT 'Self-referencing FK → employees (reporting manager)',
  hire_date           DATE            NOT NULL,
  contract_start_date DATE                NULL,
  contract_end_date   DATE                NULL COMMENT 'NULL = permanent',
  probation_end_date  DATE                NULL,

  -- Status flags
  status              ENUM('Active','Inactive','On Leave','Terminated','Resigned','Retired') NOT NULL DEFAULT 'Active',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at          DATETIME     NULL COMMENT 'Soft-delete on archival',

  PRIMARY KEY (id),
  UNIQUE KEY uq_employee_no      (company_id, employee_no),
  KEY idx_emp_company            (company_id),
  KEY idx_emp_department         (department_id),
  KEY idx_emp_position           (job_position_id),
  KEY idx_emp_branch             (branch_id),
  KEY idx_emp_type               (employment_type_id),
  KEY idx_emp_manager            (manager_id),
  KEY idx_emp_status             (status),
  KEY idx_emp_hire_date          (hire_date),
  KEY idx_emp_contract_end       (contract_end_date),  -- for expiry alerts
  KEY idx_emp_probation_end      (probation_end_date), -- for probation tracker

  CONSTRAINT fk_emp_company      FOREIGN KEY (company_id)         REFERENCES companies(id),
  CONSTRAINT fk_emp_department   FOREIGN KEY (department_id)      REFERENCES departments(id),
  CONSTRAINT fk_emp_position     FOREIGN KEY (job_position_id)    REFERENCES job_positions(id),
  CONSTRAINT fk_emp_branch       FOREIGN KEY (branch_id)          REFERENCES branch_offices(id),
  CONSTRAINT fk_emp_type         FOREIGN KEY (employment_type_id) REFERENCES employment_types(id),
  CONSTRAINT fk_emp_manager      FOREIGN KEY (manager_id)         REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Core employee master record';


-- Employee contact details (1:1 with employees — separated for security ACL)
CREATE TABLE employee_contacts (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  personal_phone  VARCHAR(30)         NULL,
  personal_email  VARCHAR(150)        NULL,
  work_email      VARCHAR(150)        NULL,
  address_line1   VARCHAR(255)        NULL,
  address_line2   VARCHAR(255)        NULL,
  city            VARCHAR(100)        NULL DEFAULT 'Addis Ababa',
  postal_code     VARCHAR(20)         NULL,
  country         VARCHAR(80)         NULL DEFAULT 'Ethiopia',
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_contact_employee (employee_id),
  KEY idx_contact_email          (personal_email),  -- for unique-employee lookups
  CONSTRAINT fk_contact_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Employee contact info — separated for access control';


-- Employee financial & payroll data (1:1 — highest sensitivity, tightest ACL)
CREATE TABLE employee_financials (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  gross_salary    DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  currency        CHAR(3)         NOT NULL DEFAULT 'ETB',
  tin             VARCHAR(30)         NULL COMMENT 'Employee personal TIN',
  bank_name       VARCHAR(100)        NULL,
  bank_account_no VARCHAR(80)         NULL,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_fin_employee (employee_id),
  CONSTRAINT fk_fin_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Salary and banking data — restricted access';


-- Identity / compliance documents per employee (1:many — passports, national IDs)
CREATE TABLE employee_id_documents (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  id_type         ENUM('National ID','Passport','Driver License','Residence Permit','Other') NOT NULL DEFAULT 'National ID',
  id_number       VARCHAR(80)     NOT NULL,
  issued_date     DATE                NULL,
  expiry_date     DATE                NULL,
  is_primary      TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = primary identity document',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_id_doc (employee_id, id_type, id_number),
  KEY idx_idoc_employee   (employee_id),
  KEY idx_idoc_expiry     (expiry_date), -- for expiry alerts
  CONSTRAINT fk_idoc_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Employee legal identity documents';


-- Emergency contacts per employee (1:many — an employee may list multiple)
CREATE TABLE employee_emergency_contacts (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  contact_name    VARCHAR(150)    NOT NULL,
  relationship    VARCHAR(60)         NULL COMMENT 'e.g. Spouse, Parent, Sibling',
  phone           VARCHAR(30)     NOT NULL,
  email           VARCHAR(150)        NULL,
  is_primary      TINYINT(1)      NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_ec_employee (employee_id),
  CONSTRAINT fk_ec_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Employee emergency contacts';


-- ============================================================
-- NOW ADD BACK-REFERENCES that needed employees to exist first
-- ============================================================

-- Department head  (circular FK handled via SET NULL + no ON DELETE CASCADE)
ALTER TABLE departments
  ADD COLUMN head_employee_id INT UNSIGNED NULL COMMENT 'FK → employees',
  ADD KEY idx_dept_head (head_employee_id),
  ADD CONSTRAINT fk_dept_head FOREIGN KEY (head_employee_id) REFERENCES employees(id) ON DELETE SET NULL;

-- Branch manager
ALTER TABLE branch_offices
  ADD COLUMN manager_id INT UNSIGNED NULL COMMENT 'FK → employees',
  ADD KEY idx_branch_manager (manager_id),
  ADD CONSTRAINT fk_branch_manager FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;


-- ============================================================
-- SECTION 4 — EMPLOYEE LIFECYCLE
-- ============================================================

-- Probation tracker (automatically driven from employees.probation_end_date,
-- but this table stores review notes and confirmation actions)
CREATE TABLE probation_reviews (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id         INT UNSIGNED    NOT NULL,
  probation_start     DATE            NOT NULL,
  probation_end       DATE            NOT NULL,
  reviewer_id         INT UNSIGNED        NULL COMMENT 'Manager who conducted review',
  outcome             ENUM('Passed','Extended','Terminated','Pending') NOT NULL DEFAULT 'Pending',
  notes               TEXT                NULL,
  reviewed_at         DATETIME            NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_prob_employee   (employee_id),
  KEY idx_prob_reviewer   (reviewer_id),
  KEY idx_prob_end        (probation_end),  -- scheduler queries
  KEY idx_prob_outcome    (outcome),
  CONSTRAINT fk_prob_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_prob_reviewer FOREIGN KEY (reviewer_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Probation period tracking and formal review records';


-- Contract renewal log (complementary to employees.contract_end_date)
CREATE TABLE contract_renewals (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id         INT UNSIGNED    NOT NULL,
  previous_end_date   DATE            NOT NULL,
  new_start_date      DATE            NOT NULL,
  new_end_date        DATE                NULL COMMENT 'NULL = converted to permanent',
  renewal_type        ENUM('Extension','Permanent Conversion','New Contract') NOT NULL DEFAULT 'Extension',
  approved_by         INT UNSIGNED        NULL COMMENT 'FK → employees (approver)',
  notes               TEXT                NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_cr_employee     (employee_id),
  KEY idx_cr_new_end      (new_end_date),
  KEY idx_cr_approved_by  (approved_by),
  CONSTRAINT fk_cr_employee    FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_cr_approved_by FOREIGN KEY (approved_by) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Contract renewal history per employee';


-- ============================================================
-- SECTION 5 — DOCUMENT VAULT
-- ============================================================

-- Master list of mandatory document types the company requires
CREATE TABLE vault_document_types (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  doc_code        VARCHAR(30)     NOT NULL COMMENT 'Short code e.g. NDA, CV, COC',
  doc_name        VARCHAR(150)    NOT NULL COMMENT 'Full name e.g. Non-Disclosure Agreement',
  category        VARCHAR(80)         NULL COMMENT 'Grouping e.g. Legal, Identity, Education',
  is_mandatory    TINYINT(1)      NOT NULL DEFAULT 1,
  sort_order      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_vdt_code (company_id, doc_code),
  KEY idx_vdt_company (company_id),
  CONSTRAINT fk_vdt_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Catalog of required HR documents for compliance';


-- Per-employee document fulfillment records
CREATE TABLE employee_documents (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id         INT UNSIGNED    NOT NULL,
  doc_type_id         INT UNSIGNED    NOT NULL COMMENT 'FK → vault_document_types',
  file_name           VARCHAR(255)        NULL,
  file_path           VARCHAR(500)        NULL COMMENT 'Storage path or S3 key',
  file_size_kb        INT UNSIGNED        NULL,
  mime_type           VARCHAR(100)        NULL,
  status              ENUM('Uploaded','Pending','Expired','Rejected') NOT NULL DEFAULT 'Pending',
  uploaded_at         DATETIME            NULL,
  expiry_date         DATE                NULL,
  verified_by         INT UNSIGNED        NULL COMMENT 'FK → employees (HR officer)',
  verified_at         DATETIME            NULL,
  notes               VARCHAR(255)        NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_emp_doctype (employee_id, doc_type_id), -- one record per document type per employee
  KEY idx_edoc_employee    (employee_id),
  KEY idx_edoc_type        (doc_type_id),
  KEY idx_edoc_status      (status),
  KEY idx_edoc_expiry      (expiry_date),
  CONSTRAINT fk_edoc_employee  FOREIGN KEY (employee_id)  REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_edoc_type      FOREIGN KEY (doc_type_id)  REFERENCES vault_document_types(id),
  CONSTRAINT fk_edoc_verified  FOREIGN KEY (verified_by)  REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Employee document vault — compliance fulfillment';


-- ============================================================
-- SECTION 6 — ASSET MANAGEMENT
-- ============================================================

-- High-level asset categories
CREATE TABLE asset_categories (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  category_name   VARCHAR(100)    NOT NULL COMMENT 'e.g. IT Hardware, Fleet, Furniture',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_ac_name (company_id, category_name),
  KEY idx_ac_company (company_id),
  CONSTRAINT fk_ac_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Asset classification categories';


-- Company asset registry
CREATE TABLE assets (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id          INT UNSIGNED    NOT NULL,
  asset_code          VARCHAR(40)     NOT NULL COMMENT 'Barcode / reference e.g. AST-2001',
  asset_name          VARCHAR(200)    NOT NULL,
  category_id         INT UNSIGNED        NULL,
  serial_number       VARCHAR(100)        NULL,
  purchase_value      DECIMAL(15,2)       NULL COMMENT 'Original cost in base currency',
  current_value       DECIMAL(15,2)       NULL COMMENT 'Depreciated book value',
  purchase_date       DATE                NULL,
  warranty_expiry     DATE                NULL,
  branch_id           INT UNSIGNED        NULL COMMENT 'Home location of asset',
  status              ENUM('Available','Assigned','Under Maintenance','Retired','Lost') NOT NULL DEFAULT 'Available',
  notes               TEXT                NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at          DATETIME     NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_asset_code (company_id, asset_code),
  KEY idx_asset_company    (company_id),
  KEY idx_asset_category   (category_id),
  KEY idx_asset_branch     (branch_id),
  KEY idx_asset_status     (status),
  KEY idx_asset_warranty   (warranty_expiry),
  CONSTRAINT fk_asset_company   FOREIGN KEY (company_id)  REFERENCES companies(id),
  CONSTRAINT fk_asset_category  FOREIGN KEY (category_id) REFERENCES asset_categories(id),
  CONSTRAINT fk_asset_branch    FOREIGN KEY (branch_id)   REFERENCES branch_offices(id)
) ENGINE=InnoDB COMMENT='Corporate asset registry';


-- Asset assignment history (who holds what at any point in time)
CREATE TABLE asset_assignments (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  asset_id        INT UNSIGNED    NOT NULL,
  employee_id     INT UNSIGNED    NOT NULL COMMENT 'Current custodian',
  assigned_by     INT UNSIGNED        NULL COMMENT 'FK → employees (assigner)',
  assigned_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  returned_at     DATETIME            NULL COMMENT 'NULL = still assigned',
  notes           VARCHAR(255)        NULL,

  PRIMARY KEY (id),
  KEY idx_aa_asset      (asset_id),
  KEY idx_aa_employee   (employee_id),
  -- Fast query: "all assets currently held by employee X"
  KEY idx_aa_active     (employee_id, returned_at),
  CONSTRAINT fk_aa_asset      FOREIGN KEY (asset_id)    REFERENCES assets(id),
  CONSTRAINT fk_aa_employee   FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_aa_assigner   FOREIGN KEY (assigned_by) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Asset custody trail — full assignment history';


-- ============================================================
-- SECTION 7 — TALENT ACQUISITION
-- ============================================================

-- Open job vacancies posted by HR
CREATE TABLE job_vacancies (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id          INT UNSIGNED    NOT NULL,
  job_position_id     INT UNSIGNED        NULL COMMENT 'FK → job_positions',
  department_id       INT UNSIGNED        NULL,
  branch_id           INT UNSIGNED        NULL,
  employment_type_id  INT UNSIGNED        NULL,
  vacancy_code        VARCHAR(30)         NULL COMMENT 'Internal reference number',
  title               VARCHAR(150)    NOT NULL COMMENT 'Displayed job title (may differ from grade title)',
  description         TEXT                NULL,
  requirements        TEXT                NULL,
  vacancies_count     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  posted_date         DATE                NULL,
  deadline_date       DATE                NULL,
  status              ENUM('Draft','Open','On Hold','Closed','Filled') NOT NULL DEFAULT 'Open',
  created_by          INT UNSIGNED        NULL COMMENT 'FK → employees',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_vac_company    (company_id),
  KEY idx_vac_position   (job_position_id),
  KEY idx_vac_department (department_id),
  KEY idx_vac_status     (status),
  KEY idx_vac_deadline   (deadline_date),
  CONSTRAINT fk_vac_company    FOREIGN KEY (company_id)        REFERENCES companies(id),
  CONSTRAINT fk_vac_position   FOREIGN KEY (job_position_id)   REFERENCES job_positions(id),
  CONSTRAINT fk_vac_department FOREIGN KEY (department_id)     REFERENCES departments(id),
  CONSTRAINT fk_vac_branch     FOREIGN KEY (branch_id)         REFERENCES branch_offices(id),
  CONSTRAINT fk_vac_emp_type   FOREIGN KEY (employment_type_id)REFERENCES employment_types(id),
  CONSTRAINT fk_vac_creator    FOREIGN KEY (created_by)        REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Open job vacancies / postings';


-- Job applicants / candidates pipeline
CREATE TABLE job_applicants (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  vacancy_id      INT UNSIGNED    NOT NULL,
  first_name      VARCHAR(80)     NOT NULL,
  last_name       VARCHAR(80)     NOT NULL,
  email           VARCHAR(150)        NULL,
  phone           VARCHAR(30)         NULL,
  cv_url          VARCHAR(500)        NULL COMMENT 'Uploaded CV path',
  applied_date    DATE                NULL,
  source          ENUM('Website','Referral','Agency','LinkedIn','Walk-In','Other') DEFAULT 'Website',
  stage           ENUM('Applied','Screening','Interview','Assessment','Offer','Hired','Rejected','Withdrawn') NOT NULL DEFAULT 'Applied',
  notes           TEXT                NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_appl_vacancy  (vacancy_id),
  KEY idx_appl_stage    (stage),
  KEY idx_appl_email    (email),
  CONSTRAINT fk_appl_vacancy FOREIGN KEY (vacancy_id) REFERENCES job_vacancies(id)
) ENGINE=InnoDB COMMENT='Job applicant pipeline and hiring stages';


-- Interview scheduling and outcome tracking
CREATE TABLE interviews (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  applicant_id    INT UNSIGNED    NOT NULL,
  interviewer_id  INT UNSIGNED        NULL COMMENT 'FK → employees',
  round           TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Interview round number',
  scheduled_at    DATETIME            NULL,
  mode            ENUM('In-Person','Video Call','Phone','Technical Test') NOT NULL DEFAULT 'In-Person',
  location        VARCHAR(200)        NULL COMMENT 'Room, Zoom link, etc.',
  score           DECIMAL(4,2)        NULL COMMENT 'Score out of 10.00',
  result          ENUM('Passed','Failed','On Hold','No-Show','Scheduled','Cancelled') NOT NULL DEFAULT 'Scheduled',
  notes           TEXT                NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_intv_applicant    (applicant_id),
  KEY idx_intv_interviewer  (interviewer_id),
  -- Quick calendar view: all interviews for a given date
  KEY idx_intv_schedule     (scheduled_at),
  KEY idx_intv_result       (result),
  CONSTRAINT fk_intv_applicant   FOREIGN KEY (applicant_id)   REFERENCES job_applicants(id),
  CONSTRAINT fk_intv_interviewer FOREIGN KEY (interviewer_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Interview sessions and outcomes';


-- Internship management
CREATE TABLE interns (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  department_id   INT UNSIGNED        NULL,
  mentor_id       INT UNSIGNED        NULL COMMENT 'FK → employees',
  intern_no       VARCHAR(20)         NULL COMMENT 'e.g. INT-26-001',
  first_name      VARCHAR(80)     NOT NULL,
  last_name       VARCHAR(80)     NOT NULL,
  email           VARCHAR(150)        NULL,
  phone           VARCHAR(30)         NULL,
  institution     VARCHAR(150)        NULL COMMENT 'University or college name',
  study_field     VARCHAR(120)        NULL,
  start_date      DATE                NULL,
  end_date        DATE                NULL,
  status          ENUM('Active','Completed','Terminated','Converted to Employee') NOT NULL DEFAULT 'Active',
  evaluation_score DECIMAL(5,2)       NULL COMMENT 'Final evaluation score',
  potential_hire  TINYINT(1)          NULL COMMENT '1 = flagged for potential hire',
  notes           TEXT                NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_intern_company    (company_id),
  KEY idx_intern_dept       (department_id),
  KEY idx_intern_mentor     (mentor_id),
  KEY idx_intern_status     (status),
  KEY idx_intern_end        (end_date),
  CONSTRAINT fk_intern_company FOREIGN KEY (company_id)   REFERENCES companies(id),
  CONSTRAINT fk_intern_dept    FOREIGN KEY (department_id)REFERENCES departments(id),
  CONSTRAINT fk_intern_mentor  FOREIGN KEY (mentor_id)    REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Internship records and evaluations';


-- ============================================================
-- SECTION 8 — EMPLOYEE MOVEMENT
-- ============================================================

-- Unified table for promotions, demotions, and transfers.
-- movement_type distinguishes the event; all fields nullable to allow partial changes.
CREATE TABLE employee_movements (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id         INT UNSIGNED    NOT NULL,
  movement_type       ENUM('Promotion','Demotion','Lateral Transfer','Department Transfer','Branch Transfer') NOT NULL,

  -- FROM state (snapshot at the time of movement)
  from_department_id  INT UNSIGNED        NULL,
  from_position_id    INT UNSIGNED        NULL,
  from_branch_id      INT UNSIGNED        NULL,
  from_salary         DECIMAL(15,2)       NULL,

  -- TO state (what it changes to)
  to_department_id    INT UNSIGNED        NULL,
  to_position_id      INT UNSIGNED        NULL,
  to_branch_id        INT UNSIGNED        NULL,
  to_salary           DECIMAL(15,2)       NULL,

  effective_date      DATE            NOT NULL,
  requested_at        DATE                NULL,
  approved_by         INT UNSIGNED        NULL COMMENT 'FK → employees',
  status              ENUM('Pending','Approved','Rejected','Processing') NOT NULL DEFAULT 'Pending',
  reason              TEXT                NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_mv_employee       (employee_id),
  KEY idx_mv_type           (movement_type),
  KEY idx_mv_status         (status),
  KEY idx_mv_effective      (effective_date),
  KEY idx_mv_approved_by    (approved_by),
  CONSTRAINT fk_mv_employee    FOREIGN KEY (employee_id)     REFERENCES employees(id),
  CONSTRAINT fk_mv_from_dept   FOREIGN KEY (from_department_id) REFERENCES departments(id),
  CONSTRAINT fk_mv_to_dept     FOREIGN KEY (to_department_id)   REFERENCES departments(id),
  CONSTRAINT fk_mv_approved    FOREIGN KEY (approved_by)     REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Promotion, demotion, and transfer history';


-- ============================================================
-- SECTION 9 — ATTENDANCE
-- ============================================================

-- One record per employee per day — optimized for the monthly ledger matrix
CREATE TABLE attendance_records (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  work_date       DATE            NOT NULL,
  status          ENUM('P','H','A','L','O') NOT NULL DEFAULT 'P'
                  COMMENT 'P=Present, H=Half-day, A=Absent, L=On Leave, O=Off/Holiday',
  check_in_time   TIME                NULL,
  check_out_time  TIME                NULL,
  work_hours      DECIMAL(4,2)        NULL COMMENT 'Calculated actual hours',
  overtime_hours  DECIMAL(4,2)        NULL DEFAULT 0.00,
  notes           VARCHAR(255)        NULL,
  recorded_by     INT UNSIGNED        NULL COMMENT 'FK → employees (HR who entered record)',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  -- This composite UNIQUE prevents duplicate entries and also serves as the most common lookup
  UNIQUE KEY uq_att_emp_date    (employee_id, work_date),
  -- Covering index for monthly summary queries: "all records for dept X in month Y"
  KEY idx_att_date              (work_date),
  KEY idx_att_status            (status),
  KEY idx_att_recorded_by       (recorded_by),
  CONSTRAINT fk_att_employee    FOREIGN KEY (employee_id)  REFERENCES employees(id),
  CONSTRAINT fk_att_recorder    FOREIGN KEY (recorded_by)  REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Daily attendance ledger — one row per employee per day';


-- ============================================================
-- SECTION 10 — LEAVE MANAGEMENT
-- ============================================================

-- Leave type definitions (Annual, Sick, Maternity, etc.)
CREATE TABLE leave_types (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  type_name       VARCHAR(100)    NOT NULL COMMENT 'e.g. Annual Leave, Sick Leave',
  days_per_year   SMALLINT UNSIGNED NULL COMMENT 'Entitlement per year; NULL = unlimited',
  carryover_days  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_paid         TINYINT(1)      NOT NULL DEFAULT 1,
  requires_approval TINYINT(1)    NOT NULL DEFAULT 1,
  applicable_gender ENUM('All','Male','Female') NOT NULL DEFAULT 'All' COMMENT 'For maternity/paternity',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_lt_name (company_id, type_name),
  KEY idx_lt_company (company_id),
  CONSTRAINT fk_lt_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Leave type catalog and entitlement policy';


-- Per-employee leave entitlement (annual balance ledger)
CREATE TABLE leave_entitlements (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  leave_type_id   INT UNSIGNED    NOT NULL,
  fiscal_year     SMALLINT UNSIGNED NOT NULL COMMENT 'e.g. 2026',
  total_days      DECIMAL(5,1)    NOT NULL DEFAULT 0 COMMENT 'Allocated for this year',
  used_days       DECIMAL(5,1)    NOT NULL DEFAULT 0,
  carried_days    DECIMAL(5,1)    NOT NULL DEFAULT 0 COMMENT 'Carried over from prior year',
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  -- One row per employee per leave type per year
  UNIQUE KEY uq_le_emp_type_year (employee_id, leave_type_id, fiscal_year),
  KEY idx_le_employee       (employee_id),
  KEY idx_le_leave_type     (leave_type_id),
  KEY idx_le_year           (fiscal_year),
  CONSTRAINT fk_le_employee   FOREIGN KEY (employee_id)  REFERENCES employees(id),
  CONSTRAINT fk_le_leave_type FOREIGN KEY (leave_type_id)REFERENCES leave_types(id)
) ENGINE=InnoDB COMMENT='Per-employee per-year leave balance';


-- Leave applications / requests
CREATE TABLE leave_requests (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  leave_type_id   INT UNSIGNED    NOT NULL,
  from_date       DATE            NOT NULL,
  to_date         DATE            NOT NULL,
  days_requested  DECIMAL(4,1)    NOT NULL COMMENT 'May be fractional for half-days',
  reason          VARCHAR(500)        NULL,
  handover_note   TEXT                NULL COMMENT 'Duties handover during absence',
  status          ENUM('Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  reviewed_by     INT UNSIGNED        NULL COMMENT 'FK → employees (approver)',
  reviewed_at     DATETIME            NULL,
  reviewer_note   VARCHAR(500)        NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_lr_employee    (employee_id),
  KEY idx_lr_type        (leave_type_id),
  KEY idx_lr_status      (status),
  -- Fast dashboard query: pending leaves for a date range
  KEY idx_lr_dates       (from_date, to_date),
  KEY idx_lr_reviewer    (reviewed_by),
  CONSTRAINT fk_lr_employee  FOREIGN KEY (employee_id)   REFERENCES employees(id),
  CONSTRAINT fk_lr_type      FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
  CONSTRAINT fk_lr_reviewer  FOREIGN KEY (reviewed_by)   REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Leave application requests and approvals';


-- ============================================================
-- SECTION 11 — BENEFITS
-- ============================================================

-- Medical / health reimbursement claims
CREATE TABLE medical_claims (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  claim_no        VARCHAR(30)         NULL COMMENT 'e.g. MC-001',
  category        ENUM('Doctor Visit','Specialist','Prescription','Dental','Vision','Hospital','Other') NOT NULL DEFAULT 'Doctor Visit',
  claim_amount    DECIMAL(15,2)   NOT NULL,
  currency        CHAR(3)         NOT NULL DEFAULT 'ETB',
  service_date    DATE                NULL,
  submitted_at    DATE                NULL,
  receipt_url     VARCHAR(500)        NULL COMMENT 'Uploaded receipt path',
  status          ENUM('Pending','Approved','Rejected','Paid') NOT NULL DEFAULT 'Pending',
  reviewed_by     INT UNSIGNED        NULL,
  reviewed_at     DATETIME            NULL,
  notes           VARCHAR(500)        NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_mc_employee  (employee_id),
  KEY idx_mc_status    (status),
  KEY idx_mc_category  (category),
  KEY idx_mc_date      (service_date),
  CONSTRAINT fk_mc_employee  FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_mc_reviewer  FOREIGN KEY (reviewed_by) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Employee medical reimbursement claims';


-- Overtime request and approval
CREATE TABLE overtime_requests (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  ot_date         DATE            NOT NULL,
  ot_hours        DECIMAL(4,1)    NOT NULL COMMENT 'Requested overtime hours',
  reason          VARCHAR(500)        NULL,
  submitted_at    DATE                NULL,
  status          ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  approved_by     INT UNSIGNED        NULL,
  approved_at     DATETIME            NULL,
  notes           VARCHAR(300)        NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_ot_employee   (employee_id),
  KEY idx_ot_date       (ot_date),
  KEY idx_ot_status     (status),
  CONSTRAINT fk_ot_employee  FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_ot_approver  FOREIGN KEY (approved_by) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Employee overtime request log';


-- ============================================================
-- SECTION 12 — COMPLIANCE & EXIT
-- ============================================================

-- Disciplinary action log
CREATE TABLE disciplinary_actions (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  issued_by       INT UNSIGNED        NULL COMMENT 'FK → employees (HR/manager)',
  action_type     ENUM('Verbal Warning','Written Warning','Final Warning','Suspension','Demotion','Termination') NOT NULL,
  incident_date   DATE                NULL,
  issued_date     DATE            NOT NULL,
  description     TEXT                NULL,
  employee_response TEXT              NULL COMMENT 'Employee acknowledgment/response',
  attachment_url  VARCHAR(500)        NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_da_employee   (employee_id),
  KEY idx_da_type       (action_type),
  KEY idx_da_issued_by  (issued_by),
  KEY idx_da_date       (issued_date),
  CONSTRAINT fk_da_employee  FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_da_issuer    FOREIGN KEY (issued_by)   REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Disciplinary action records';


-- Resignation submissions
CREATE TABLE resignations (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  resignation_date DATE           NOT NULL,
  last_working_day DATE               NULL,
  reason_category ENUM('Personal','Career Growth','Compensation','Work Environment','Relocation','Health','Other') DEFAULT 'Personal',
  reason_detail   TEXT                NULL,
  notice_period_days SMALLINT UNSIGNED NULL,
  status          ENUM('Submitted','Acknowledged','Approved','Retracted','Processed') NOT NULL DEFAULT 'Submitted',
  processed_by    INT UNSIGNED        NULL,
  processed_at    DATETIME            NULL,
  notes           TEXT                NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_res_employee      (employee_id),
  KEY idx_res_status        (status),
  KEY idx_res_last_day      (last_working_day),
  CONSTRAINT fk_res_employee  FOREIGN KEY (employee_id)  REFERENCES employees(id),
  CONSTRAINT fk_res_processor FOREIGN KEY (processed_by) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Employee resignation records';


-- Separation / termination records (covers all exit types)
CREATE TABLE separations (
  id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id         INT UNSIGNED    NOT NULL,
  separation_type     ENUM('Resignation','Involuntary Termination','End of Contract','Retirement','Redundancy','Death in Service') NOT NULL,
  notice_date         DATE                NULL,
  last_working_date   DATE            NOT NULL,
  final_settlement    DECIMAL(15,2)       NULL COMMENT 'Agreed final settlement amount',
  settlement_status   ENUM('Pending','Processing','Paid') NOT NULL DEFAULT 'Pending',
  initiated_by        INT UNSIGNED        NULL COMMENT 'FK → employees (HR initiator)',
  approved_by         INT UNSIGNED        NULL,
  notes               TEXT                NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_sep_employee      (employee_id),
  KEY idx_sep_type          (separation_type),
  KEY idx_sep_last_day      (last_working_date),
  CONSTRAINT fk_sep_employee  FOREIGN KEY (employee_id)  REFERENCES employees(id),
  CONSTRAINT fk_sep_initiator FOREIGN KEY (initiated_by) REFERENCES employees(id),
  CONSTRAINT fk_sep_approver  FOREIGN KEY (approved_by)  REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Employee separation and offboarding records';


-- Exit clearance — per-department sign-off checklist
CREATE TABLE exit_clearance (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  separation_id   INT UNSIGNED    NOT NULL COMMENT 'FK → separations',
  employee_id     INT UNSIGNED    NOT NULL,
  department_unit VARCHAR(80)     NOT NULL COMMENT 'e.g. IT, Finance, HR, Admin',
  cleared         TINYINT(1)      NOT NULL DEFAULT 0,
  cleared_by      INT UNSIGNED        NULL COMMENT 'FK → employees',
  cleared_at      DATETIME            NULL,
  notes           VARCHAR(300)        NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  -- Fast lookup: all clearance items for a given separation
  UNIQUE KEY uq_ec_sep_dept  (separation_id, department_unit),
  KEY idx_ec_employee        (employee_id),
  CONSTRAINT fk_ec_separation FOREIGN KEY (separation_id) REFERENCES separations(id),
  CONSTRAINT fk_ec_employee   FOREIGN KEY (employee_id)   REFERENCES employees(id),
  CONSTRAINT fk_ec_cleared_by FOREIGN KEY (cleared_by)    REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Exit clearance sign-off per department';


-- ============================================================
-- SECTION 13 — TRAINING & DEVELOPMENT
-- ============================================================

-- Training Needs Analysis (TNA) records
CREATE TABLE training_needs (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  department_id   INT UNSIGNED        NULL,
  skill_gap       VARCHAR(200)    NOT NULL COMMENT 'Description of the identified skill gap',
  priority        ENUM('High','Medium','Low') NOT NULL DEFAULT 'Medium',
  affected_count  SMALLINT UNSIGNED  NULL COMMENT 'Number of staff affected',
  proposed_training VARCHAR(200)     NULL COMMENT 'Suggested training intervention',
  status          ENUM('Identified','Approved','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Identified',
  identified_by   INT UNSIGNED        NULL COMMENT 'FK → employees',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_tna_company    (company_id),
  KEY idx_tna_dept       (department_id),
  KEY idx_tna_priority   (priority),
  KEY idx_tna_status     (status),
  CONSTRAINT fk_tna_company  FOREIGN KEY (company_id)   REFERENCES companies(id),
  CONSTRAINT fk_tna_dept     FOREIGN KEY (department_id)REFERENCES departments(id)
) ENGINE=InnoDB COMMENT='Training needs analysis records';


-- Training session schedule
CREATE TABLE training_sessions (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  tna_id          INT UNSIGNED        NULL COMMENT 'FK → training_needs (optional link)',
  course_name     VARCHAR(200)    NOT NULL,
  department_id   INT UNSIGNED        NULL COMMENT 'Target department (NULL = company-wide)',
  trainer_name    VARCHAR(150)        NULL COMMENT 'External trainer name',
  trainer_id      INT UNSIGNED        NULL COMMENT 'FK → employees (internal trainer)',
  session_date    DATE                NULL,
  start_time      TIME                NULL,
  end_time        TIME                NULL,
  venue           VARCHAR(200)        NULL COMMENT 'Room name, online link, etc.',
  max_seats       SMALLINT UNSIGNED   NULL,
  status          ENUM('Planned','Confirmed','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_ts_company    (company_id),
  KEY idx_ts_dept       (department_id),
  KEY idx_ts_date       (session_date),
  KEY idx_ts_status     (status),
  CONSTRAINT fk_ts_company FOREIGN KEY (company_id)   REFERENCES companies(id),
  CONSTRAINT fk_ts_dept    FOREIGN KEY (department_id)REFERENCES departments(id),
  CONSTRAINT fk_ts_trainer FOREIGN KEY (trainer_id)   REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Training session schedule';


-- Training enrollment (many-to-many: employees ↔ training_sessions)
CREATE TABLE training_enrollments (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  session_id      INT UNSIGNED    NOT NULL,
  employee_id     INT UNSIGNED    NOT NULL,
  enrolled_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  attended        TINYINT(1)          NULL COMMENT 'NULL=unknown, 1=yes, 0=no',
  score           DECIMAL(5,2)        NULL,
  certificate_url VARCHAR(500)        NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_te_session_emp (session_id, employee_id),
  KEY idx_te_employee   (employee_id),
  CONSTRAINT fk_te_session  FOREIGN KEY (session_id)  REFERENCES training_sessions(id),
  CONSTRAINT fk_te_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Training session enrollment and attendance';


-- ============================================================
-- SECTION 14 — PERFORMANCE MANAGEMENT
-- ============================================================

-- Performance review cycles
CREATE TABLE performance_reviews (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED    NOT NULL,
  reviewer_id     INT UNSIGNED        NULL COMMENT 'FK → employees (direct manager)',
  review_period   VARCHAR(20)     NOT NULL COMMENT 'e.g. Q1 2026, FY2025',
  period_start    DATE                NULL,
  period_end      DATE                NULL,
  overall_score   DECIMAL(4,2)        NULL COMMENT 'Score out of 10.00',
  rating          ENUM('Exceptional','Exceeds Expectations','Meets Expectations','Below Expectations','Unsatisfactory') NULL,
  strengths       TEXT                NULL,
  areas_to_improve TEXT               NULL,
  goals_next      TEXT                NULL,
  status          ENUM('Pending','In Progress','Submitted','Acknowledged') NOT NULL DEFAULT 'Pending',
  submitted_at    DATETIME            NULL,
  acknowledged_at DATETIME            NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_pr_employee    (employee_id),
  KEY idx_pr_reviewer    (reviewer_id),
  KEY idx_pr_period      (review_period),
  KEY idx_pr_status      (status),
  CONSTRAINT fk_pr_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_pr_reviewer FOREIGN KEY (reviewer_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='Annual / quarterly performance review records';


-- 360° Feedback campaigns
CREATE TABLE feedback_360_campaigns (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  subject_id      INT UNSIGNED    NOT NULL COMMENT 'Employee being reviewed',
  campaign_name   VARCHAR(150)    NOT NULL COMMENT 'e.g. Q1 2026 360 Review',
  status          ENUM('Open','In Progress','Closed') NOT NULL DEFAULT 'Open',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at       DATETIME            NULL,

  PRIMARY KEY (id),
  KEY idx_360c_company  (company_id),
  KEY idx_360c_subject  (subject_id),
  KEY idx_360c_status   (status),
  CONSTRAINT fk_360c_company  FOREIGN KEY (company_id) REFERENCES companies(id),
  CONSTRAINT fk_360c_subject  FOREIGN KEY (subject_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='360-degree feedback campaign headers';


-- Individual 360° response (one per respondent per campaign)
CREATE TABLE feedback_360_responses (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  campaign_id     INT UNSIGNED    NOT NULL,
  respondent_id   INT UNSIGNED    NOT NULL COMMENT 'FK → employees (peer/manager/subordinate)',
  respondent_type ENUM('Manager','Peer','Subordinate','Self') NOT NULL DEFAULT 'Peer',
  score           DECIMAL(4,2)        NULL COMMENT 'Overall score out of 10',
  comments        TEXT                NULL,
  submitted_at    DATETIME            NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_360r_campaign_resp (campaign_id, respondent_id),
  KEY idx_360r_respondent (respondent_id),
  CONSTRAINT fk_360r_campaign    FOREIGN KEY (campaign_id)   REFERENCES feedback_360_campaigns(id),
  CONSTRAINT fk_360r_respondent  FOREIGN KEY (respondent_id) REFERENCES employees(id)
) ENGINE=InnoDB COMMENT='360-degree feedback individual responses';


-- ============================================================
-- SECTION 15 — SYSTEM ADMINISTRATION
-- ============================================================

-- System user accounts (separate from employee records — one employee may have zero or one system login)
CREATE TABLE system_users (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  employee_id     INT UNSIGNED        NULL COMMENT 'Link to employee record; NULL = non-employee admin',
  username        VARCHAR(60)     NOT NULL,
  email           VARCHAR(150)    NOT NULL,
  password_hash   VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash — NEVER store plaintext',
  role_id         INT UNSIGNED        NULL COMMENT 'FK → roles',
  last_login_at   DATETIME            NULL,
  status          ENUM('Active','Inactive','Locked') NOT NULL DEFAULT 'Active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_su_username  (username),
  UNIQUE KEY uq_su_email     (email),
  KEY idx_su_employee        (employee_id),
  KEY idx_su_role            (role_id),
  KEY idx_su_status          (status),
  CONSTRAINT fk_su_employee  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
  CONSTRAINT fk_su_role      FOREIGN KEY (role_id)     REFERENCES roles(id)
) ENGINE=InnoDB COMMENT='System login accounts';


-- System roles (Super Admin, HRM User, Dept Manager, etc.)
CREATE TABLE roles (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  role_name       VARCHAR(80)     NOT NULL,
  description     VARCHAR(255)        NULL,
  is_system_role  TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '1 = built-in, cannot be deleted',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_role_name (company_id, role_name),
  KEY idx_role_company (company_id),
  CONSTRAINT fk_role_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB COMMENT='Access control roles';


-- Module permission definitions per role
CREATE TABLE role_permissions (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  role_id         INT UNSIGNED    NOT NULL,
  module_id       VARCHAR(30)     NOT NULL COMMENT 'Matches sidebar menu IDs e.g. m-emp, m-leave',
  module_name     VARCHAR(100)    NOT NULL,
  can_view        TINYINT(1)      NOT NULL DEFAULT 1,
  can_create      TINYINT(1)      NOT NULL DEFAULT 0,
  can_edit        TINYINT(1)      NOT NULL DEFAULT 0,
  can_delete      TINYINT(1)      NOT NULL DEFAULT 0,
  can_approve     TINYINT(1)      NOT NULL DEFAULT 0,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_rp_role_module (role_id, module_id),
  KEY idx_rp_role   (role_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Module-level access permissions per role';


-- Individual permission overrides (supercede role defaults for specific users)
CREATE TABLE user_permission_overrides (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED    NOT NULL,
  module_id       VARCHAR(30)     NOT NULL,
  module_name     VARCHAR(100)    NOT NULL,
  can_view        TINYINT(1)      NOT NULL DEFAULT 1,
  can_create      TINYINT(1)      NOT NULL DEFAULT 0,
  can_edit        TINYINT(1)      NOT NULL DEFAULT 0,
  can_delete      TINYINT(1)      NOT NULL DEFAULT 0,
  can_approve     TINYINT(1)      NOT NULL DEFAULT 0,
  created_by      INT UNSIGNED        NULL COMMENT 'Admin who set the override',
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_upo_user_module (user_id, module_id),
  KEY idx_upo_user   (user_id),
  CONSTRAINT fk_upo_user    FOREIGN KEY (user_id)    REFERENCES system_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_upo_creator FOREIGN KEY (created_by) REFERENCES system_users(id)
) ENGINE=InnoDB COMMENT='Per-user permission overrides (higher priority than role defaults)';


-- Full system audit trail — immutable, append-only
CREATE TABLE audit_logs (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Big INT for high-volume tables',
  user_id         INT UNSIGNED        NULL COMMENT 'FK → system_users',
  employee_id     INT UNSIGNED        NULL COMMENT 'Context: which employee was affected',
  action          ENUM('CREATE','READ','UPDATE','DELETE','LOGIN','LOGOUT','APPROVE','REJECT','EXPORT') NOT NULL,
  module          VARCHAR(60)     NOT NULL COMMENT 'System module name e.g. Employees, Leave',
  record_id       INT UNSIGNED        NULL COMMENT 'PK of the affected record',
  record_label    VARCHAR(200)        NULL COMMENT 'Human-readable identifier of the record',
  old_values      JSON                NULL COMMENT 'Snapshot before change',
  new_values      JSON                NULL COMMENT 'Snapshot after change',
  ip_address      VARCHAR(45)         NULL COMMENT 'Supports IPv6',
  user_agent      VARCHAR(300)        NULL,
  created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_al_user       (user_id),
  KEY idx_al_action     (action),
  KEY idx_al_module     (module),
  KEY idx_al_created    (created_at),          -- time-range queries
  -- Composite: "show all changes to a specific record"
  KEY idx_al_record     (module, record_id),
  CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES system_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Immutable system audit trail — never update or delete rows here';


-- Custom report definitions (saved query builder configurations)
CREATE TABLE custom_report_definitions (
  id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  company_id      INT UNSIGNED    NOT NULL,
  created_by      INT UNSIGNED        NULL COMMENT 'FK → system_users',
  report_name     VARCHAR(150)    NOT NULL,
  module          VARCHAR(60)         NULL COMMENT 'Base data module e.g. Employees, Attendance',
  date_range_from DATE                NULL,
  date_range_to   DATE                NULL,
  filters_json    JSON                NULL COMMENT 'Serialized filter parameters',
  columns_json    JSON                NULL COMMENT 'Selected columns to display',
  is_shared       TINYINT(1)      NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_crd_company    (company_id),
  KEY idx_crd_created_by (created_by),
  CONSTRAINT fk_crd_company FOREIGN KEY (company_id)  REFERENCES companies(id),
  CONSTRAINT fk_crd_user   FOREIGN KEY (created_by)   REFERENCES system_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Saved custom report configurations';


-- ============================================================
-- Re-enable FK checks
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- SECTION 16 — SEED DATA (Reference / Lookup Tables)
-- ============================================================

-- Insert the single company record
INSERT INTO companies (legal_name, trading_name, entity_type, registration_no, tin, vat_number,
  trade_license_no, establishment_date, head_office_address, city, country,
  work_week_desc, probation_days, retirement_age, main_bank, bank_account, base_currency,
  fiscal_year_start, website, support_email, corporate_phone)
VALUES (
  'YDY HRM Enterprise Ltd.', 'YDY Systems', 'Private Ltd. Co', 'MT/AA/14/667/09',
  '0019283746', '9928374-VAT-01', '01/01/14/19283', '2010-10-12',
  'Mexico, Lideta, Addis Ababa', 'Addis Ababa', 'Ethiopia',
  'Mon–Fri (40 hrs) + Sat (Half day)', 90, 60,
  'CBE (Commercial Bank of Ethiopia)', '1000192837465', 'ETB',
  'Hamle 01 (July 08)', 'https://www.ydy-hrm.com', 'support@ydyhrm.com', '+251 11 667 89'
);

-- Default employment types
INSERT INTO employment_types (company_id, type_name, description, has_benefits) VALUES
  (1, 'Permanent / Full-Time', 'Regular employee with full benefits',   1),
  (1, 'Fixed-Term Contract',   'Time-bound employment agreement',        1),
  (1, 'Part-Time',             'Less than 40 hours per week',            1),
  (1, 'Internship',            'Student or graduate trainee',            0),
  (1, 'Temporary / Casual',   'Short-term project-based',               0);

-- Default leave types
INSERT INTO leave_types (company_id, type_name, days_per_year, carryover_days, is_paid, requires_approval, applicable_gender) VALUES
  (1, 'Annual Leave',        20, 5, 1, 1, 'All'),
  (1, 'Sick Leave',          10, 0, 1, 0, 'All'),
  (1, 'Maternity Leave',     90, 0, 1, 1, 'Female'),
  (1, 'Paternity Leave',     14, 0, 1, 1, 'Male'),
  (1, 'Bereavement Leave',    5, 0, 1, 1, 'All'),
  (1, 'Unpaid Leave',      NULL, 0, 0, 1, 'All'),
  (1, 'Study / Exam Leave',   5, 0, 1, 1, 'All'),
  (1, 'Public Holiday',      12, 0, 1, 0, 'All');

-- Default asset categories
INSERT INTO asset_categories (company_id, category_name) VALUES
  (1, 'IT Hardware'),
  (1, 'Fleet / Vehicles'),
  (1, 'Office Furniture'),
  (1, 'Networking Equipment'),
  (1, 'Security Equipment'),
  (1, 'Office Supplies');

-- Default vault document types (mandatory personnel documents)
INSERT INTO vault_document_types (company_id, doc_code, doc_name, category, is_mandatory, sort_order) VALUES
  (1, 'CONTRACT',   'Signed Employment Contract',       'Legal',        1, 1),
  (1, 'CV',         'Curriculum Vitae (CV)',             'Identity',     1, 2),
  (1, 'ACADEMIC',   'Academic Credentials',              'Education',    1, 3),
  (1, 'CLEARANCE',  'Clearance / Release Letter',        'History',      1, 4),
  (1, 'EXPERIENCE', 'Experience Letters',                'History',      0, 5),
  (1, 'COC',        'Certificate of Competence (COC)',   'Professional', 0, 6),
  (1, 'GUARANTOR',  'Guarantor Form & ID',               'Legal',        1, 7),
  (1, 'NDA',        'Confidentiality / NDA Agreement',   'Compliance',   1, 8),
  (1, 'HANDBOOK',   'Staff Handbook Acknowledgment',     'Compliance',   1, 9),
  (1, 'NATID',      'National ID / Passport Copy',       'Identity',     1, 10),
  (1, 'TIN',        'TIN Certification Document',        'Tax',          1, 11),
  (1, 'MEDICAL',    'Health & Fitness Clearance',        'Compliance',   0, 12);

-- Default system roles
INSERT INTO roles (company_id, role_name, description, is_system_role) VALUES
  (1, 'Super Admin',        'Full system authority — unrestricted access',  1),
  (1, 'HRM User',           'Standard HR operations access',                1),
  (1, 'Department Manager', 'Limited to own team data and approvals',        1);

-- Super Admin gets full permissions on all modules
INSERT INTO role_permissions (role_id, module_id, module_name, can_view, can_create, can_edit, can_delete, can_approve)
SELECT 1, m.module_id, m.module_name, 1, 1, 1, 1, 1
FROM (SELECT 'm-org' AS module_id, 'Company & Structure' AS module_name
  UNION ALL SELECT 'm-emp',   'Employees'
  UNION ALL SELECT 'm-rec',   'Talent Acquisition'
  UNION ALL SELECT 'm-move',  'Employee Movement'
  UNION ALL SELECT 'm-att',   'Attendance'
  UNION ALL SELECT 'm-leave', 'Leave Management'
  UNION ALL SELECT 'm-ben',   'Benefits'
  UNION ALL SELECT 'm-comp',  'Compliance & Exit'
  UNION ALL SELECT 'm-train', 'Training & Dev'
  UNION ALL SELECT 'm-perf',  'Performance'
  UNION ALL SELECT 'm-rep',   'Reports & Analytics'
  UNION ALL SELECT 'm-sys',   'System Admin'
) AS m;


-- ============================================================
-- SECTION 17 — USEFUL VIEWS
-- ============================================================

-- Active employees with their key details (most common dashboard query)
CREATE OR REPLACE VIEW v_active_employees AS
SELECT
  e.id,
  e.employee_no,
  CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS full_name,
  e.gender,
  e.hire_date,
  e.status,
  d.dept_name,
  jp.job_title,
  b.branch_name,
  et.type_name      AS employment_type,
  c.personal_phone,
  c.personal_email,
  c.city
FROM employees e
LEFT JOIN departments     d  ON e.department_id      = d.id
LEFT JOIN job_positions   jp ON e.job_position_id    = jp.id
LEFT JOIN branch_offices  b  ON e.branch_id          = b.id
LEFT JOIN employment_types et ON e.employment_type_id = et.id
LEFT JOIN employee_contacts c ON e.id               = c.employee_id
WHERE e.status = 'Active'
  AND e.deleted_at IS NULL;


-- Document compliance summary per employee (powers the vault matrix)
CREATE OR REPLACE VIEW v_document_compliance AS
SELECT
  e.id                                                            AS employee_id,
  e.employee_no,
  CONCAT_WS(' ', e.first_name, e.last_name)                      AS full_name,
  COUNT(vdt.id)                                                   AS total_required,
  SUM(CASE WHEN ed.status = 'Uploaded' THEN 1 ELSE 0 END)        AS uploaded,
  SUM(CASE WHEN ed.status != 'Uploaded' OR ed.id IS NULL THEN 1 ELSE 0 END) AS missing,
  ROUND(SUM(CASE WHEN ed.status = 'Uploaded' THEN 1 ELSE 0 END) /
        COUNT(vdt.id) * 100, 1)                                   AS compliance_pct
FROM employees e
CROSS JOIN vault_document_types vdt ON vdt.company_id = e.company_id AND vdt.is_mandatory = 1
LEFT JOIN employee_documents ed ON ed.employee_id = e.id AND ed.doc_type_id = vdt.id
WHERE e.status = 'Active' AND e.deleted_at IS NULL
GROUP BY e.id, e.employee_no, full_name;


-- Contracts expiring within the next 30 days (dashboard critical alert)
CREATE OR REPLACE VIEW v_expiring_contracts AS
SELECT
  e.id,
  e.employee_no,
  CONCAT_WS(' ', e.first_name, e.last_name) AS full_name,
  d.dept_name,
  e.contract_end_date,
  DATEDIFF(e.contract_end_date, CURDATE())  AS days_remaining
FROM employees e
LEFT JOIN departments d ON e.department_id = d.id
WHERE e.contract_end_date IS NOT NULL
  AND e.contract_end_date >= CURDATE()
  AND e.contract_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
  AND e.status = 'Active'
  AND e.deleted_at IS NULL
ORDER BY e.contract_end_date ASC;


-- Monthly attendance summary per employee (powers Attendance Reports page)
CREATE OR REPLACE VIEW v_monthly_attendance_summary AS
SELECT
  ar.employee_id,
  YEAR(ar.work_date)  AS year,
  MONTH(ar.work_date) AS month,
  COUNT(*)                                                        AS total_days,
  SUM(ar.status = 'P')                                           AS present,
  SUM(ar.status = 'H')                                           AS half_days,
  SUM(ar.status = 'A')                                           AS absent,
  SUM(ar.status = 'L')                                           AS on_leave,
  SUM(ar.status = 'O')                                           AS day_off,
  ROUND(SUM(ar.overtime_hours), 1)                               AS total_ot_hrs,
  ROUND(SUM(ar.status IN ('P','H')) / COUNT(*) * 100, 1)        AS attendance_rate_pct
FROM attendance_records ar
GROUP BY ar.employee_id, YEAR(ar.work_date), MONTH(ar.work_date);


-- Headcount per department (powers the dashboard chart)
CREATE OR REPLACE VIEW v_headcount_by_department AS
SELECT
  d.id            AS department_id,
  d.dept_name,
  COUNT(e.id)     AS headcount
FROM departments d
LEFT JOIN employees e ON e.department_id = d.id
  AND e.status = 'Active'
  AND e.deleted_at IS NULL
WHERE d.deleted_at IS NULL
GROUP BY d.id, d.dept_name
ORDER BY headcount DESC;


-- Leave balance summary (powers Leave Entitlement page)
CREATE OR REPLACE VIEW v_leave_balances AS
SELECT
  le.employee_id,
  e.employee_no,
  CONCAT_WS(' ', e.first_name, e.last_name)  AS full_name,
  lt.type_name                                AS leave_type,
  le.fiscal_year,
  le.total_days,
  le.used_days,
  le.carried_days,
  ROUND(le.total_days + le.carried_days - le.used_days, 1) AS remaining_balance
FROM leave_entitlements le
JOIN employees   e ON e.id  = le.employee_id
JOIN leave_types lt ON lt.id = le.leave_type_id
WHERE e.deleted_at IS NULL;


-- ============================================================
-- SECTION 18 — KEY STORED PROCEDURES
-- ============================================================

DELIMITER $$

-- Procedure: Generate a fresh employee number in format E-XXXX
CREATE PROCEDURE sp_next_employee_no(IN p_company_id INT UNSIGNED, OUT p_employee_no VARCHAR(20))
BEGIN
  DECLARE v_max INT UNSIGNED DEFAULT 0;
  SELECT COALESCE(MAX(CAST(SUBSTRING(employee_no, 3) AS UNSIGNED)), 0)
    INTO v_max
    FROM employees
   WHERE company_id = p_company_id;
  SET p_employee_no = CONCAT('E-', LPAD(v_max + 1, 4, '0'));
END$$


-- Procedure: Soft-delete an employee and create a separation record atomically
CREATE PROCEDURE sp_terminate_employee(
  IN p_employee_id     INT UNSIGNED,
  IN p_sep_type        VARCHAR(50),
  IN p_last_work_date  DATE,
  IN p_initiated_by    INT UNSIGNED,
  IN p_notes           TEXT
)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;

    -- 1. Update employee status
    UPDATE employees
       SET status     = 'Terminated',
           deleted_at = NOW()
     WHERE id = p_employee_id;

    -- 2. Insert separation record
    INSERT INTO separations (employee_id, separation_type, last_working_date, initiated_by, notes)
    VALUES (p_employee_id, p_sep_type, p_last_work_date, p_initiated_by, p_notes);

    -- 3. Seed exit clearance checklist rows
    INSERT INTO exit_clearance (separation_id, employee_id, department_unit)
    SELECT LAST_INSERT_ID(), p_employee_id, u.unit
    FROM (SELECT 'IT' AS unit UNION ALL SELECT 'Finance'
          UNION ALL SELECT 'HR' UNION ALL SELECT 'Admin' UNION ALL SELECT 'Assets') u;

  COMMIT;
END$$


-- Procedure: Apply an approved employee movement (promotion/transfer)
CREATE PROCEDURE sp_apply_movement(IN p_movement_id INT UNSIGNED)
BEGIN
  DECLARE v_emp_id    INT UNSIGNED;
  DECLARE v_to_dept   INT UNSIGNED;
  DECLARE v_to_pos    INT UNSIGNED;
  DECLARE v_to_branch INT UNSIGNED;
  DECLARE v_to_sal    DECIMAL(15,2);

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  SELECT employee_id, to_department_id, to_position_id, to_branch_id, to_salary
    INTO v_emp_id, v_to_dept, v_to_pos, v_to_branch, v_to_sal
    FROM employee_movements
   WHERE id = p_movement_id AND status = 'Approved';

  IF v_emp_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movement not found or not in Approved status';
  END IF;

  START TRANSACTION;

    -- 1. Update employees master record
    UPDATE employees
       SET department_id    = COALESCE(v_to_dept,   department_id),
           job_position_id  = COALESCE(v_to_pos,    job_position_id),
           branch_id        = COALESCE(v_to_branch, branch_id)
     WHERE id = v_emp_id;

    -- 2. Update salary if included
    IF v_to_sal IS NOT NULL THEN
      UPDATE employee_financials
         SET gross_salary = v_to_sal
       WHERE employee_id = v_emp_id;
    END IF;

    -- 3. Mark movement as processing
    UPDATE employee_movements SET status = 'Processing' WHERE id = p_movement_id;

  COMMIT;
END$$

DELIMITER ;


-- ============================================================
-- END OF SCHEMA
-- ============================================================
-- Table count summary:
--   Core structure   : companies, branch_offices, departments, job_positions, employment_types
--   Employee master  : employees, employee_contacts, employee_financials,
--                      employee_id_documents, employee_emergency_contacts
--   Lifecycle        : probation_reviews, contract_renewals
--   Vault            : vault_document_types, employee_documents
--   Assets           : asset_categories, assets, asset_assignments
--   Recruitment      : job_vacancies, job_applicants, interviews, interns
--   Movement         : employee_movements
--   Attendance       : attendance_records
--   Leave            : leave_types, leave_entitlements, leave_requests
--   Benefits         : medical_claims, overtime_requests
--   Compliance/Exit  : disciplinary_actions, resignations, separations, exit_clearance
--   Training         : training_needs, training_sessions, training_enrollments
--   Performance      : performance_reviews, feedback_360_campaigns, feedback_360_responses
--   System           : system_users, roles, role_permissions,
--                      user_permission_overrides, audit_logs, custom_report_definitions
-- ============================================================
