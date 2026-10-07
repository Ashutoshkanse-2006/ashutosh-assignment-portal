<?php
// Lets a logged-in faculty member open/download a student's submitted file: download.php?id=<submission id>
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$id = (int) ($_GET['id'] ?? 0);

$st = pgc_prepare($conn, "SELECT u.filename, u.mime, u.data
    FROM assignments a
    JOIN uploaded_files u ON u.filename = a.assignment_file AND u.enrollment_number = a.enrollment_number
    WHERE a.id = ? ORDER BY u.id DESC LIMIT 1");
pgc_stmt_bind_param($st, "i", $id);
pgc_stmt_execute($st);
$f = pgc_fetch_assoc(pgc_stmt_get_result($st));

if (!$f) {
    http_response_code(404);
    echo "File not found.";
    exit();
}

header("Content-Type: " . ($f['mime'] ?: 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '', $f['filename']) . '"');
echo $f['data'];
