<?php
include 'db_connect.php';
require_admin();
$active = 'add';
$page_title = 'Add Staff';

$errors = array();
$success = "";
$generated_pass = "";

$old = array(
    'staff_name' => '', 'gender' => 'Male', 'dob' => '', 'doj' => '',
    'phone' => '', 'email' => '', 'address' => '', 'employee_type' => 'Faculty',
    'dept' => '', 'basic' => '', 'da' => '0', 'hra' => '0', 'medical' => '0',
    'child_edu' => '0', 'duty_type' => 'Teaching', 'course_name' => '',
    'duty_desc' => '', 'assigned_date' => '', 'status' => 'Active'
);

function clean_date($value) {
    $value = trim($value);
    if ($value === '') return '';
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
}

function default_password($name) {
    $tokens = preg_split('/\s+/', trim($name));
    $skip = array('dr', 'dr.', 'prof', 'prof.', 'md', 'md.', 'mrs', 'mrs.', 'ms', 'ms.', 'phd', 'ph.d');
    foreach ($tokens as $token) {
        $clean = preg_replace('/[^a-zA-Z]/', '', $token);
        $lower = strtolower($clean);
        if ($clean !== '' && !in_array($lower, $skip)) {
            return '123' . $clean;
        }
    }
    return '123staff';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();

    foreach (array_keys($old) as $key) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['staff_name'] === '') { $errors[] = "Staff name is required."; }
    if (!in_array($old['gender'], array('Male', 'Female', 'Other'))) { $errors[] = "Invalid gender."; }
    if ($old['dob'] === '' || clean_date($old['dob']) === '') { $errors[] = "A valid date of birth (YYYY-MM-DD) is required."; }
    if ($old['doj'] === '' || clean_date($old['doj']) === '') { $errors[] = "A valid date of join (YYYY-MM-DD) is required."; }
    if ($old['phone'] === '' || !preg_match('/^\d{8,10}$/', $old['phone'])) { $errors[] = "Phone number must be 8-10 digits after +880."; }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/@ewubd\.edu$/i', $old['email'])) { $errors[] = "Email must be a valid address ending with @ewubd.edu."; }
    if ($old['address'] === '') { $errors[] = "Address is required."; }
    if (!in_array($old['employee_type'], array('Faculty', 'Administrative'))) { $errors[] = "Invalid employee type."; }
    if ($old['dept'] === '') { $errors[] = "Department is required."; }
    if ($old['basic'] === '' || !is_numeric($old['basic']) || (float)$old['basic'] < 0) { $errors[] = "Basic pay must be a positive number."; }
    foreach (array('da', 'hra', 'medical', 'child_edu') as $allow) {
        if ($old[$allow] === '' || !is_numeric($old[$allow]) || (float)$old[$allow] < 0) {
            $errors[] = "Allowance fields must be non-negative numbers.";
            break;
        }
    }
    if (!in_array($old['duty_type'], array('Teaching', 'Administrative'))) { $errors[] = "Invalid duty type."; }
    if ($old['duty_type'] === 'Teaching' && $old['course_name'] === '') { $errors[] = "Course name is required for teaching duty."; }
    if ($old['duty_type'] === 'Administrative' && $old['course_name'] === '') { $errors[] = "Duty title is required for administrative duty."; }
    if ($old['assigned_date'] === '' || clean_date($old['assigned_date']) === '') { $errors[] = "A valid assigned date is required."; }
    if (!in_array($old['status'], array('Active', 'Completed', 'Pending'))) { $errors[] = "Invalid status."; }

    $dept_check = $conn->prepare("SELECT Dept_Name FROM department WHERE Dept_Name = ?");
    $dept_check->bind_param("s", $old['dept']);
    $dept_check->execute();
    if ($dept_check->get_result()->num_rows === 0) { $errors[] = "Selected department does not exist."; }
    $dept_check->close();

    if (count($errors) === 0) {
        $phone = '+880' . $old['phone'];
        $dup_check = $conn->prepare("SELECT Staff_Name, Phone_Number, Email_Address FROM staff WHERE Staff_Name = ? OR Phone_Number = ? OR Email_Address = ?");
        $dup_check->bind_param("sss", $old['staff_name'], $phone, $old['email']);
        $dup_check->execute();
        $dup = $dup_check->get_result()->fetch_assoc();
        $dup_check->close();

        if ($dup) {
            if ($dup['Staff_Name'] === $old['staff_name']) { $errors[] = "A staff member with this name already exists."; }
            if ($dup['Phone_Number'] === $phone) { $errors[] = "This phone number is already in use."; }
            if ($dup['Email_Address'] === $old['email']) { $errors[] = "This email address is already in use."; }
        }
    }

    if (count($errors) === 0) {
        $dob = $old['dob'];
        $doj = $old['doj'];
        $assigned_date = $old['assigned_date'];
        $generated_pass = default_password($old['staff_name']);
        $hash = password_hash($generated_pass, PASSWORD_DEFAULT);

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO staff (Staff_Name, Gender, Date_of_Birth, Date_of_Join, Phone_Number, Email_Address, Address, Employee_Type, Is_Admin, Dept_Name, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");
            $stmt->bind_param("ssssssssss", $old['staff_name'], $old['gender'], $dob, $doj, $phone, $old['email'], $old['address'], $old['employee_type'], $old['dept'], $hash);
            $stmt->execute();
            $new_id = $stmt->insert_id;
            $stmt->close();

            $sal_stmt = $conn->prepare("INSERT INTO salary (Staff_ID, Basic_Pay, DA, HRA, Medical_Allow, Child_Education) VALUES (?, ?, ?, ?, ?, ?)");
            $sal_stmt->bind_param("iddddd", $new_id, $old['basic'], $old['da'], $old['hra'], $old['medical'], $old['child_edu']);
            $sal_stmt->execute();
            $sal_stmt->close();

            $duty_stmt = $conn->prepare("INSERT INTO assigned_duties (Staff_ID, Duty_Type, Course_Name, Duty_Description, Assigned_Date, Status) VALUES (?, ?, ?, ?, ?, ?)");
            $duty_stmt->bind_param("isssss", $new_id, $old['duty_type'], $old['course_name'], $old['duty_desc'], $assigned_date, $old['status']);
            $duty_stmt->execute();
            $duty_stmt->close();

            $conn->commit();

            $success = "Staff member added successfully with Staff ID " . $new_id . ".";
        } catch (mysqli_sql_exception $ex) {
            $conn->rollback();
            if ($ex->getCode() === 1062) {
                $errors[] = "Duplicate entry detected. Name, phone or email is already registered.";
            } else {
                $errors[] = "Database error while saving. Please try again.";
            }
        }
    }
}

$dept_result = $conn->query("SELECT Dept_Name FROM department ORDER BY Dept_Name");
include 'partials/header.php';
?>

<h2 class="section-title">Add Staff <span class="badge badge-admin">Admin Only</span></h2>

<?php if ($success !== "") { ?>
  <div class="alert alert-success">
    <?php echo e($success); ?><br>
    <b>Login username:</b> <?php echo e($old['staff_name']); ?> &middot;
    <b>Default password:</b> <code><?php echo e($generated_pass); ?></code> (please share securely)
  </div>
<?php } ?>

<?php if (count($errors) > 0) { ?>
  <div class="alert alert-error">
    <ul>
      <?php foreach ($errors as $err) { echo "<li>" . e($err) . "</li>"; } ?>
    </ul>
  </div>
<?php } ?>

<form method="post" action="add_staff.php" class="form-card card">
  <?php echo csrf_field(); ?>

  <h3>Personal Information</h3>
  <div class="form-grid">
    <div class="form-group">
      <label for="staff_name">Full Name *</label>
      <input type="text" id="staff_name" name="staff_name" value="<?php echo e($old['staff_name']); ?>" required>
    </div>
    <div class="form-group">
      <label for="gender">Gender *</label>
      <select id="gender" name="gender">
        <option value="Male" <?php echo $old['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
        <option value="Female" <?php echo $old['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
        <option value="Other" <?php echo $old['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
      </select>
    </div>
    <div class="form-group">
      <label for="dob">Date of Birth (YYYY-MM-DD) *</label>
      <input type="date" id="dob" name="dob" value="<?php echo e($old['dob']); ?>" required>
    </div>
    <div class="form-group">
      <label for="doj">Date of Join (YYYY-MM-DD) *</label>
      <input type="date" id="doj" name="doj" value="<?php echo e($old['doj']); ?>" required>
    </div>
    <div class="form-group">
      <label for="phone">Phone Number *</label>
      <div class="phone-input">
        <span class="phone-prefix">+880</span>
        <input type="text" id="phone" name="phone" value="<?php echo e($old['phone']); ?>" placeholder="1XXXXXXXXX" maxlength="10" required>
      </div>
    </div>
    <div class="form-group">
      <label for="email">Email Address *</label>
      <input type="email" id="email" name="email" value="<?php echo e($old['email']); ?>" placeholder="name@ewubd.edu" required>
    </div>
    <div class="form-group form-full">
      <label for="address">Address *</label>
      <input type="text" id="address" name="address" value="<?php echo e($old['address']); ?>" required>
    </div>
  </div>

  <h3>Job Information</h3>
  <div class="form-grid">
    <div class="form-group">
      <label for="employee_type">Employee Type *</label>
      <select id="employee_type" name="employee_type">
        <option value="Faculty" <?php echo $old['employee_type'] === 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
        <option value="Administrative" <?php echo $old['employee_type'] === 'Administrative' ? 'selected' : ''; ?>>Administrative</option>
      </select>
    </div>
    <div class="form-group">
      <label for="dept">Department *</label>
      <select id="dept" name="dept" required>
        <option value="">Select Department</option>
        <?php while ($d = $dept_result->fetch_assoc()) { ?>
          <option value="<?php echo e($d['Dept_Name']); ?>" <?php echo $old['dept'] === $d['Dept_Name'] ? 'selected' : ''; ?>>
            <?php echo e($d['Dept_Name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
  </div>

  <h3>Salary Information (in BDT)</h3>
  <div class="form-grid">
    <div class="form-group">
      <label for="basic">Basic Pay *</label>
      <input type="number" step="0.01" min="0" id="basic" name="basic" value="<?php echo e($old['basic']); ?>" required>
    </div>
    <div class="form-group">
      <label for="da">DA (Dearness Allowance)</label>
      <input type="number" step="0.01" min="0" id="da" name="da" value="<?php echo e($old['da']); ?>">
    </div>
    <div class="form-group">
      <label for="hra">HRA (House Rent Allowance)</label>
      <input type="number" step="0.01" min="0" id="hra" name="hra" value="<?php echo e($old['hra']); ?>">
    </div>
    <div class="form-group">
      <label for="medical">Medical Allowance</label>
      <input type="number" step="0.01" min="0" id="medical" name="medical" value="<?php echo e($old['medical']); ?>">
    </div>
    <div class="form-group">
      <label for="child_edu">Child Education Allowance</label>
      <input type="number" step="0.01" min="0" id="child_edu" name="child_edu" value="<?php echo e($old['child_edu']); ?>">
    </div>
  </div>

  <h3>Initial Duty</h3>
  <div class="form-grid">
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
    <button type="submit" class="btn btn-primary">Add Staff</button>
    <a class="btn btn-ghost" href="home.php">Cancel</a>
  </div>
</form>

<?php include 'partials/footer.php'; ?>
