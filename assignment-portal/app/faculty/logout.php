<?php
require_once __DIR__ . '/../includes/session.php';

foreach (['faculty_id', 'faculty_name', 'faculty_department', 'faculty_schema_ok', 'flash'] as $k) {
    unset($_SESSION[$k]);
}
session_destroy();
header("Location: login.php");
exit();
?>
