<?php
require_once __DIR__ . '/includes/session.php';
include("includes/db.php");
if (!isset($_SESSION['student_name'])) { header("Location: login.php"); exit(); }
$enr  = $_SESSION['enrollment_number'];
$dept = (string) ($_SESSION['department'] ?? '');
$sem  = (string) ($_SESSION['semester'] ?? '');
$st = pgc_prepare($conn, "SELECT f.*, fa.faculty_name,
    (SELECT COUNT(*) FROM assignments a WHERE a.enrollment_number = ? AND a.assignment_title = f.title) AS done
    FROM faculty_assignments f LEFT JOIN faculty fa ON fa.id = f.faculty_id
    WHERE (f.department = 'All' OR f.department = ?) AND (f.semester = 'All' OR f.semester = ?)
    ORDER BY f.due_date");
pgc_stmt_bind_param($st, "sss", $enr, $dept, $sem);
pgc_stmt_execute($st);
$list = pgc_fetch_all(pgc_stmt_get_result($st));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
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

        <div class="page-header">
            <h1>Assignments</h1>
            <p>These are the upcoming assignments.</p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
<?php if (!$list) { ?>
                    <tr><td colspan="5" class="empty-row">No assignments have been posted for you yet.</td></tr>
<?php } foreach ($list as $r) {
    $overdue = strtotime($r['due_date'] . ' 23:59:59') < time(); ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['title']); ?></strong><br><small><?php echo htmlspecialchars($r['subject'] ?? ''); ?><?php echo $r['faculty_name'] ? ' &middot; ' . htmlspecialchars($r['faculty_name']) : ''; ?></small></td>
                        <td><?php echo nl2br(htmlspecialchars($r['description'] ?? '')); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($r['due_date'])); ?></td>
                        <td><span class="badge"><?php echo (int) $r['done'] > 0 ? 'Submitted' : ($overdue ? 'Overdue' : 'Pending'); ?></span></td>
                        <td><a class="btn btn-outline" href="submit_assigment.php?title=<?php echo urlencode($r['title']); ?>"><?php echo (int) $r['done'] > 0 ? 'Resubmit' : 'Submit'; ?></a></td>
                    </tr>
<?php } ?>
                </tbody>
            </table>
        </div>

        <h2 class="section-title" style="margin-top: 36px;">Assignment Details</h2>
        <div class="grid">
            <a class="card action-card" href="assigment-details.php?id=1">
                <h3>Assignment 1 Web Technology</h3>
                <p>View details &rarr;</p>
            </a>
            <a class="card action-card" href="assigment-details.php?id=2">
                <h3>Assignment 1 Computer Networks</h3>
                <p>View details &rarr;</p>
            </a>
            <a class="card action-card" href="assigment-details.php?id=3">
                <h3>Assignment 1 DAA</h3>
                <p>View details &rarr;</p>
            </a>
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
