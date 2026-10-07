<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

function count_of($conn, $sql) {
    $r = pgc_query($conn, $sql);
    $row = $r ? pgc_fetch_row($r) : [0];
    return (int) $row[0];
}

$total_assignments = count_of($conn, "SELECT COUNT(*) FROM faculty_assignments");
$total_submissions = count_of($conn, "SELECT COUNT(*) FROM assignments");
$pending           = count_of($conn, "SELECT COUNT(*) FROM assignments WHERE status = 'Submitted'");
$total_students    = count_of($conn, "SELECT COUNT(*) FROM students");

$recent = pgc_query($conn, "SELECT id, student_name, enrollment_number, assignment_title, submitted_at, status FROM assignments ORDER BY submitted_at DESC, id DESC LIMIT 5");

faculty_header("Faculty Dashboard");
?>
    <div class="container">

        <section class="welcome-banner">
            <h1>Faculty Dashboard</h1>
            <p>Welcome, <?php echo e($_SESSION['faculty_name']); ?>. Create assignments, review student submissions and give marks.</p>
        </section>

        <?php flash_show(); ?>

        <section class="grid" style="margin-bottom: 32px;">
            <div class="card info-card">
                <span class="label">Assignments Created</span>
                <span class="value"><?php echo $total_assignments; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Total Submissions</span>
                <span class="value"><?php echo $total_submissions; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Waiting for Review</span>
                <span class="value"><?php echo $pending; ?></span>
            </div>
            <div class="card info-card">
                <span class="label">Registered Students</span>
                <span class="value"><?php echo $total_students; ?></span>
            </div>
        </section>

        <h2 class="section-title">Quick Actions</h2>
        <section class="grid" style="margin-bottom: 36px;">
            <a class="card action-card" href="create-assigments.php">
                <h3>Create Assignment</h3>
                <p>Post a new assignment for students.</p>
            </a>
            <a class="card action-card" href="assigments.php">
                <h3>Manage Assignments</h3>
                <p>Edit or delete assignments.</p>
            </a>
            <a class="card action-card" href="submissions.php">
                <h3>View Submissions</h3>
                <p>Download work, give marks and feedback.</p>
            </a>
            <a class="card action-card" href="students.php">
                <h3>Students</h3>
                <p>See registered students and their progress.</p>
            </a>
        </section>

        <h2 class="section-title">Recent Submissions</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Enrollment No.</th>
                        <th>Assignment</th>
                        <th>Submitted On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
<?php if ($recent && pgc_num_rows($recent) > 0) {
    while ($row = pgc_fetch_assoc($recent)) { ?>
                    <tr>
                        <td><?php echo e($row['student_name']); ?></td>
                        <td><?php echo e($row['enrollment_number']); ?></td>
                        <td><a href="submissions.php?id=<?php echo (int) $row['id']; ?>"><?php echo e($row['assignment_title']); ?></a></td>
                        <td><?php echo e($row['submitted_at']); ?></td>
                        <td><span class="badge <?php echo $row['status'] === 'Submitted' ? 'badge-warn' : ($row['status'] === 'Needs Revision' ? 'badge-danger' : ''); ?>"><?php echo e($row['status']); ?></span></td>
                    </tr>
<?php }
} else { ?>
                    <tr><td colspan="5" class="empty-row">No submissions yet.</td></tr>
<?php } ?>
                </tbody>
            </table>
        </div>

    </div>
<?php faculty_footer(); ?>
