<?php
require_once __DIR__ . '/includes/session.php';

if (!isset($_SESSION['student_name'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
    <div class="container narrow">

        <div class="page-header">
            <h1>Student Profile</h1>
        </div>

        <div class="card">
            <dl class="detail-list">
                <div class="detail-row"><dt>Name</dt><dd><?php echo htmlspecialchars((string) $_SESSION['student_name']); ?></dd></div>
                <div class="detail-row"><dt>Enrollment Number</dt><dd><?php echo htmlspecialchars((string) $_SESSION['enrollment_number']); ?></dd></div>
                <div class="detail-row"><dt>Department</dt><dd><?php echo htmlspecialchars((string) $_SESSION['department']); ?></dd></div>
                <div class="detail-row"><dt>Semester</dt><dd><?php echo htmlspecialchars((string) $_SESSION['semester']); ?></dd></div>
            </dl>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="dashboard.php">Back to Dashboard</a>
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
