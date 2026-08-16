<?php
include 'db_connect.php';
require_login();
$active = 'home';
$page_title = 'Home';

$notice = "";
if (isset($_GET['error']) && $_GET['error'] == 1) {
    $notice = "You do not have permission to access that page.";
}
include 'partials/header.php';
?>
<div class="hero">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1>Welcome, <?php echo e($_SESSION['staff_name']); ?></h1>
    <p>Department: <?php echo e($_SESSION['dept_name']); ?> &middot; Role: <?php echo is_admin() ? 'System Administrator' : 'Staff Member'; ?></p>
  </div>
</div>

<?php if ($notice !== "") { echo '<div class="alert alert-error">' . e($notice) . '</div>'; } ?>

<h2 class="section-title">Dashboard</h2>
<div class="cards-grid">
  <a class="card card-link" href="staff_details.php">
    <h3>Staff Details</h3>
    <p>View the complete list of all university staff with search and filter options.</p>
  </a>
  <a class="card card-link" href="duties.php">
    <h3>Assigned Duties</h3>
    <p>View courses taught by faculty and administrative duties of office staff.</p>
  </a>
  <a class="card card-link" href="leave.php">
    <h3>Leave Records</h3>
    <p>View all staff leave records joined with their full details.</p>
  </a>

  <?php if (is_admin()) { ?>
    <a class="card card-link admin" href="add_staff.php">
      <h3>Add Staff</h3>
      <p>Register a new staff member with personal, salary and duty information.</p>
    </a>
    <a class="card card-link admin" href="add_duty.php">
      <h3>Add Duty</h3>
      <p>Assign a teaching course or administrative duty to a staff member.</p>
    </a>
    <a class="card card-link admin" href="add_leave.php">
      <h3>Add Leave</h3>
      <p>Record a leave entry for any staff member.</p>
    </a>
    <a class="card card-link admin" href="salary.php">
      <h3>View Salaries</h3>
      <p>Admin only: view salary breakdown and total payroll of all staff.</p>
    </a>
  <?php } else { ?>
    <div class="card card-locked">
      <h3>Add Staff <span class="badge badge-admin">Admin Only</span></h3>
      <p>Only the system administrator can add new staff members.</p>
    </div>
    <div class="card card-locked">
      <h3>View Salaries <span class="badge badge-admin">Admin Only</span></h3>
      <p>Salary information is restricted to the system administrator.</p>
    </div>
  <?php } ?>
</div>

<?php include 'partials/footer.php'; ?>
