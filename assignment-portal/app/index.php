<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Assignment Submission Portal</title>
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
    <div class="container">

        <section class="hero">
            <h1>Student Assignment Submission Portal</h1>
            <p>Welcome to the Student Assignment Submission Portal. Students can view assignments, submit their work online, and track their progress.</p>
            <div class="btn-group">
                <a class="btn btn-primary" href="login.php">Student Login</a>
                <a class="btn btn-outline" href="faculty/login.php">Faculty Login</a>
                <a class="btn btn-outline" href="register.php">Register</a>
            </div>
        </section>

        <section>
            <h2 class="section-title">Features</h2>
            <div class="grid">
                <div class="card">
                    <div class="feature-icon">1</div>
                    <h3>View Assignments</h3>
                    <p>Access assignments uploaded by faculty members.</p>
                </div>
                <div class="card">
                    <div class="feature-icon">2</div>
                    <h3>Submit Assignments</h3>
                    <p>Upload completed assignments online.</p>
                </div>
                <div class="card">
                    <div class="feature-icon">3</div>
                    <h3>Track Progress</h3>
                    <p>View submission status, marks, and feedback.</p>
                </div>
            </div>
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
