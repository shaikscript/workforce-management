-- AI SQUARE HUB Database Schema
-- MySQL / MariaDB (InnoDB, utf8mb4_unicode_ci)

CREATE DATABASE IF NOT EXISTS ai_square_hub DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ai_square_hub;

-- 1. Company Settings
CREATE TABLE company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT 'AI SQUARE IT SOLUTIONS',
    tagline VARCHAR(255) DEFAULT 'Build • Evaluate • Grow Together',
    logo_path VARCHAR(255) DEFAULT 'assets/img/logo.svg',
    default_designation_label VARCHAR(100) DEFAULT 'AI Evaluator',
    timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
    smtp_host VARCHAR(255),
    smtp_port INT DEFAULT 587,
    smtp_encryption VARCHAR(20) DEFAULT 'tls',
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    smtp_from_email VARCHAR(255),
    smtp_from_name VARCHAR(255),
    payroll_cycle_day INT DEFAULT 1,
    attendance_auto_checkin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'hr_manager', 'payroll_manager', 'assessment_manager', 'recruitment_manager') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Admin Audit Logs
CREATE TABLE admin_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    portal VARCHAR(20) DEFAULT 'admin',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Employees
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(255),
    joining_date DATE,
    employment_status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    salary_template_id INT,
    bank_name VARCHAR(100),
    account_holder_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc_code VARCHAR(20),
    pan VARCHAR(20),
    pf_uan VARCHAR(50),
    esi_number VARCHAR(50),
    emergency_contact_name VARCHAR(100),
    emergency_contact_relation VARCHAR(50),
    emergency_contact_phone VARCHAR(20),
    residential_address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (salary_template_id) REFERENCES salary_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Salary Templates
CREATE TABLE salary_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    basic_salary DECIMAL(12,2) DEFAULT 0,
    hra DECIMAL(12,2) DEFAULT 0,
    special_allowance DECIMAL(12,2) DEFAULT 0,
    conveyance_allowance DECIMAL(12,2) DEFAULT 0,
    performance_bonus DECIMAL(12,2) DEFAULT 0,
    pf_deduction DECIMAL(12,2) DEFAULT 0,
    esi_deduction DECIMAL(12,2) DEFAULT 0,
    professional_tax DECIMAL(12,2) DEFAULT 0,
    tds_tax DECIMAL(12,2) DEFAULT 0,
    other_deductions DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Employee Salaries (Custom Overrides)
CREATE TABLE employee_salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    template_id INT,
    basic_salary DECIMAL(12,2),
    hra DECIMAL(12,2),
    special_allowance DECIMAL(12,2),
    conveyance_allowance DECIMAL(12,2),
    performance_bonus DECIMAL(12,2),
    pf_deduction DECIMAL(12,2),
    esi_deduction DECIMAL(12,2),
    professional_tax DECIMAL(12,2),
    tds_tax DECIMAL(12,2),
    other_deductions DECIMAL(12,2),
    effective_from DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES salary_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Employee Salary Revisions
CREATE TABLE employee_salary_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_salary_id INT,
    revision_number INT DEFAULT 1,
    old_salary_data JSON,
    new_salary_data JSON,
    reason TEXT,
    effective_from DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_salary_id) REFERENCES employee_salaries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Attendance Records
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    date DATE NOT NULL,
    status ENUM('present', 'half_day', 'paid_leave', 'unpaid_leave', 'weekly_off', 'holiday', 'absent') NOT NULL,
    check_in_time DATETIME,
    check_out_time DATETIME,
    total_hours DECIMAL(5,2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Attendance Audit Logs
CREATE TABLE attendance_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_id INT,
    admin_id INT,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_id) REFERENCES attendance_records(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Holidays
CREATE TABLE holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Leave Requests
CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    leave_type ENUM('casual', 'sick', 'unpaid') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Payroll Cycles
CREATE TABLE payroll_cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month INT NOT NULL,
    year INT NOT NULL,
    status ENUM('draft', 'pending_review', 'published') DEFAULT 'draft',
    total_employees INT DEFAULT 0,
    total_payout DECIMAL(15,2) DEFAULT 0,
    generated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES admins(id) ON DELETE SET NULL,
    UNIQUE KEY unique_month_year (month, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Payslips (Immutable Snapshots)
CREATE TABLE payslips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_cycle_id INT,
    employee_id INT,
    revision_number INT DEFAULT 1,
    parent_payslip_id INT,
    snapshot_json JSON NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (payroll_cycle_id) REFERENCES payroll_cycles(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_payslip_id) REFERENCES payslips(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Evaluation Categories
CREATE TABLE evaluation_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Rating Scales
CREATE TABLE rating_scales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    rating_label VARCHAR(50) NOT NULL,
    color_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Employee Assessments
CREATE TABLE employee_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    category_id INT,
    batch_title VARCHAR(100),
    eval_date DATE,
    score_percent DECIMAL(5,2),
    tasks_count INT DEFAULT 0,
    remarks TEXT,
    published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES evaluation_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Candidates
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position_applied VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Tests
CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    instructions TEXT,
    duration_minutes INT NOT NULL,
    passing_percentage DECIMAL(5,2) NOT NULL,
    max_attempts INT DEFAULT 1,
    randomize_questions TINYINT(1) DEFAULT 1,
    shuffle_options TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Test Sections
CREATE TABLE test_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT,
    name VARCHAR(100) NOT NULL,
    order_number INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Test Questions
CREATE TABLE test_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT,
    section_id INT,
    question_type ENUM('mcq', 'descriptive', 'image', 'audio') NOT NULL,
    question_text TEXT NOT NULL,
    options_json JSON,
    correct_option INT,
    marks DECIMAL(5,2) NOT NULL,
    media_url VARCHAR(255),
    media_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES test_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Candidate Test Assignments
CREATE TABLE candidate_test_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT,
    test_id INT,
    start_deadline DATETIME,
    expiry_deadline DATETIME,
    assigned_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES admins(id) ON DELETE SET NULL,
    UNIQUE KEY unique_candidate_test (candidate_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Candidate Attempts
CREATE TABLE candidate_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT,
    start_time DATETIME,
    end_time DATETIME,
    total_score DECIMAL(5,2),
    percentage DECIMAL(5,2),
    status ENUM('in_progress', 'submitted', 'graded', 'published') DEFAULT 'in_progress',
    result_published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES candidate_test_assignments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Candidate Answers
CREATE TABLE candidate_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT,
    question_id INT,
    answer_text TEXT,
    selected_option INT,
    marks_obtained DECIMAL(5,2),
    evaluator_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES candidate_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES test_questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attempt_question (attempt_id, question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Candidate Security Logs
CREATE TABLE candidate_security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT,
    event_type ENUM('tab_switch', 'window_blur', 'page_refresh', 'timer_expired') NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    details TEXT,
    FOREIGN KEY (attempt_id) REFERENCES candidate_attempts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25. Announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('normal', 'important', 'urgent') DEFAULT 'normal',
    target_audience ENUM('all_employees', 'specific_employees', 'candidates') DEFAULT 'all_employees',
    send_email_alert TINYINT(1) DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 26. Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    portal ENUM('admin', 'employee', 'candidate') DEFAULT 'employee',
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES employees(id) ON DELETE CASCADE -- Can be extended for candidates/admins
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. Feedback Tickets
CREATE TABLE feedback_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    topic ENUM('payroll', 'assessment', 'attendance_leave', 'suggestion', 'issue') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_anonymous TINYINT(1) DEFAULT 0,
    status ENUM('open', 'in_review', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 28. Feedback Replies
CREATE TABLE feedback_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT,
    admin_id INT,
    reply_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES feedback_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for performance
CREATE INDEX idx_employee_id ON attendance_records(employee_id);
CREATE INDEX idx_employee_id ON leave_requests(employee_id);
CREATE INDEX idx_employee_id ON employee_assessments(employee_id);
CREATE INDEX idx_candidate_id ON candidate_test_assignments(candidate_id);
CREATE INDEX idx_attempt_id ON candidate_answers(attempt_id);
CREATE INDEX idx_ticket_id ON feedback_replies(ticket_id);

-- Initial Seed Data
INSERT INTO company_settings (company_name, tagline, logo_path, default_designation_label) 
VALUES ('AI SQUARE IT SOLUTIONS', 'Build • Evaluate • Grow Together', 'assets/img/logo.svg', 'AI Evaluator');

-- Super Admin Account (Default)
INSERT INTO admins (username, email, password_hash, name, role) 
VALUES ('superadmin', 'admin@aisquare.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin');

-- Sample Employee
INSERT INTO employees (employee_id, full_name, email, mobile_number, password_hash, joining_date, employment_status) 
VALUES ('AISQ-001', 'Rahul Sharma', 'rahul@aisquare.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-15', 'active');

-- Sample Candidate
INSERT INTO candidates (candidate_id, full_name, email, phone, position_applied, password_hash) 
VALUES ('AISQ-CAND-001', 'Priya Patel', 'priya@example.com', '9123456789', 'AI Evaluator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample Evaluation Categories
INSERT INTO evaluation_categories (name, description) VALUES 
('Audio Evaluation', 'Assessment of audio quality and transcription'),
('Image Evaluation', 'Assessment of image classification and tagging'),
('Text Evaluation', 'Assessment of text analysis and summarization'),
('Data Quality', 'Assessment of data accuracy and completeness');

-- Sample Rating Scales
INSERT INTO rating_scales (min_score, max_score, rating_label, color_code) VALUES 
(90, 100, 'Excellent', '#10B981'),
(80, 89, 'Very Good', '#3B82F6'),
(70, 79, 'Good', '#2563EB'),
(60, 69, 'Average', '#F59E0B'),
(0, 59, 'Needs Improvement', '#EF4444');

-- Sample Salary Template
INSERT INTO salary_templates (name, basic_salary, hra, special_allowance, conveyance_allowance, performance_bonus, pf_deduction, esi_deduction, professional_tax, tds_tax, other_deductions) 
VALUES ('Standard Package', 50000, 25000, 10000, 3000, 5000, 6000, 1500, 200, 500, 100);

-- Sample Holiday
INSERT INTO holidays (name, date, description) VALUES 
('Republic Day', '2025-01-26', 'National Holiday');

-- Sample Announcement
INSERT INTO announcements (title, content, priority, target_audience, created_by) 
VALUES ('Welcome to AI Square Hub', 'Welcome to our new internal platform! Please explore the features.', 'normal', 'all_employees', 1);

-- Commit transaction
COMMIT;
