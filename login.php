<?php
include 'db_connect.php';

if (!empty($_SESSION['staff_id'])) {
    header("Location: home.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $username = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT Staff_ID, Staff_Name, Dept_Name, Is_Admin, password_hash FROM staff WHERE Staff_Name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        if (password_verify($pass, $staff['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['staff_id'] = (int)$staff['Staff_ID'];
            $_SESSION['staff_name'] = $staff['Staff_Name'];
            $_SESSION['dept_name'] = $staff['Dept_Name'];
            $_SESSION['is_admin'] = ((int)$staff['Is_Admin'] === 1);
            header("Location: home.php");
            exit;
        }
    }
    $stmt->close();
    $error = "Invalid username or password. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Staff Management</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
<div class="login-wrap">
  <div class="login-card">
    <div class="login-side">
      <h2>East West University</h2>
      <p>Staff Management System</p>
      <div class="login-side-bg"></div>
    </div>
    <div class="login-form">
      <h1>Staff Login</h1>
      <p class="muted">Login with your name and password</p>

      <?php if ($error !== "") { echo '<div class="alert alert-error">' . e($error) . '</div>'; } ?>
      <?php if (isset($_GET['loggedout'])) { echo '<div class="alert alert-info">You have been logged out successfully.</div>'; } ?>

      <form method="post" action="login.php">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="username">Username (Staff Name)</label>
          <input type="text" id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>

      <details class="demo-credentials">
        <summary>Demo credentials</summary>
        <p><b>Admin:</b> Rahim Uddin / Anwar Hossain / Nasrin Akter &mdash; password <code>admin123</code></p>
        <p><b>Staff rule:</b> username = full name, password = <code>123</code> + first name</p>
        <p><i>Examples:</i></p>
        <ul>
          <li>Dr. Maheen Islam / <code>123Maheen</code></li>
          <li>Dr. Tania Sultana / <code>123Tania</code></li>
          <li>Khairul Alam / <code>123Khairul</code></li>
          <li>Salma Begum / <code>123Salma</code></li>
        </ul>
      </details>
    </div>
  </div>
</div>
</body>
</html>
