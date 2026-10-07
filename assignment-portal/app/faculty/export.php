<?php
// Downloads all (or filtered) student submissions as a CSV file for the faculty member.
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$title = trim($_GET['title'] ?? '');
$sql = "SELECT a.assignment_title, a.student_name, a.enrollment_number, s.department, s.semester,
        a.submitted_at, a.status, a.marks, a.feedback, a.reviewed_at
        FROM assignments a LEFT JOIN students s ON s.enrollment_number = a.enrollment_number";
$st = null;
if ($title !== '') {
    $st = pgc_prepare($conn, $sql . " WHERE a.assignment_title = ? ORDER BY a.student_name");
    pgc_stmt_bind_param($st, "s", $title);
} else {
    $st = pgc_prepare($conn, $sql . " ORDER BY a.assignment_title, a.student_name");
}
pgc_stmt_execute($st);
$rows = pgc_fetch_all(pgc_stmt_get_result($st));

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="submissions-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Assignment', 'Student', 'Enrollment', 'Department', 'Semester', 'Submitted at', 'Status', 'Marks', 'Feedback', 'Reviewed at']);
foreach ($rows as $r) {
    fputcsv($out, [$r['assignment_title'], $r['student_name'], $r['enrollment_number'], $r['department'], $r['semester'], $r['submitted_at'], $r['status'], $r['marks'], $r['feedback'], $r['reviewed_at']]);
}
fclose($out);
