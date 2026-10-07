<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$a = ['title' => '', 'subject' => '', 'description' => '', 'instructions' => '',
      'department' => 'All', 'semester' => 'All', 'due_date' => ''];
$error = "";

if (isset($_POST['save'])) {
    $a = assignment_from_post();

    if ($a['title'] === '' || $a['subject'] === '' || $a['description'] === '' || $a['due_date'] === '') {
        $error = "Please fill in title, subject, description and due date.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $a['due_date'])) {
        $error = "Please choose a valid due date.";
    } else {
        $st = pgc_prepare($conn, "INSERT INTO faculty_assignments (faculty_id, title, subject, description, instructions, department, semester, due_date) VALUES (?,?,?,?,?,?,?,?)");
        $fid = (int) $_SESSION['faculty_id'];
        pgc_stmt_bind_param($st, "isssssss", $fid, $a['title'], $a['subject'], $a['description'], $a['instructions'], $a['department'], $a['semester'], $a['due_date']);
        if (pgc_stmt_execute($st)) {
            flash_set('success', 'Assignment created successfully.');
            header("Location: assigments.php");
            exit();
        }
        $error = "Could not save the assignment. Please try again.";
    }
}

faculty_header("Create Assignment");
?>
    <div class="container auth-wrapper">
        <div class="card auth-card wide">
            <h1>Create Assignment</h1>
            <p class="intro">Fill in the details of the new assignment.</p>

<?php if ($error !== "") { ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
<?php } ?>

<?php assignment_form($a, "Create Assignment"); ?>
        </div>
    </div>
<?php faculty_footer(); ?>
