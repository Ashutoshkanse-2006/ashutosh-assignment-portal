<?php

require_once __DIR__ . '/includes/session.php';

include("includes/db.php");

$login_error = "";
if (isset($_POST['login'])) {
    $enrollment_number = trim($_POST['enrollment_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $st = pgc_prepare($conn, "SELECT * FROM students WHERE enrollment_number = ?");
    pgc_stmt_bind_param($st, "s", $enrollment_number);
    pgc_stmt_execute($st);
    $row = pgc_fetch_assoc(pgc_stmt_get_result($st));
    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['student_name'] = $row['student_name'];
        $_SESSION['enrollment_number'] = $row['enrollment_number'];
        $_SESSION['department'] = $row['department'];
        $_SESSION['semester'] = $row['semester'];
        header("Location: dashboard.php");
        exit();
    }
    $login_error = "Invalid enrollment number or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="index.php"><span class="brand-mark">AP</span><span>Assignment Portal</span></a>
        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="faculty/login.php">Faculty Login</a></li>
            <li><a href="login.php">Student Login</a></li>
            <li><a class="nav-cta" href="register.php">Register</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="container auth-wrapper">
        <div class="card auth-card">
            <h1>Student Login</h1>
            <?php if (isset($_GET['registered'])) echo '<p style="color:#067647;font-weight:600;">Registration successful. Please log in.</p>'; ?>
            <?php if ($login_error) echo '<p style="color:#b42318;font-weight:600;">' . htmlspecialchars($login_error) . '</p>'; ?>
            <p class="intro">You are Requested to enter your credentials to log in.</p>

            <form method="POST" action="">

                <div class="form-group">
                    <label for="enrollment_number">Enrollment Number</label>
                    <input class="form-control" type="text" id="enrollment_number" name="enrollment_number">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password">
                </div>

                <button class="btn btn-primary btn-block" type="submit" name="login">Login</button>

            </form>

            <p class="form-footer">New student? <a href="register.php">Create an account</a></p>
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
