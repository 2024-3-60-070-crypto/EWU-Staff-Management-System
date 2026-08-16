A complete web-based Staff Management System developed as a Database Systems (CSE302) project. It digitizes the administration of East West University's faculty and administrative staff with a normalized relational database (staff, department, assigned_duties, salary, leave tables).

Features:

Role-based login/logout with secure password hashing (Admin & Staff roles)
Add staff with personal, job, salary and initial duty info in one transaction
Searchable/filterable staff directory (name, ID, e-mail, phone, department)
Staff profiles with duties and leave history (salary admin-only)
Assign teaching courses / administrative duties
Leave register with leave types and day counts
Salary report with per-staff breakdown, total payroll & average statistics
Security: prepared statements (SQL injection safe), CSRF protection, XSS-safe output escaping
Tech: PHP 8, MySQL, Apache (XAMPP), HTML5/CSS3. DB: staff_management.
