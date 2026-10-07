<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$statuses = ['Submitted', 'Reviewed', 'Needs Revision'];

function status_class($s) {
    if ($s === 'Submitted') return 'badge-warn';
    if ($s === 'Needs Revision') return 'badge-danger';
    return '';
}

// ---------- Save marks / feedback ----------
if (isset($_POST['grade'])) {
    $id       = (int) ($_POST['id'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');
    $status   = $_POST['status'] ?? 'Reviewed';
    if (!in_array($status, $statuses, true)) $status = 'Reviewed';

    $marks = null;
    $marks_raw = trim($_POST['marks'] ?? '');
    if ($marks_raw !== '') {
        if (!ctype_digit($marks_raw) || (int) $marks_raw > 100) {
            flash_set('error', 'Marks must be a whole number from 0 to 100.');
            header("Location: submissions.php?id=" . $id);
            exit();
        }
        $marks = (int) $marks_raw;
    }

    $st = pgc_prepare($conn, "UPDATE assignments SET marks = ?, feedback = ?, status = ?, reviewed_at = NOW() WHERE id = ?");
    pgc_stmt_bind_param($st, "issi", $marks, $feedback, $status, $id);
    pgc_stmt_execute($st);

    flash_set('success', 'Marks and feedback saved.');
    header("Location: submissions.php?id=" . $id);
    exit();
}

// ---------- Single submission (review page) ----------
$view_id = (int) ($_GET['id'] ?? 0);
if ($view_id > 0) {
    $st = pgc_prepare($conn, "SELECT a.*, s.email, s.department, s.semester
        FROM assignments a LEFT JOIN students s ON s.enrollment_number = a.enrollment_number WHERE a.id = ?");
    pgc_stmt_bind_param($st, "i", $view_id);
    pgc_stmt_execute($st);
    $sub = pgc_fetch_assoc(pgc_stmt_get_result($st));

    if (!$sub) {
        flash_set('error', 'Submission not found.');
        header("Location: submissions.php");
        exit();
    }

    faculty_header("Review Submission");
    ?>
    <div class="container narrow">

        <div class="page-header">
            <h1>Review Submission</h1>
            <p><?php echo e($sub['assignment_title']); ?></p>
        </div>

        <?php flash_show(); ?>

        <div class="card" style="margin-bottom: 24px;">
            <dl class="detail-list">
                <div class="detail-row"><dt>Student</dt><dd><?php echo e($sub['student_name']); ?></dd></div>
                <div class="detail-row"><dt>Enrollment No.</dt><dd><?php echo e($sub['enrollment_number']); ?></dd></div>
                <div class="detail-row"><dt>Department / Sem</dt><dd><?php echo e($sub['department'] ?? '-'); ?> / <?php echo e($sub['semester'] ?? '-'); ?></dd></div>
                <div class="detail-row"><dt>Email</dt><dd><?php echo e($sub['email'] ?? '-'); ?></dd></div>
                <div class="detail-row"><dt>Submitted On</dt><dd><?php echo e($sub['submitted_at']); ?></dd></div>
                <div class="detail-row"><dt>File</dt><dd><a href="download.php?id=<?php echo (int) $sub['id']; ?>" target="_blank"><?php echo e($sub['assignment_file']); ?></a></dd></div>
                <div class="detail-row"><dt>Status</dt><dd><span class="badge <?php echo status_class($sub['status']); ?>"><?php echo e($sub['status']); ?></span></dd></div>
            </dl>
        </div>

        <div class="card">
            <h2>Marks &amp; Feedback</h2>
            <form method="POST" action="submissions.php">
                <input type="hidden" name="id" value="<?php echo (int) $sub['id']; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="marks">Marks (out of 100)</label>
                        <input class="form-control" type="number" id="marks" name="marks" min="0" max="100" value="<?php echo e($sub['marks']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
<?php foreach ($statuses as $s) { ?>
                            <option value="<?php echo e($s); ?>"<?php echo ($sub['status'] === $s || ($sub['status'] === 'Submitted' && $s === 'Reviewed')) ? ' selected' : ''; ?>><?php echo e($s); ?></option>
<?php } ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="feedback">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4"><?php echo e($sub['feedback']); ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit" name="grade">Save</button>
                    <a class="btn btn-outline" href="submissions.php">Back to Submissions</a>
                </div>
            </form>
        </div>

    </div>
    <?php
    faculty_footer();
    exit();
}

// ---------- List of submissions (with filters) ----------
$title  = trim($_GET['title'] ?? '');
$status = trim($_GET['status'] ?? '');
$q      = trim($_GET['q'] ?? '');

$where = [];
$types = "";
$args  = [];
if ($title !== '')  { $where[] = "a.assignment_title = ?"; $types .= "s"; $args[] = $title; }
if ($status !== '' && in_array($status, $statuses, true)) { $where[] = "a.status = ?"; $types .= "s"; $args[] = $status; }
if ($q !== '') {
    $where[] = "(a.student_name LIKE ? OR a.enrollment_number LIKE ?)";
    $types .= "ss";
    $like = '%' . $q . '%';
    $args[] = $like;
    $args[] = $like;
}

$sql = "SELECT a.id, a.student_name, a.enrollment_number, a.assignment_title, a.assignment_file, a.submitted_at, a.marks, a.status
        FROM assignments a" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY a.submitted_at DESC, a.id DESC";
$st = pgc_prepare($conn, $sql);
if ($args) pgc_stmt_bind_param($st, $types, ...$args);
pgc_stmt_execute($st);
$result = pgc_stmt_get_result($st);

// titles for the filter dropdown
$titles = pgc_query($conn, "SELECT DISTINCT assignment_title FROM assignments ORDER BY assignment_title");

faculty_header("Submissions");
?>
    <div class="container">

        <div class="page-header">
            <h1>Student Submissions</h1>
            <p><a class="btn btn-outline" href="export.php<?php echo isset($_GET['title']) && $_GET['title'] !== '' ? '?title=' . urlencode($_GET['title']) : ''; ?>">Export CSV</a></p>
            <p>Open a submission to download the file, give marks and write feedback.</p>
        </div>

        <?php flash_show(); ?>

        <form method="GET" action="submissions.php" class="toolbar">
            <input class="form-control" type="text" name="q" placeholder="Search name or enrollment no." value="<?php echo e($q); ?>">
            <select class="form-control" name="title">
                <option value="">All assignments</option>
<?php while ($t = pgc_fetch_assoc($titles)) { ?>
                <option value="<?php echo e($t['assignment_title']); ?>"<?php echo $title === $t['assignment_title'] ? ' selected' : ''; ?>><?php echo e($t['assignment_title']); ?></option>
<?php } ?>
            </select>
            <select class="form-control" name="status">
                <option value="">All statuses</option>
<?php foreach ($statuses as $s) { ?>
                <option value="<?php echo e($s); ?>"<?php echo $status === $s ? ' selected' : ''; ?>><?php echo e($s); ?></option>
<?php } ?>
            </select>
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn btn-outline" href="submissions.php">Reset</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Enrollment No.</th>
                        <th>Assignment</th>
                        <th>Submitted On</th>
                        <th>Marks</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
<?php if ($result && pgc_num_rows($result) > 0) {
    while ($row = pgc_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo e($row['student_name']); ?></td>
                        <td><?php echo e($row['enrollment_number']); ?></td>
                        <td><?php echo e($row['assignment_title']); ?></td>
                        <td><?php echo e($row['submitted_at']); ?></td>
                        <td><?php echo $row['marks'] === null ? '&mdash;' : (int) $row['marks'] . '/100'; ?></td>
                        <td><span class="badge <?php echo status_class($row['status']); ?>"><?php echo e($row['status']); ?></span></td>
                        <td class="actions">
                            <a class="btn btn-outline btn-sm" href="submissions.php?id=<?php echo (int) $row['id']; ?>">Review</a>
                            <a class="btn btn-outline btn-sm" href="download.php?id=<?php echo (int) $row['id']; ?>" target="_blank">File</a>
                        </td>
                    </tr>
<?php }
} else { ?>
                    <tr><td colspan="7" class="empty-row">No submissions found.</td></tr>
<?php } ?>
                </tbody>
            </table>
        </div>

    </div>
<?php faculty_footer(); ?>
