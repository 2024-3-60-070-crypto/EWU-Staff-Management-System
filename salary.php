<?php
include 'db_connect.php';
require_admin();
$active = 'salary';
$page_title = 'Salary Records';

$sql = "SELECT sa.*, s.Staff_Name, s.Employee_Type, s.Staff_ID, d.Dept_Name
        FROM salary sa
        JOIN staff s ON sa.Staff_ID = s.Staff_ID
        JOIN department d ON s.Dept_Name = d.Dept_Name
        ORDER BY sa.Total_Salary DESC";

$result = $conn->query($sql);
$total = $result->num_rows;

$sum_stmt = $conn->query("SELECT COUNT(*) AS staff_count, SUM(Total_Salary) AS total_payroll, AVG(Total_Salary) AS avg_salary FROM salary");
$stats = $sum_stmt->fetch_assoc();
include 'partials/header.php';
?>

<h2 class="section-title">Salary Records <span class="badge badge-admin">Admin Only</span></h2>

<div class="stats-row">
  <div class="stat-card">
    <span class="stat-label">Staff on Payroll</span>
    <span class="stat-value"><?php echo number_format((int)$stats['staff_count']); ?></span>
  </div>
  <div class="stat-card">
    <span class="stat-label">Total Monthly Payroll</span>
    <span class="stat-value"><?php echo number_format((float)$stats['total_payroll'], 2); ?> BDT</span>
  </div>
  <div class="stat-card">
    <span class="stat-label">Average Salary</span>
    <span class="stat-value"><?php echo number_format((float)$stats['avg_salary'], 2); ?> BDT</span>
  </div>
</div>

<div class="table-wrap">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Department</th>
        <th>Employee Type</th>
        <th>Basic Pay</th>
        <th>DA</th>
        <th>HRA</th>
        <th>Medical</th>
        <th>Child Edu</th>
        <th>Total Salary</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($total > 0) {
        while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo (int)$row['Staff_ID']; ?></td>
            <td><a class="link" href="view_staff.php?id=<?php echo (int)$row['Staff_ID']; ?>"><strong><?php echo e($row['Staff_Name']); ?></strong></a></td>
            <td><?php echo e($row['Dept_Name']); ?></td>
            <td><?php echo e($row['Employee_Type']); ?></td>
            <td><?php echo number_format($row['Basic_Pay'], 2); ?></td>
            <td><?php echo number_format($row['DA'], 2); ?></td>
            <td><?php echo number_format($row['HRA'], 2); ?></td>
            <td><?php echo number_format($row['Medical_Allow'], 2); ?></td>
            <td><?php echo number_format($row['Child_Education'], 2); ?></td>
            <td class="total-cell"><strong><?php echo number_format($row['Total_Salary'], 2); ?> BDT</strong></td>
          </tr>
        <?php }
      } else { ?>
        <tr><td colspan="10" class="empty-cell">No salary records found.</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include 'partials/footer.php'; ?>
