# AI SQUARE HUB - Internal Payroll, AI Evaluation & Recruitment Platform

## Project Overview
**Platform Name:** AI SQUARE HUB  
**Company Name:** AI SQUARE IT SOLUTIONS  
**Tagline:** Build • Evaluate • Grow Together  

## Tech Stack (InfinityFree Compatible)
- **Backend:** PHP 8.1+ (Procedural/Modular OOP with PDO)
- **Database:** MySQL / MariaDB (InnoDB, utf8mb4_unicode_ci)
- **Frontend:** HTML5, CSS3, Bootstrap 5.3 (CDN), Bootstrap/Lucide Icons (CDN)
- **Charts:** Chart.js (CDN)
- **PDF Export:** html2pdf.js / jsPDF (CDN)
- **Excel/CSV Parsing:** SheetJS (CDN)
- **SMTP:** Pure-PHP SMTP Client (Self-contained)

## Folder Structure
```
/
├── index.php                  # Unified Login Gateway
├── logout.php
├── .htaccess                  # Security headers, directory indexing off
├── config/
│   ├── config.example.php     # DB + base URL + timezone config template
│   └── database.php           # PDO singleton connection
├── includes/
│   ├── auth.php               # Session, CSRF, RBAC & anti-IDOR guards
│   ├── helpers.php            # Sanitizers, INR number-to-words, date & badge helpers
│   ├── mailer.php             # Self-contained SMTP mailer + in-app notification dispatcher
│   ├── header.php             # Shared head, theme loader, CSS vars, SVG logo
│   └── footer.php             # Shared JS, Chart.js, SheetJS, html2pdf, theme toggle
├── assets/
│   ├── css/app.css            # Modern SaaS Light/Dark design system
│   ├── js/app.js              # Theme switcher, toasts, CSV/XLSX parser, PDF export
│   └── img/logo.svg           # Custom AI Square IT Solutions vector logo
├── admin/                     # Admin Hub pages & AJAX endpoints
├── employee/                  # Employee Hub pages & AJAX endpoints
├── candidate/                 # Candidate Hub pages & AJAX endpoints
├── uploads/                   # Avatars, company logo, test images/audio (protected)
├── sql/
│   └── schema.sql             # Complete MySQL schema + demo seed accounts
└── README.md                  # Local XAMPP setup + InfinityFree deployment guide
```

## Deployment Guide

### 1. Local XAMPP/Laragon Setup
1. Place all files in `htdocs/`
2. Create MySQL database and import `sql/schema.sql`
3. Update `config/config.php` with DB credentials
4. Access `http://localhost/` for Unified Login Gateway

### 2. InfinityFree Deployment
1. Upload all files to `htdocs/` via FTP/File Manager
2. Create MySQL database via Control Panel
3. Import `sql/schema.sql` via phpMyAdmin
4. Update `config/config.php` with InfinityFree DB credentials
5. Access `https://yourdomain.rf.gd/` for Unified Login Gateway

### 3. Security Notes
- `.htaccess` protects upload directories from PHP execution
- All passwords hashed with BCRYPT
- CSRF tokens enforced on state-changing requests
- Anti-IDOR protection implemented via session binding
- File uploads limited to 8MB (InfinityFree safety)

## Portals
1. **Admin Hub** — Central administration, payroll, evaluation & recruitment
2. **Employee Hub** — Personal dashboard, payslips, attendance & feedback
3. **Candidate Hub** — Secure recruitment examinations

## Branding
- Primary Colors: Royal Blue (#2563EB), Deep Navy (#0F172A)
- Success: Emerald (#10B981) | Warning: Amber (#F59E0B) | Error: Rose (#EF4444)
- Theme Support: Light/Dark Mode with instant toggle
- Typography: Inter / Plus Jakarta Sans (Google Fonts)

## Database Schema
Complete schema available in `sql/schema.sql` covering:
- Company settings & branding
- Admins & RBAC roles
- Employees (no department/project fields)
- Salary templates & payroll engine
- Attendance & leave management
- AI evaluation categories & scores
- Candidate recruitment & test builder
- Announcements & notifications
- Feedback management (anonymous support)

## Security Features
- Password hashing (BCRYPT)
- Session regeneration on login
- CSRF token validation
- Anti-IDOR protection
- Upload directory protection
- HTML output escaping
- InfinityFree compatibility (8MB file limit, no Composer)

## Usage Instructions
1. Configure `config/config.php` with database credentials
2. Import `sql/schema.sql` into MySQL
3. Access `index.php` for Unified Login Gateway
4. Use Admin credentials: `superadmin` / `admin@aisquare.com` / password: `password123` (change after first login)
5. Use Employee credentials: `AISQ-001` / `rahul@aisquare.com` / password: `password123`
6. Use Candidate credentials: `AISQ-CAND-001` / `priya@example.com` / password: `password123`

## Portals Access
- **Admin Hub:** `/admin/dashboard.php`
- **Employee Hub:** `/employee/dashboard.php`
- **Candidate Hub:** `/candidate/dashboard.php`
- **Unified Login:** `/index.php` (with portal switcher)

## Development Notes
- All files are compatible with InfinityFree shared hosting
- No SSH or root access required
- No Composer CLI dependency
- Clean MVC/Modular OOP structure
- Responsive design for mobile/tablet/desktop
- Light/Dark theme support
- PDF payslip generation via client-side html2pdf.js
- Excel/CSV bulk upload via SheetJS
- Pure-PHP SMTP client for email delivery

## Contact
For support or customization, contact AI SQUARE IT SOLUTIONS team.
