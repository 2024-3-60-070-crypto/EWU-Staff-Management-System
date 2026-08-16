<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "staff_management";

$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function run_query($conn, $sql) {
    if (!$conn->query($sql)) {
        die("SQL error: " . $conn->error . "<br>Statement: " . htmlspecialchars(substr($sql, 0, 120)));
    }
}

$steps = [];

run_query($conn, "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);
$steps[] = "Database <b>$dbname</b> created / selected";

$schema = file_get_contents(__DIR__ . '/database.sql');
$schema = preg_replace('/CREATE DATABASE[^;]+;/i', '', $schema);
$schema = preg_replace('/USE [^;]+;/i', '', $schema);
foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
    run_query($conn, $statement);
}
$steps[] = "Tables created: department, staff, assigned_duties, salary, leaves";

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

function seed_if_empty($conn) {
    $res = $conn->query("SELECT COUNT(*) AS c FROM staff");
    $row = $res->fetch_assoc();
    return (int)$row['c'] === 0;
}

if (!seed_if_empty($conn)) {
    echo "<h2>Staff Management - Setup</h2><p>The database already contains staff records. Nothing was re-seeded.</p>";
    echo '<p><a href="login.php">Go to Login Page</a></p>';
    exit;
}

$departments = array(
    array('Computer Science & Engineering', 'Academic', 'CSE Building, 4th Floor'),
    array('Electrical & Electronic Engineering', 'Academic', 'CSE Building, 5th Floor'),
    array('Business Administration', 'Academic', 'Business School, 2nd Floor'),
    array('Registrar Office', 'Administrative', 'Administrative Building, Ground Floor'),
    array('Accounts Office', 'Administrative', 'Administrative Building, 1st Floor'),
    array('admin', 'Administrative', 'Administrative Building')
);

foreach ($departments as $d) {
    $stmt = $conn->prepare("INSERT INTO department (Dept_Name, Dept_Type, Location) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $d[0], $d[1], $d[2]);
    $stmt->execute();
    $stmt->close();
}
$steps[] = count($departments) . " departments seeded";

$staff_rows = array(
    array('Dr. Md. Mozammel Huq Azad Khan', 'Male', '1965-03-15', '2000-07-01', '+8801711000001', 'mozammel.khan@ewubd.edu', 'House 12, Road 5, Dhanmondi, Dhaka', 'Faculty', 0, 'Computer Science & Engineering', null),
    array('Dr. Maheen Islam', 'Female', '1975-06-20', '2005-01-15', '+8801711000002', 'maheen.islam@ewubd.edu', 'Flat 3B, House 27, Road 9, Mirpur-10, Dhaka', 'Faculty', 0, 'Computer Science & Engineering', null),
    array('Dr. Anisur Rahman', 'Male', '1978-11-05', '2008-05-10', '+8801711000003', 'anisur.rahman@ewubd.edu', 'House 41, Road 7, Sector 7, Uttara, Dhaka', 'Faculty', 0, 'Computer Science & Engineering', null),
    array('Dr. Tania Sultana', 'Female', '1985-02-12', '2012-06-01', '+8801711000004', 'tania.sultana@ewubd.edu', 'House 18, Block B, Banasree, Rampura, Dhaka', 'Faculty', 0, 'Computer Science & Engineering', null),
    array('Dr. Md. Nawab Yousuf Ali', 'Male', '1970-09-01', '2002-01-05', '+8801711000005', 'nawab.yousuf@ewubd.edu', 'House 5, Road 2, Mohammadpur, Dhaka', 'Faculty', 0, 'Computer Science & Engineering', null),
    array('Khairul Alam', 'Male', '1972-04-18', '2007-01-10', '+8801711000006', 'khairul.alam@ewubd.edu', 'House 96, Road 11, Block C, Bashundhara R/A, Dhaka', 'Faculty', 0, 'Electrical & Electronic Engineering', null),
    array('Dr. Halima Begum', 'Female', '1980-08-25', '2010-02-01', '+8801711000007', 'halima.begum@ewubd.edu', 'House 33, Road 12, Rampura, Dhaka', 'Faculty', 0, 'Electrical & Electronic Engineering', null),
    array('Dr. Farhana Parveen', 'Female', '1984-01-30', '2013-07-15', '+8801711000008', 'farhana.parveen@ewubd.edu', 'House 7, Road 4, Khilgaon, Dhaka', 'Faculty', 0, 'Electrical & Electronic Engineering', null),
    array('Dr. Mohammad Ryyan Khan', 'Male', '1976-12-03', '2009-06-20', '+8801711000009', 'ryyan.khan@ewubd.edu', 'House 65, Road 15, Gulshan-2, Dhaka', 'Faculty', 0, 'Electrical & Electronic Engineering', null),
    array('Dr. Farhana Ferdousi', 'Female', '1974-05-15', '2006-09-05', '+8801711000010', 'farhana.ferdousi@ewubd.edu', 'House 21, Road 15, Dhanmondi, Dhaka', 'Faculty', 0, 'Business Administration', null),
    array('Dr. Nikhil Chandra Shil', 'Male', '1973-07-22', '2005-03-12', '+8801711000011', 'nikhil.shil@ewubd.edu', 'House 14, Road 4, Mohammadpur, Dhaka', 'Faculty', 0, 'Business Administration', null),
    array('Dr. Salma Akter', 'Female', '1982-10-08', '2011-05-01', '+8801711000012', 'salma.akter@ewubd.edu', 'House 29, Road 8, Shyamoli, Dhaka', 'Faculty', 0, 'Business Administration', null),
    array('Laila Zaman', 'Female', '1988-03-17', '2016-08-10', '+8801711000013', 'laila.zaman@ewubd.edu', 'House 9, Road 3, Mirpur-2, Dhaka', 'Faculty', 0, 'Business Administration', null),
    array('Rahim Uddin', 'Male', '1975-01-09', '2003-01-01', '+8801711000014', 'rahim.uddin@ewubd.edu', 'House 50, Road 9, Mohammadpur, Dhaka', 'Administrative', 1, 'admin', 'admin123'),
    array('Anwar Hossain', 'Male', '1981-05-12', '2012-03-01', '+8801711000030', 'anwar.hossain@ewubd.edu', 'House 11, Road 9, Banani, Dhaka', 'Administrative', 1, 'admin', 'admin123'),
    array('Nasrin Akter', 'Female', '1986-11-23', '2014-01-15', '+8801711000031', 'nasrin.akter@ewubd.edu', 'Flat 5A, House 8, Road 7, Gulshan-1, Dhaka', 'Administrative', 1, 'admin', 'admin123'),
    array('Salma Begum', 'Female', '1983-06-27', '2010-10-01', '+8801711000015', 'salma.begum@ewubd.edu', 'Flat 2A, House 13, Magbazar, Dhaka', 'Administrative', 0, 'Accounts Office', null),
    array('Kamal Hossain', 'Male', '1979-09-14', '2008-04-01', '+8801711000016', 'kamal.hossain@ewubd.edu', 'House 88, Road 1, Wari, Dhaka', 'Administrative', 0, 'Registrar Office', null)
);

$staff_ids = array();
foreach ($staff_rows as $s) {
    $plain = $s[10] !== null ? $s[10] : default_password($s[0]);
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO staff (Staff_Name, Gender, Date_of_Birth, Date_of_Join, Phone_Number, Email_Address, Address, Employee_Type, Is_Admin, Dept_Name, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssiss", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $hash);
    $stmt->execute();
    $staff_ids[$s[0]] = $stmt->insert_id;
    $stmt->close();
}
$steps[] = count($staff_rows) . " staff members seeded (passwords hashed)";

$salary_rows = array(
    array('Dr. Md. Mozammel Huq Azad Khan', 110000, 22000, 30000, 10000, 5000),
    array('Dr. Maheen Islam', 85000, 17000, 22000, 9000, 4000),
    array('Dr. Anisur Rahman', 82000, 16000, 21000, 9000, 4000),
    array('Dr. Tania Sultana', 60000, 12000, 16000, 7000, 3000),
    array('Dr. Md. Nawab Yousuf Ali', 105000, 21000, 28000, 10000, 5000),
    array('Khairul Alam', 100000, 20000, 26000, 10000, 5000),
    array('Dr. Halima Begum', 78000, 15000, 20000, 8000, 4000),
    array('Dr. Farhana Parveen', 58000, 11000, 15000, 7000, 3000),
    array('Dr. Mohammad Ryyan Khan', 80000, 16000, 21000, 9000, 4000),
    array('Dr. Farhana Ferdousi', 98000, 19000, 25000, 10000, 5000),
    array('Dr. Nikhil Chandra Shil', 102000, 20000, 26000, 10000, 5000),
    array('Dr. Salma Akter', 75000, 15000, 19000, 8000, 4000),
    array('Laila Zaman', 55000, 11000, 14000, 7000, 3000),
    array('Rahim Uddin', 65000, 13000, 17000, 8000, 4000),
    array('Anwar Hossain', 60000, 12000, 16000, 7000, 3000),
    array('Nasrin Akter', 58000, 11000, 15000, 7000, 3000),
    array('Salma Begum', 55000, 11000, 15000, 7000, 3000),
    array('Kamal Hossain', 50000, 10000, 14000, 7000, 3000)
);

foreach ($salary_rows as $sl) {
    $id = $staff_ids[$sl[0]];
    $stmt = $conn->prepare("INSERT INTO salary (Staff_ID, Basic_Pay, DA, HRA, Medical_Allow, Child_Education) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddddd", $id, $sl[1], $sl[2], $sl[3], $sl[4], $sl[5]);
    $stmt->execute();
    $stmt->close();
}
$steps[] = count($salary_rows) . " salary records seeded (Total_Salary auto-calculated)";

$duty_rows = array(
    array('Dr. Md. Mozammel Huq Azad Khan', 'Teaching', 'Database Management Systems', null, '2026-01-05', 'Active'),
    array('Dr. Maheen Islam', 'Teaching', 'Data Structures & Algorithms', null, '2026-01-05', 'Active'),
    array('Dr. Anisur Rahman', 'Teaching', 'Operating Systems', null, '2026-01-05', 'Active'),
    array('Dr. Tania Sultana', 'Teaching', 'Object Oriented Programming', null, '2026-01-05', 'Active'),
    array('Dr. Md. Nawab Yousuf Ali', 'Teaching', 'Artificial Intelligence', null, '2026-01-05', 'Active'),
    array('Khairul Alam', 'Teaching', 'Digital Logic Design', null, '2026-01-05', 'Active'),
    array('Dr. Halima Begum', 'Teaching', 'Microprocessors & Embedded Systems', null, '2026-01-05', 'Active'),
    array('Dr. Farhana Parveen', 'Teaching', 'Electronic Circuits', null, '2026-01-05', 'Active'),
    array('Dr. Mohammad Ryyan Khan', 'Teaching', 'Signals & Systems', null, '2026-01-05', 'Active'),
    array('Dr. Farhana Ferdousi', 'Teaching', 'Principles of Marketing', null, '2026-01-05', 'Active'),
    array('Dr. Nikhil Chandra Shil', 'Teaching', 'Financial Accounting', null, '2026-01-05', 'Active'),
    array('Dr. Salma Akter', 'Teaching', 'Organizational Behavior', null, '2026-01-05', 'Active'),
    array('Laila Zaman', 'Teaching', 'Business Statistics', null, '2026-01-05', 'Active'),
    array('Rahim Uddin', 'Administrative', 'Student Records Management', 'Maintains student registration and academic records', '2026-01-01', 'Active'),
    array('Salma Begum', 'Administrative', 'Payroll Processing', 'Processes monthly payroll of all staff members', '2026-01-01', 'Active'),
    array('Kamal Hossain', 'Administrative', 'Admission Management', 'Handles admission documents and verification', '2026-01-01', 'Active')
);

foreach ($duty_rows as $du) {
    $id = $staff_ids[$du[0]];
    $stmt = $conn->prepare("INSERT INTO assigned_duties (Staff_ID, Duty_Type, Course_Name, Duty_Description, Assigned_Date, Status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $id, $du[1], $du[2], $du[3], $du[4], $du[5]);
    $stmt->execute();
    $stmt->close();
}
$steps[] = "16 duty records seeded";

$leave_rows = array(
    array('Dr. Maheen Islam', '2026-03-02', 2, 'Sick', 'Viral fever, doctor advised rest'),
    array('Dr. Maheen Islam', '2026-05-10', 1, 'Casual', 'Personal work'),
    array('Dr. Anisur Rahman', '2026-01-15', 3, 'Earned', 'Family trip'),
    array('Dr. Anisur Rahman', '2026-04-20', 1, 'Sick', 'Fever and throat infection'),
    array('Dr. Tania Sultana', '2026-02-22', 2, 'Casual', 'Family event'),
    array('Dr. Halima Begum', '2026-03-25', 3, 'Academic', 'Conference paper writing'),
    array('Dr. Salma Akter', '2026-06-15', 2, 'Sick', 'Migraine'),
    array('Laila Zaman', '2026-07-01', 2, 'Earned', 'Vacation'),
    array('Rahim Uddin', '2026-02-05', 1, 'Casual', 'Personal work')
);

foreach ($leave_rows as $lv) {
    $id = $staff_ids[$lv[0]];
    $stmt = $conn->prepare("INSERT INTO leaves (Staff_ID, Leave_Date, No_of_Days, Leave_Type, Reason) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $id, $lv[1], $lv[2], $lv[3], $lv[4]);
    $stmt->execute();
    $stmt->close();
}
$steps[] = "9 leave records seeded";

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup Complete - Staff Management</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card setup-card">
    <h1>Setup Complete</h1>
    <p>The <b>staff_management</b> database has been created and populated successfully.</p>
    <ul>
      <?php foreach ($steps as $step) { echo "<li>" . $step . "</li>"; } ?>
    </ul>
    <p><b>Admin login:</b> Rahim Uddin / Anwar Hossain / Nasrin Akter &mdash; password <code>admin123</code></p>
    <p><b>Staff login rule:</b> username = full name, password = 123 + first name (example: Dr. Maheen Islam / 123Maheen)</p>
    <p><a class="btn btn-primary" href="login.php">Go to Login Page</a></p>
  </div>
</div>
</body>
</html>
