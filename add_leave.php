<?php
include 'db_connect.php';
require_admin();
$active = 'add';
$page_title = 'Add Leave';

$errors = array();
$success = "";

$old = array(
    'staff_id' => '', 'leave_date' => '', 'no_of_days' => '1',
    'leave_type' => 'Casual', 'reason' => ''
);

function clean_date($value) {
    $value = trim($value);
    if ($value === '') return '';
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    foreach (array_keys($old) as $key) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['staff_id'] === '' || (int)$old['staff_id'] <= 0) { $errors[] = "Please select a staff member."; }
    if ($old['leave_date'] === '' || clean_date($old['leave_date']) === '') { $errors[] = "A valid leave date is required."; }
    if ($old['no_of_days'] === '' || (int)$old['no_of_days'] < 1 || (int)$old['no_of_days'] > 365) { $errors[] = "Number of days must be between 1 and 365."; }
    if (!in_array($old['leave_type'], array('Casual', 'Sick', 'Earned', 'Academic'))) { $errors[] = "Invalid leave type."; }

    if (count($errors) === 0) {
        $staff_id = (int)$old['staff_id'];
        $staff_check = $conn->prepare("SELECT Staff_ID FROM staff WHERE Staff_ID = ?");
        $staff_check->bind_param("i", $staff_id);
        $staff_check->execute();
        if ($staff_check->get_result()->num_rows === 0) { $errors[] = "Selected staff member does not exist."; }
        $staff_check->close();
    }

    if (count($errors) === 0) {
        $staff_id = (int)$old['staff_id'];
        $no_of_days = (int)$old['no_of_days'];
        $stmt = $conn->prepare("INSERT INTO leaves (Staff_ID, Leave_Date, No_of_Days, Leave_Type, Reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $staff_id, $old['leave_date'], $no_of_days, $old['leave_type'], $old['reason']);
        if ($stmt->execute()) {
            $success = "Leave record added successfully.";
        } else {
            if ($stmt->errno === 1062) {
                $errors[] = "A leave record already exists for this staff member on this date.";
            } else {
                $errors[] = "Database error while saving. Please try again.";
            }
        }
        $stmt->close();
    }
}

$staff_result = $conn->query("SELECT Staff_ID, Staff_Name, Dept_Name FROM staff ORDER BY Staff_Name");
include 'partials/header.php';
?>

<h2 class="section-title">Add Leave <span class="badge badge-admin">Admin Only</span></h2>

<?php if ($success !== "") { echo '<div class="alert alert-success">' . e($success) . '</div>'; } ?>

<?php if (count($errors) > 0) { ?>
  <div class="alert alert-error">
    <ul>
      <?php foreach ($errors as $err) { echo "<li>" . e($err) . "</li>"; } ?>
    </ul>
  </div>
<?php } ?>

<form method="post" action="add_leave.php" class="form-card card">
  <?php echo csrf_field(); ?>
  <div class="form-grid">
    <div class="form-group">
      <label for="staff_id">Staff Member *</label>
      <select id="staff_id" name="staff_id" required>
        <option value="">Select Staff</option>
        <?php while ($s = $staff_result->fetch_assoc()) { ?>
          <option value="<?php echo (int)$s['Staff_ID']; ?>" <?php echo $old['staff_id'] !== '' && (int)$old['staff_id'] === (int)$s['Staff_ID'] ? 'selected' : ''; ?>>
            <?php echo e($s['Staff_Name']); ?> (<?php echo e($s['Dept_Name']); ?>)
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="form-group">
      <label for="leave_date">Leave Date *</label>
      <input type="date" id="leave_date" name="leave_date" value="<?php echo e($old['leave_date']); ?>" required>
    </div>
    <div class="form-group">
      <label for="no_of_days">Number of Days *</label>
      <input type="number" id="no_of_days" name="no_of_days" min="1" max="365" value="<?php echo e($old['no_of_days']); ?>" required>
    </div>
    <div class="form-group">
      <label for="leave_type">Leave Type *</label>
      <select id="leave_type" name="leave_type">
        <option value="Casual" <?php echo $old['leave_type'] === 'Casual' ? 'selected' : ''; ?>>Casual</option>
        <option value="Sick" <?php echo $old['leave_type'] === 'Sick' ? 'selected' : ''; ?>>Sick</option>
        <option value="Earned" <?php echo $old['leave_type'] === 'Earned' ? 'selected' : ''; ?>>Earned</option>
        <option value="Academic" <?php echo $old['leave_type'] === 'Academic' ? 'selected' : ''; ?>>Academic</option>
      </select>
    </div>
    <div class="form-group form-full">
      <label for="reason">Reason (optional)</label>
      <textarea id="reason" name="reason" rows="2"><?php echo e($old['reason']); ?></textarea>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Add Leave</button>
    <a class="btn btn-ghost" href="home.php">Cancel</a>
  </div>
</form>

<?php include 'partials/footer.php'; ?>
