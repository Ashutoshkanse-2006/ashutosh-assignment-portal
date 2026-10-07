<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$result = pgc_query($conn, "SELECT a.*, f.faculty_name,
    (SELECT COUNT(*) FROM assignments s WHERE s.assignment_title = a.title) AS submission_count
    FROM faculty_assignments a
    LEFT JOIN faculty f ON f.id = a.faculty_id
    ORDER BY a.due_date DESC, a.id DESC");

faculty_header("Assignments");
?>
    <div class="container">

        <div class="page-header page-header-row">
            <div>
                <h1>Assignments</h1>
                <p>All assignments posted by faculty.</p>
            </div>
            <a class="btn btn-primary" href="create-assigments.php">+ Create Assignment</a>
        </div>

        <?php flash_show(); ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>For</th>
                        <th>Due Date</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php if ($result && pgc_num_rows($result) > 0) {
    while ($row = pgc_fetch_assoc($result)) {
        $mine = ((int) $row['faculty_id'] === (int) $_SESSION['faculty_id']);
        $overdue = $row['due_date'] < date('Y-m-d'); ?>
                    <tr>
                        <td>
                            <strong><?php echo e($row['title']); ?></strong><br>
                            <small class="muted">by <?php echo e($row['faculty_name'] ?? 'Unknown'); ?></small>
                        </td>
                        <td><?php echo e($row['subject']); ?></td>
                        <td><?php echo e($row['department']); ?> / <?php echo $row['semester'] === 'All' ? 'All Sem' : 'Sem ' . e($row['semester']); ?></td>
                        <td>
                            <?php echo e(date('d-m-Y', strtotime($row['due_date']))); ?>
                            <?php if ($overdue) { ?><span class="badge badge-danger">Closed</span><?php } ?>
                        </td>
                        <td><a href="submissions.php?title=<?php echo urlencode($row['title']); ?>"><?php echo (int) $row['submission_count']; ?></a></td>
                        <td class="actions">
<?php if ($mine) { ?>
                            <a class="btn btn-outline btn-sm" href="edit-assigments.php?id=<?php echo (int) $row['id']; ?>">Edit</a>
                            <form method="POST" action="delete-assigments.php" class="inline-form" data-confirm="Delete this assignment?">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
<?php } else { ?>
                            <span class="muted">&mdash;</span>
<?php } ?>
                        </td>
                    </tr>
<?php }
} else { ?>
                    <tr><td colspan="6" class="empty-row">No assignments yet. Click "Create Assignment" to add one.</td></tr>
<?php } ?>
                </tbody>
            </table>
        </div>

    </div>
<?php faculty_footer(); ?>
