<?php
// Deletes one assignment. Called by the Delete button in assigments.php (POST only).
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int) ($_POST['id'] ?? 0);
    $fid = (int) $_SESSION['faculty_id'];

    $st = pgc_prepare($conn, "DELETE FROM faculty_assignments WHERE id = ? AND faculty_id = ?");
    pgc_stmt_bind_param($st, "ii", $id, $fid);
    pgc_stmt_execute($st);

    if (pgc_stmt_affected_rows($st) > 0) {
        flash_set('success', 'Assignment deleted.');
    } else {
        flash_set('error', 'Assignment not found.');
    }
}

header("Location: assigments.php");
exit();
