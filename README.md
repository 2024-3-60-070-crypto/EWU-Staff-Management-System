# East West University Staff Management System

A complete web-based Staff Management System developed as a **Database Systems (CSE302)** project. It digitizes the administration of East West University's faculty and administrative staff using a normalized relational database.

## Features

- **Role-based login/logout** with secure password hashing (Admin & Staff roles)
- **Add staff** with personal, job, salary and initial duty info in a single transaction
- **Searchable staff directory** with filters for name, ID, e-mail, phone and department
- **Staff profiles** showing duties and leave history (salary visible to admins only)
- **Assign duties** — teaching courses or administrative tasks
- **Leave register** with leave types and day counts
- **Salary report** with per-staff breakdown, total payroll and average salary statistics
- **Security** — prepared statements (SQL-injection safe), CSRF token protection, XSS-safe output escaping

## Tech Stack

| Layer       | Technology      |
|-------------|-----------------|
| Backend     | PHP 8           |
| Database    | MySQL           |
| Web Server  | Apache (XAMPP)  |
| Frontend    | HTML5, CSS3     |

**Database:** `staff_management`
