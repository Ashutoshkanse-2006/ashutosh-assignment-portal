<?php
// Database connection. Settings come from Vercel Environment Variables (see includes/db_connect.php);
// without them it uses the local XAMPP defaults.
require_once __DIR__ . '/db_connect.php';

if (!isset($conn) || !($conn instanceof PgcConn)) {
    $conn = db_connect('student_portal');
}
?>
