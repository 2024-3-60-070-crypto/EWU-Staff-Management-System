<?php
include 'db_connect.php';
require_login();
$active = 'staff';
$page_title = 'Staff Profile';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!is_admin() && $id !== (int)$_SESSION['staff_id']) {
    include 'partials/header.php';
    echo '<div class="alert alert-error">You can only view your own profile. Duties and leave records are private.</div>';
    echo '<p><a class="btn btn-ghost" href="view_staff.php?id=' . (int)$_SESSION['staff_id'] . '">View My Profile</a></p>';
    include 'partials/footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT s.*, d.Dept_Type, d.Location FROM staff s JOIN department d ON s.Dept_Name = d.Dept_Name WHERE s.Staff_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$staff) {
    include 'partials/header.php';
    echo '<div class="alert alert-error">Staff member not found.</div>';
    echo '<p><a class="btn btn-ghost" href="staff_details.php">Back to Staff Details</a></p>';
    include 'partials/footer.php';
    exit;
}

$duty_stmt = $conn->prepare("SELECT * FROM assigned_duties WHERE Staff_ID = ? ORDER BY Assigned_Date DESC");
$duty_stmt->bind_param("i", $id);
$duty_stmt->execute();
$duties = $duty_stmt->get_result();

$leave_stmt = $conn->prepare("SELECT * FROM leaves WHERE Staff_ID = ? ORDER BY Leave_Date DESC");
$leave_stmt->bind_param("i", $id);
$leave_stmt->execute();
$leaves = $leave_stmt->get_result();

$salary = null;
if (is_admin()) {
    $sal_stmt = $conn->prepare("SELECT * FROM salary WHERE Staff_ID = ?");
    $sal_stmt->bind_param("i", $id);
    $sal_stmt->execute();
    $salary = $sal_stmt->get_result()->fetch_assoc();
    $sal_stmt->close();
}
include 'partials/header.php';
?>

<h2 class="section-title">Staff Profile</h2>
<p><a class="btn btn-ghost" href="staff_details.php">&larr; Back to Staff Details</a></p>

<div class="profile-grid">
  <div class="card profile-card">
    <h3><?php echo e($staff['Staff_Name']); ?>
      <?php echo ((int)$staff['Is_Admin'] === 1) ? '<span class="badge badge-admin">Admin</span>' : ''; ?>
    </h3>
    <p class="muted"><?php echo e($staff['Employee_Type']); ?> &middot; <?php echo e($staff['Dept_Name']); ?></p>
    <table class="info-table">
      <tr><th>Staff ID</th><td><?php echo e($staff['Staff_ID']); ?></td></tr>
      <tr><th>Gender</th><td><?php echo e($staff['Gender']); ?></td></tr>
      <tr><th>Date of Birth</th><td><?php echo e($staff['Date_of_Birth']); ?></td></tr>
      <tr><th>Date of Join</th><td><?php echo e($staff['Date_of_Join']); ?></td></tr>
      <tr><th>Phone</th><td><?php echo e($staff['Phone_Number']); ?></td></tr>
      <tr><th>Email</th><td><?php echo e($staff['Email_Address']); ?></td></tr>
      <tr><th>Address</th><td><?php echo e($staff['Address']); ?></td></tr>
      <tr><th>Department Type</th><td><?php echo e($staff['Dept_Type']); ?></td></tr>
      <tr><th>Location</th><td><?php echo e($staff['Location']); ?></td></tr>
    </table>
  </div>

  <?php if ($salary) { ?>
    <div class="card profile-card">
      <h3>Salary Details <span class="badge badge-admin">Admin Only</span></h3>
      <table class="info-table">
        <tr><th>Basic Pay</th><td><?php echo number_format($salary['Basic_Pay'], 2); ?> BDT</td></tr>
        <tr><th>DA (Dearness Allowance)</th><td><?php echo number_format($salary['DA'], 2); ?> BDT</td></tr>
        <tr><th>HRA (House Rent Allowance)</th><td><?php echo number_format($salary['HRA'], 2); ?> BDT</td></tr>
        <tr><th>Medical Allowance</th><td><?php echo number_format($salary['Medical_Allow'], 2); ?> BDT</td></tr>
        <tr><th>Child Education</th><td><?php echo number_format($salary['Child_Education'], 2); ?> BDT</td></tr>
        <tr class="total-row"><th>Total Salary</th><td><strong><?php echo number_format($salary['Total_Salary'], 2); ?> BDT</strong></td></tr>
      </table>
    </div>
  <?php } ?>
</div>

<div class="card">
  <h3>Assigned Duties</h3>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Duty Type</th><th>Course / Duty</th><th>Description</th><th>Assigned Date</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php $has = false;
        while ($d = $duties->fetch_assoc()) { $has = true; ?>
          <tr>
            <td><span class="badge <?php echo $d['Duty_Type'] === 'Teaching' ? 'badge-faculty' : 'badge-office'; ?>"><?php echo e($d['Duty_Type']); ?></span></td>
            <td><?php echo e($d['Course_Name']); ?></td>
            <td><?php echo e($d['Duty_Description'] ?: '-'); ?></td>
            <td><?php echo e($d['Assigned_Date']); ?></td>
            <td><?php echo e($d['Status']); ?></td>
          </tr>
        <?php }
        if (!$has) { echo '<tr><td colspan="5" class="empty-cell">No duties assigned.</td></tr>'; } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <h3>Leave Records</h3>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Leave Date</th><th>No. of Days</th><th>Leave Type</th><th>Reason</th></tr>
      </thead>
      <tbody>
        <?php $has = false;
        while ($l = $leaves->fetch_assoc()) { $has = true; ?>
          <tr>
            <td><?php echo e($l['Leave_Date']); ?></td>
            <td><?php echo (int)$l['No_of_Days']; ?></td>
            <td><?php echo e($l['Leave_Type']); ?></td>
            <td><?php echo e($l['Reason'] ?: '-'); ?></td>
          </tr>
        <?php }
        if (!$has) { echo '<tr><td colspan="4" class="empty-cell">No leave records.</td></tr>'; } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
