<?php
require_once __DIR__ . '/includes/session.php';
if (isset($_SESSION["student_name"])) {
    unset($_SESSION["student_name"]);
}
if (isset($_SESSION["enrollment_number"])) {
    unset($_SESSION["enrollment_number"]);
}
if (isset($_SESSION["department"])) {
    unset($_SESSION["department"]);
}
if (isset($_SESSION["semester"])) {
    unset($_SESSION["semester"]);
}
session_destroy();
header("Location: login.php");
exit();
?>