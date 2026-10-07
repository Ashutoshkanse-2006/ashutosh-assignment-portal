<?php
require_once __DIR__ . '/includes/session.php';

if(!isset($_SESSION['student_name']))
{
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
    <div class="container">

        <section class="welcome-banner">
            <h1>Student Dashboard</h1>
            <p>Welcome to the Student Dashboard. Here you can view your assignments, submit your work, and track your progress.</p>
        </section>

        <section class="grid" style="margin-bottom: 32px;">
            <div class="card info-card">
                <span class="label">Name</span>
                <span class="value"><?php echo $_SESSION['student_name']; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Enrollment Number</span>
                <span class="value"><?php echo $_SESSION['enrollment_number']; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Department</span>
                <span class="value"><?php echo $_SESSION['department']; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Semester</span>
                <span class="value"><?php echo $_SESSION['semester']; ?></span>
            </div>
        </section>

        <h2 class="section-title">Quick Actions</h2>
        <section class="grid">
            <a class="card action-card" href="assigments.php">
                <h3>View Assignments</h3>
                <p>See upcoming assignments and their due dates.</p>
            </a>
            <a class="card action-card" href="submit_assigment.php">
                <h3>Submit Assignment</h3>
                <p>Upload your completed work.</p>
            </a>
            <a class="card action-card" href="my-submissions.php">
                <h3>My Submissions</h3>
                <p>Review everything you have submitted.</p>
            </a>
            <a class="card action-card" href="profile.php">
                <h3>My Profile</h3>
                <p>View your student details.</p>
            </a>
        </section>

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
