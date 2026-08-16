<?php
include 'db_connect.php';
require_login();
$active = 'staff';
$page_title = 'Staff Details';

$term = trim($_GET['search'] ?? '');
$dept = trim($_GET['dept'] ?? '');

$where = array();
$params = array();
$types = "";

if ($term !== "") {
    $like = "%" . $term . "%";
    $where[] = "(s.Staff_Name LIKE ? OR s.Staff_ID LIKE ? OR s.Email_Address LIKE ? OR s.Phone_Number LIKE ?)";
    array_push($params, $like, $like, $like, $like);
    $types .= "ssss";
}
if ($dept !== "") {
    $where[] = "s.Dept_Name = ?";
    $params[] = $dept;
    $types .= "s";
}

$sql = "SELECT s.*, d.Dept_Type, d.Location
        FROM staff s
        JOIN department d ON s.Dept_Name = d.Dept_Name";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY s.Staff_ID";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total = $result->num_rows;

$dept_result = $conn->query("SELECT Dept_Name FROM department ORDER BY Dept_Name");
include 'partials/header.php';
?>

<h2 class="section-title">Staff Details</h2>

<div class="card filter-card">
  <form method="get" action="staff_details.php" class="filter-form">
    <div class="form-group">
      <label for="search">Search</label>
      <input type="text" id="search" name="search" value="<?php echo e($term); ?>"
             placeholder="Name, ID, email or phone...">
    </div>
    <div class="form-group">
      <label for="dept">Department</label>
      <select id="dept" name="dept">
        <option value="">All Departments</option>
        <?php while ($d = $dept_result->fetch_assoc()) { ?>
          <option value="<?php echo e($d['Dept_Name']); ?>" <?php echo $dept === $d['Dept_Name'] ? 'selected' : ''; ?>>
            <?php echo e($d['Dept_Name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Search</button>
      <a class="btn btn-ghost" href="staff_details.php">Reset</a>
    </div>
  </form>
</div>

<p class="result-count"><?php echo $total; ?> staff member(s) found.</p>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>DOB</th>
        <th>Date of Join</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Employee Type</th>
        <th>Department</th>
        <th>Location</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($total > 0) {
        while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo e($row['Staff_ID']); ?></td>
            <td><strong><?php echo e($row['Staff_Name']); ?></strong></td>
            <td><?php echo e($row['Gender']); ?></td>
            <td><?php echo e($row['Date_of_Birth']); ?></td>
            <td><?php echo e($row['Date_of_Join']); ?></td>
            <td><?php echo e($row['Phone_Number']); ?></td>
            <td><?php echo e($row['Email_Address']); ?></td>
            <td><span class="badge <?php echo $row['Employee_Type'] === 'Faculty' ? 'badge-faculty' : 'badge-office'; ?>"><?php echo e($row['Employee_Type']); ?></span></td>
            <td><?php echo e($row['Dept_Name']); ?></td>
            <td><?php echo e($row['Location']); ?></td>
            <td><?php echo ((int)$row['Is_Admin'] === 1) ? '<span class="badge badge-admin">Admin</span>' : '<span class="badge badge-staff">Staff</span>'; ?></td>
            <td><a class="btn btn-sm btn-accent" href="view_staff.php?id=<?php echo (int)$row['Staff_ID']; ?>">View</a></td>
          </tr>
        <?php }
      } else { ?>
        <tr><td colspan="12" class="empty-cell">No staff members found.</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include 'partials/footer.php'; ?>
