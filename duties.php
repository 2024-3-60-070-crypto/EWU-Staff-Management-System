<?php
include 'db_connect.php';
require_login();
$active = 'duties';
$page_title = 'Assigned Duties';

$status = trim($_GET['status'] ?? '');
$duty_type = trim($_GET['duty_type'] ?? '');

$where = array();
$params = array();
$types = "";

if ($status !== "") {
    $where[] = "ad.Status = ?";
    $params[] = $status;
    $types .= "s";
}
if ($duty_type !== "") {
    $where[] = "ad.Duty_Type = ?";
    $params[] = $duty_type;
    $types .= "s";
}
if (!is_admin()) {
    $where[] = "ad.Staff_ID = ?";
    $params[] = $_SESSION['staff_id'];
    $types .= "i";
}

$sql = "SELECT ad.*, s.Staff_Name, s.Gender, s.Phone_Number, s.Email_Address, d.Dept_Name
        FROM assigned_duties ad
        JOIN staff s ON ad.Staff_ID = s.Staff_ID
        JOIN department d ON s.Dept_Name = d.Dept_Name";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY ad.Assigned_Date DESC, s.Staff_ID";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total = $result->num_rows;
include 'partials/header.php';
?>

<?php if (!is_admin()) { ?>
  <div class="alert alert-info">Showing your own assigned duties only.</div>
<?php } ?>

<h2 class="section-title">Assigned Duties</h2>

<div class="card filter-card">
  <form method="get" action="duties.php" class="filter-form">
    <div class="form-group">
      <label for="duty_type">Duty Type</label>
      <select id="duty_type" name="duty_type">
        <option value="">All Types</option>
        <option value="Teaching" <?php echo $duty_type === 'Teaching' ? 'selected' : ''; ?>>Teaching</option>
        <option value="Administrative" <?php echo $duty_type === 'Administrative' ? 'selected' : ''; ?>>Administrative</option>
      </select>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="">All Status</option>
        <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
        <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
        <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
      </select>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a class="btn btn-ghost" href="duties.php">Reset</a>
    </div>
  </form>
</div>

<p class="result-count"><?php echo $total; ?> duty record(s) found.</p>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>Staff</th>
        <th>Department</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Duty Type</th>
        <th>Course / Duty</th>
        <th>Description</th>
        <th>Assigned Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($total > 0) {
        while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><a class="link" href="view_staff.php?id=<?php echo (int)$row['Staff_ID']; ?>"><strong><?php echo e($row['Staff_Name']); ?></strong></a></td>
            <td><?php echo e($row['Dept_Name']); ?></td>
            <td><?php echo e($row['Phone_Number']); ?></td>
            <td><?php echo e($row['Email_Address']); ?></td>
            <td><span class="badge <?php echo $row['Duty_Type'] === 'Teaching' ? 'badge-faculty' : 'badge-office'; ?>"><?php echo e($row['Duty_Type']); ?></span></td>
            <td><?php echo e($row['Course_Name']); ?></td>
            <td><?php echo e($row['Duty_Description'] ?: '-'); ?></td>
            <td><?php echo e($row['Assigned_Date']); ?></td>
            <td>
              <span class="badge <?php
                if ($row['Status'] === 'Active') { echo 'badge-active'; }
                elseif ($row['Status'] === 'Completed') { echo 'badge-completed'; }
                else { echo 'badge-pending'; }
              ?>"><?php echo e($row['Status']); ?></span>
            </td>
          </tr>
        <?php }
      } else { ?>
        <tr><td colspan="9" class="empty-cell">No duty records found.</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include 'partials/footer.php'; ?>
