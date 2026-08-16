<?php
include 'db_connect.php';
require_admin();
$active = 'add';
$page_title = 'Add Duty';

$errors = array();
$success = "";

$old = array(
    'staff_id' => '', 'duty_type' => 'Teaching', 'course_name' => '',
    'duty_desc' => '', 'assigned_date' => '', 'status' => 'Active'
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
    if (!in_array($old['duty_type'], array('Teaching', 'Administrative'))) { $errors[] = "Invalid duty type."; }
    if ($old['course_name'] === '') { $errors[] = "Course name / duty title is required."; }
    if ($old['assigned_date'] === '' || clean_date($old['assigned_date']) === '') { $errors[] = "A valid assigned date is required."; }
    if (!in_array($old['status'], array('Active', 'Completed', 'Pending'))) { $errors[] = "Invalid status."; }

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
        $stmt = $conn->prepare("INSERT INTO assigned_duties (Staff_ID, Duty_Type, Course_Name, Duty_Description, Assigned_Date, Status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $staff_id, $old['duty_type'], $old['course_name'], $old['duty_desc'], $old['assigned_date'], $old['status']);
        if ($stmt->execute()) {
            $success = "Duty assigned successfully.";
        } else {
            if ($stmt->errno === 1062) {
                $errors[] = "This duty already exists for the selected staff member (same duty type and course).";
            } else {
                $errors[] = "Database error while saving. Please try again.";
            }
        }
        $stmt->close();
    }
}

$staff_result = $conn->query("SELECT Staff_ID, Staff_Name, Employee_Type, Dept_Name FROM staff ORDER BY Staff_Name");
include 'partials/header.php';
?>

<h2 class="section-title">Add Duty <span class="badge badge-admin">Admin Only</span></h2>

<?php if ($success !== "") { echo '<div class="alert alert-success">' . e($success) . '</div>'; } ?>

<?php if (count($errors) > 0) { ?>
  <div class="alert alert-error">
    <ul>
      <?php foreach ($errors as $err) { echo "<li>" . e($err) . "</li>"; } ?>
    </ul>
  </div>
<?php } ?>

<form method="post" action="add_duty.php" class="form-card card">
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
      <label for="duty_type">Duty Type *</label>
      <select id="duty_type" name="duty_type">
        <option value="Teaching" <?php echo $old['duty_type'] === 'Teaching' ? 'selected' : ''; ?>>Teaching</option>
        <option value="Administrative" <?php echo $old['duty_type'] === 'Administrative' ? 'selected' : ''; ?>>Administrative</option>
      </select>
    </div>
    <div class="form-group">
      <label for="course_name">Course Name (Teaching) / Duty Title (Administrative) *</label>
      <input type="text" id="course_name" name="course_name" value="<?php echo e($old['course_name']); ?>" required>
    </div>
    <div class="form-group form-full">
      <label for="duty_desc">Duty Description</label>
      <textarea id="duty_desc" name="duty_desc" rows="2"><?php echo e($old['duty_desc']); ?></textarea>
    </div>
    <div class="form-group">
      <label for="assigned_date">Assigned Date *</label>
      <input type="date" id="assigned_date" name="assigned_date" value="<?php echo e($old['assigned_date']); ?>" required>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="Active" <?php echo $old['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
        <option value="Completed" <?php echo $old['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
        <option value="Pending" <?php echo $old['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Assign Duty</button>
    <a class="btn btn-ghost" href="home.php">Cancel</a>
  </div>
</form>

<?php include 'partials/footer.php'; ?>
