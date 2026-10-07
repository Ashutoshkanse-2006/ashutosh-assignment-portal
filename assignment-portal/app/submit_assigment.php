<?php
require_once __DIR__ . '/includes/session.php';
include("includes/db.php");
include("includes/uploads.php");
if (!isset($_SESSION['student_name'])) { header("Location: login.php"); exit(); }

$enrollment_number = $_SESSION['enrollment_number'];
$student_name = $_SESSION['student_name'];
$dept = (string) ($_SESSION['department'] ?? '');
$sem  = (string) ($_SESSION['semester'] ?? '');
$msg = ""; $ok = false;

// assignments the faculty has posted for this student's department / semester
$st = pgc_prepare($conn, "SELECT title, due_date FROM faculty_assignments WHERE (department='All' OR department=?) AND (semester='All' OR semester=?) ORDER BY due_date");
pgc_stmt_bind_param($st, "ss", $dept, $sem);
pgc_stmt_execute($st);
$choices = pgc_fetch_all(pgc_stmt_get_result($st));
$preset = trim($_GET['title'] ?? '');

if (isset($_POST["submit_assignment"])) {
    $assignment_title = trim($_POST['assignment_title'] ?? '');
    $f = $_FILES['assignment_file'] ?? null;
    if ($assignment_title === '') {
        $msg = "Please choose an assignment.";
    } elseif (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        $msg = "Please choose a file (maximum 4 MB).";
    } elseif ($f['size'] > 4 * 1024 * 1024) {
        $msg = "The file is too large. Maximum size is 4 MB.";
    } else {
        $file_name = basename($f['name']);
        if (save_upload($f['tmp_name'], $file_name, $enrollment_number)) {
            // a re-submission replaces the earlier one for the same assignment
            $d = pgc_prepare($conn, "DELETE FROM assignments WHERE enrollment_number = ? AND assignment_title = ?");
            pgc_stmt_bind_param($d, "ss", $enrollment_number, $assignment_title);
            pgc_stmt_execute($d);
            $i = pgc_prepare($conn, "INSERT INTO assignments (enrollment_number, student_name, assignment_title, assignment_file) VALUES (?,?,?,?)");
            pgc_stmt_bind_param($i, "ssss", $enrollment_number, $student_name, $assignment_title, $file_name);
            pgc_stmt_execute($i);
            $ok = true; $msg = "Assignment submitted successfully.";
        } else {
            $msg = "Failed to upload the file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="dashboard.php"><span class="brand-mark">AP</span><span>Assignment Portal</span></a>
        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="assigments.php">Assignments</a></li>
            <li><a href="submit_assigment.php">Submit</a></li>
            <li><a href="my-submissions.php">My Submissions</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a class="nav-cta" href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="container auth-wrapper">
        <div class="card auth-card wide">
            <h1>Submit Assignment</h1>
            <p class="intro">Pick the assignment and upload your completed work.</p>
            <?php if ($msg) echo '<p style="font-weight:600;color:' . ($ok ? '#067647' : '#b42318') . ';">' . htmlspecialchars($msg) . '</p>'; ?>

            <form method="POST" action="" enctype="multipart/form-data">

                <div class="form-grid">

                    <div class="form-group">
                        <label for="enrollment_number">Enrollment Number</label>
                        <input class="form-control" type="text" id="enrollment_number" value="<?php echo htmlspecialchars($enrollment_number); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="student_name">Student Name</label>
                        <input class="form-control" type="text" id="student_name" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
                    </div>

                    <div class="form-group full">
                        <label for="assignment_title">Assignment Title</label>
                        <?php if ($choices) { ?>
                        <select class="form-control" id="assignment_title" name="assignment_title" required>
                            <option value="">-- Select assignment --</option>
<?php foreach ($choices as $c) { ?>
                            <option value="<?php echo htmlspecialchars($c['title']); ?>"<?php echo $preset === $c['title'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($c['title']); ?> (due <?php echo date('d-m-Y', strtotime($c['due_date'])); ?>)</option>
<?php } ?>
                        </select>
                        <?php } else { ?>
                        <input class="form-control" type="text" id="assignment_title" name="assignment_title" value="<?php echo htmlspecialchars($preset); ?>" required>
                        <?php } ?>
                    </div>

                    <div class="form-group full">
                        <label for="assignment_file">Assignment File</label>
                        <input class="form-control" type="file" id="assignment_file" name="assignment_file" required>
                    </div>

                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit" name="submit_assignment" value="Submit Assignment">Submit Assignment</button>
                    <a class="btn btn-outline" href="dashboard.php">Back to Dashboard</a>
                </div>

            </form>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <p><strong>Student Assignment Submission Portal</strong></p>
        <p>Indus University &middot; Department of Computer Science &amp; Engineering</p>
    </div>
</footer>

<script src="javascript/script.js"></script>
</body>
</html>
