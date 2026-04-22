<?php
/**
 * ==============================================================================
 * YONA ERP - MANUAL ATTENDANCE MANAGEMENT SYSTEM IMPLEMENTATION GUIDE
 * ==============================================================================
 * 
 * DESIGN PHILOSOPHY:
 * 1. Fast & Performant: Since data will be fed manually or via Excel, we avoid
 *    real-time cron jobs that kill the DB. All heavy calculations (Overtime, Lateness)
 *    happen *during* insertion, not during fetching.
 * 2. Optimized: UI must use Server-Side rendering for DataTables to avoid freezing.
 * 3. Scalable: Excel import must process in chunks to prevent memory limit exhaustion.
 * 
 * MODULES COVERED:
 * - Shift Management
 * - Manual Attendance Tracking
 * - Overtime Calculation
 * - Excel Bulk Upload Roadmap
 * 
 * HOW TO USE THIS FILE:
 * Read through the schemas and API logic. Copy the specific sections into their 
 * respective files (e.g., shifts.php, attendance_upload.php) when you start coding them.
 */

/* ==============================================================================
 * 1. DATABASE SCHEMA DESIGN (Run these in phpMyAdmin / MySQL)
 * ============================================================================== 
 * We design the tables to be lightweight. We use INTs for states and indexed dates.
 */

$sql_schemas = "
-- 1. Shifts Table: Defines different working hours (Morning, Night, Custom)
CREATE TABLE `shifts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `shift_name` VARCHAR(50) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `grace_period_minutes` INT DEFAULT 15, -- Minutes allowed before marked Late
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Employee Shifts: Maps employees to their assigned shifts
CREATE TABLE `employee_shifts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `shift_id` INT NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE DEFAULT NULL,
    FOREIGN KEY (`employee_id`) REFERENCES employees(`id`),
    FOREIGN KEY (`shift_id`) REFERENCES shifts(`id`)
);

-- 3. Attendance Logs: The core table. Optimized with indexes on date and employee_id.
CREATE TABLE `attendance_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `attendance_date` DATE NOT NULL,
    `clock_in` TIME DEFAULT NULL,
    `clock_out` TIME DEFAULT NULL,
    `status` ENUM('Present', 'Absent', 'Late', 'Half Day', 'On Leave') DEFAULT 'Absent',
    `is_manual` BOOLEAN DEFAULT TRUE, -- Indicates it was manually entered or uploaded
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_attendance` (`employee_id`, `attendance_date`), -- Prevents duplicate entries per day
    INDEX `idx_date_status` (`attendance_date`, `status`) -- Speeds up daily dashboard queries
);

-- 4. Overtime Records: Kept separate so it doesn't bloat the attendance table.
CREATE TABLE `overtime_records` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `attendance_id` BIGINT NOT NULL,
    `overtime_date` DATE NOT NULL,
    `approved_hours` DECIMAL(5,2) DEFAULT 0.00,
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    FOREIGN KEY (`attendance_id`) REFERENCES attendance_logs(`id`) ON DELETE CASCADE
);
";


/* ==============================================================================
 * 2. API ENDPOINTS STRUCTURE (What files you need to create)
 * ==============================================================================
 */

/**
 * File: api/attendance/save_daily_attendance.php
 * Purpose: Handle manual grid entry of attendance for multiple employees at once.
 */
function example_save_daily_attendance() {
    // 1. Receive JSON payload of [{employee_id: 1, clock_in: "08:00", clock_out: "17:00", status: "Present"}]
    // 2. Begin DB Transaction to ensure data integrity
    // 3. Loop through payload.
    // 4. Calculate Overtime & Lateness ON THE FLY.
    //    - If clock_in > Shift Start Time + Grace Period => Status = Late
    //    - If clock_out > Shift End Time by > 1 hour => Insert into overtime_records (Pending)
    // 5. Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new entries and edits seamlessly without failing.
    // 6. Commit Transaction.
}

/**
 * File: api/attendance/upload_excel_attendance.php
 * Purpose: To feed attendance from biometric machine exports or HR spreadsheets.
 */
function example_excel_upload_logic() {
    // 1. Use PhpSpreadsheet library to read the uploaded .xlsx file.
    // 2. SUPER IMPORTANT FOR PERFORMANCE: Do not insert row by row!
    // 3. Build a bulk SQL insert string: 
    //    "INSERT INTO attendance_logs (employee_id, attendance_date, clock_in, clock_out) VALUES 
    //    (1, '2026-04-22', '08:00', '17:00'), (2, '2026-04-22', '07:55', '16:50')..."
    // 4. Execute the bulk insert. This allows inserting 5,000+ rows in less than a second.
}

/**
 * File: api/attendance/fetch_attendance.php
 * Purpose: Feed the frontend UI.
 */
function example_fetch_attendance() {
    // 1. MUST USE Server-Side DataTables Pagination. 
    // 2. Receive `start`, `length`, `search` parameters from frontend.
    // 3. Query DB using `LIMIT :start, :length`.
    // 4. This ensures your system will never crash, even if there are 1 million attendance records.
}


/* ==============================================================================
 * 3. FRONTEND UI & PERFORMANCE GUIDELINES (assets/js/attendance.js)
 * ==============================================================================
 * 
 * 1. UI AESTHETICS (The "Wow" Factor):
 *    - Use a Calendar Heatmap (like GitHub contributions) for individual employees to show 
 *      attendance visually. Green = Present, Red = Absent, Yellow = Late.
 *    - Include a modern, dark-themed summary dashboard at the top (Total Present, Absent today)
 *      using smooth CSS transitions on hover.
 * 
 * 2. MANUAL ENTRY WORKFLOW:
 *    - Do NOT make the user submit one by one.
 *    - Create an editable HTML table (like an Excel sheet in the browser) where HR can rapidly 
 *      type time-ins and time-outs, then press one single "Save All" button.
 * 
 * 3. EXCEL UPLOAD WORKFLOW:
 *    - Create a beautiful drag-and-drop file upload zone.
 *    - When the file uploads, show a visual progress bar.
 *    - Once processed, return a summary: "500 records inserted, 2 errors."
 * 
 * 4. OPTIMIZATION RULE:
 *    - Never fetch more than 50 records at a time from the API. Always use AJAX pagination.
 */

?>
