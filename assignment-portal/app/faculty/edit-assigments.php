<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$fid = (int) $_SESSION['faculty_id'];

// load the assignment (only the faculty who created it can edit it)
$st = pgc_prepare($conn, "SELECT * FROM faculty_assignments WHERE id = ? AND faculty_id = ?");
pgc_stmt_bind_param($st, "ii", $id, $fid);
pgc_stmt_execute($st);
$a = pgc_fetch_assoc(pgc_stmt_get_result($st));

if (!$a) {
    flash_set('error', 'Assignment not found.');
    header("Location: assigments.php");
    exit();
}

$error = "";

if (isset($_POST['save'])) {
    $a = array_merge($a, assignment_from_post());

    if ($a['title'] === '' || $a['subject'] === '' || $a['description'] === '' || $a['due_date'] === '') {
        $error = "Please fill in title, subject, description and due date.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $a['due_date'])) {
        $error = "Please choose a valid due date.";
    } else {
        $up = pgc_prepare($conn, "UPDATE faculty_assignments SET title=?, subject=?, description=?, instructions=?, department=?, semester=?, due_date=? WHERE id=? AND faculty_id=?");
        pgc_stmt_bind_param($up, "sssssssii", $a['title'], $a['subject'], $a['description'], $a['instructions'], $a['department'], $a['semester'], $a['due_date'], $id, $fid);
        if (pgc_stmt_execute($up)) {
            flash_set('success', 'Assignment updated.');
            header("Location: assigments.php");
            exit();
        }
        $error = "Could not update the assignment. Please try again.";
    }
}

faculty_header("Edit Assignment");
?>
    <div class="container auth-wrapper">
        <div class="card auth-card wide">
            <h1>Edit Assignment</h1>
            <p class="intro">Update the details and save.</p>

<?php if ($error !== "") { ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
<?php } ?>

<?php assignment_form($a, "Save Changes"); ?>
        </div>
    </div>
<?php faculty_footer(); ?>
