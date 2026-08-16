<?php
include 'db_connect.php';
require_login();
$active = 'leave';
$page_title = 'Leave Records';

$leave_type = trim($_GET['leave_type'] ?? '');

$where = array();
$params = array();
$types = "";

if ($leave_type !== "") {
    $where[] = "l.Leave_Type = ?";
    $params[] = $leave_type;
    $types .= "s";
}

$sql = "SELECT l.*, s.Staff_Name, s.Gender, s.Date_of_Join, s.Phone_Number, s.Email_Address, s.Employee_Type, d.Dept_Name, d.Dept_Type
        FROM leaves l
        JOIN staff s ON l.Staff_ID = s.Staff_ID
        JOIN department d ON s.Dept_Name = d.Dept_Name";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY l.Leave_Date DESC";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total = $result->num_rows;
include 'partials/header.php';
?>

<h2 class="section-title">Leave Records</h2>

<div class="card filter-card">
  <form method="get" action="leave.php" class="filter-form">
    <div class="form-group">
      <label for="leave_type">Leave Type</label>
      <select id="leave_type" name="leave_type">
        <option value="">All Types</option>
        <option value="Casual" <?php echo $leave_type === 'Casual' ? 'selected' : ''; ?>>Casual</option>
        <option value="Sick" <?php echo $leave_type === 'Sick' ? 'selected' : ''; ?>>Sick</option>
        <option value="Earned" <?php echo $leave_type === 'Earned' ? 'selected' : ''; ?>>Earned</option>
        <option value="Academic" <?php echo $leave_type === 'Academic' ? 'selected' : ''; ?>>Academic</option>
      </select>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a class="btn btn-ghost" href="leave.php">Reset</a>
    </div>
  </form>
</div>

<p class="result-count"><?php echo $total; ?> leave record(s) found.</p>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Staff ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Date of Join</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Department</th>
        <th>Employee Type</th>
        <th>Leave Date</th>
        <th>Days</th>
        <th>Leave Type</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($total > 0) {
        while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo (int)$row['Staff_ID']; ?></td>
            <td><a class="link" href="view_staff.php?id=<?php echo (int)$row['Staff_ID']; ?>"><strong><?php echo e($row['Staff_Name']); ?></strong></a></td>
            <td><?php echo e($row['Gender']); ?></td>
            <td><?php echo e($row['Date_of_Join']); ?></td>
            <td><?php echo e($row['Phone_Number']); ?></td>
            <td><?php echo e($row['Email_Address']); ?></td>
            <td><?php echo e($row['Dept_Name']); ?> (<?php echo e($row['Dept_Type']); ?>)</td>
            <td><?php echo e($row['Employee_Type']); ?></td>
            <td><?php echo e($row['Leave_Date']); ?></td>
            <td><?php echo (int)$row['No_of_Days']; ?></td>
            <td><span class="badge badge-leave"><?php echo e($row['Leave_Type']); ?></span></td>
            <td><?php echo e($row['Reason'] ?: '-'); ?></td>
          </tr>
        <?php }
      } else { ?>
        <tr><td colspan="12" class="empty-cell">No leave records found.</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include 'partials/footer.php'; ?>
