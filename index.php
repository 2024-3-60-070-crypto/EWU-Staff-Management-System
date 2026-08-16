<?php
include 'db_connect.php';

if (!empty($_SESSION['staff_id'])) {
    header("Location: home.php");
} else {
    header("Location: login.php");
}
exit;
