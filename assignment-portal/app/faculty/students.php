<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$q    = trim($_GET['q'] ?? '');
$dept = trim($_GET['department'] ?? '');
$sem  = trim($_GET['semester'] ?? '');

$where = [];
$types = "";
$args  = [];
if ($q !== '') {
    $where[] = "(s.student_name LIKE ? OR s.enrollment_number LIKE ? OR s.email LIKE ?)";
    $types .= "sss";
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like);
}
if ($dept !== '') { $where[] = "s.department = ?"; $types .= "s"; $args[] = $dept; }
if ($sem !== '')  { $where[] = "s.semester = ?";   $types .= "s"; $args[] = $sem; }

$sql = "SELECT s.student_name, s.enrollment_number, s.email, s.department, s.semester,
        (SELECT COUNT(*) FROM assignments a WHERE a.enrollment_number = s.enrollment_number) AS submissions
        FROM students s" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY s.student_name";
$st = pgc_prepare($conn, $sql);
if ($args) pgc_stmt_bind_param($st, $types, ...$args);
pgc_stmt_execute($st);
$result = pgc_stmt_get_result($st);

faculty_header("Students");
?>
    <div class="container">

        <div class="page-header">
            <h1>Students</h1>
            <p>Registered students and how many assignments they have submitted.</p>
        </div>

        <form method="GET" action="students.php" class="toolbar">
            <input class="form-control" type="text" name="q" placeholder="Search name, enrollment no. or email" value="<?php echo e($q); ?>">
            <select class="form-control" name="department">
                <option value="">All departments</option>
<?php foreach (['CSE', 'IT', 'CE', 'AIML', 'EC'] as $d) { ?>
                <option value="<?php echo $d; ?>"<?php echo $dept === $d ? ' selected' : ''; ?>><?php echo $d; ?></option>
<?php } ?>
            </select>
            <select class="form-control" name="semester">
                <option value="">All semesters</option>
<?php for ($i = 1; $i <= 8; $i++) { ?>
                <option value="<?php echo $i; ?>"<?php echo $sem === (string) $i ? ' selected' : ''; ?>>Semester <?php echo $i; ?></option>
<?php } ?>
            </select>
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn btn-outline" href="students.php">Reset</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Enrollment No.</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Submissions</th>
                    </tr>
                </thead>
                <tbody>
<?php if ($result && pgc_num_rows($result) > 0) {
    while ($row = pgc_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo e($row['student_name']); ?></td>
                        <td><?php echo e($row['enrollment_number']); ?></td>
                        <td><?php echo e($row['email']); ?></td>
                        <td><?php echo e($row['department']); ?></td>
                        <td><?php echo e($row['semester']); ?></td>
                        <td><a href="submissions.php?q=<?php echo urlencode($row['enrollment_number']); ?>"><?php echo (int) $row['submissions']; ?></a></td>
                    </tr>
<?php }
} else { ?>
                    <tr><td colspan="6" class="empty-row">No students found.</td></tr>
<?php } ?>
                </tbody>
            </table>
        </div>

    </div>
<?php faculty_footer(); ?>
