<?php
$id = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details</title>
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
            <h1>Assignment Details</h1>
        </div>

        <div class="card">
<?php
if($id == 1)
{
?>
            <h2>Web Technology Assignment 1</h2>
            <dl class="detail-list">
                <div class="detail-row"><dt>Description</dt><dd>Complete Assignment 1.</dd></div>
                <div class="detail-row"><dt>Due Date</dt><dd>30-08-2026</dd></div>
                <div class="detail-row"><dt>Instructions</dt><dd>Submit PDF format only.</dd></div>
            </dl>
<?php
}
?>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="assigments.php">Back to Assignments</a>
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
