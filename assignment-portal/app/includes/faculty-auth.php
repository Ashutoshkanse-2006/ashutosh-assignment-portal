<?php
// Shared helpers for every page in app/faculty/.
// - starts the (database-backed) session
// - makes sure the faculty tables / columns exist, and seeds one default faculty login
// - require_faculty() blocks pages when nobody is logged in
// - faculty_header() / faculty_footer() print the common page layout

require_once __DIR__ . '/session.php';   // also creates $conn

function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function faculty_setup_schema($conn) {


    // default login: faculty / faculty123  (only created when there is no faculty at all)
    $r = pgc_query($conn, "SELECT COUNT(*) AS c FROM faculty");
    $row = $r ? pgc_fetch_assoc($r) : null;
    if ($row && (int) $row['c'] === 0) {
        $hash = '$2y$10$dn8W2il.rbsDlJ4gm75uGu0fgv4TiFhVA8vUqIdgcI6pu59BgPu9e';
        $st = pgc_prepare($conn, "INSERT INTO faculty (faculty_name, username, email, department, password) VALUES ('Default Faculty','faculty','faculty@portal.local','CSE',?)");
        pgc_stmt_bind_param($st, "s", $hash);
        pgc_stmt_execute($st);
    }
}

if (empty($_SESSION['faculty_schema_ok'])) {
    faculty_setup_schema($conn);
    $_SESSION['faculty_schema_ok'] = true;
}

function require_faculty() {
    if (!isset($_SESSION['faculty_id'])) {
        header("Location: login.php");
        exit();
    }
}

// one-time messages shown after a redirect (e.g. "Assignment created.")
function flash_set($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function flash_show() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

function faculty_header($title, $logged_in = true) {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> - Faculty</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/faculty.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?php echo $logged_in ? 'dashboard.php' : '../index.php'; ?>"><span class="brand-mark">AP</span><span>Faculty Portal</span></a>
        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <ul class="nav-links">
<?php if ($logged_in) { ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="assigments.php">Assignments</a></li>
            <li><a href="submissions.php">Submissions</a></li>
            <li><a href="students.php">Students</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a class="nav-cta" href="logout.php">Logout</a></li>
<?php } else { ?>
            <li><a href="../index.php">Home</a></li>
            <li><a href="../login.php">Student Login</a></li>
            <li><a class="nav-cta" href="login.php">Faculty Login</a></li>
<?php } ?>
        </ul>
    </nav>
</header>

<main>
<?php
}

function faculty_footer() {
    ?>
</main>

<footer class="site-footer">
    <div class="container">
        <p><strong>Student Assignment Submission Portal</strong></p>
        <p>Indus University &middot; Department of Computer Science &amp; Engineering</p>
    </div>
</footer>

<script src="/javascript/script.js"></script>
<script src="/javascript/faculty.js"></script>
</body>
</html>
<?php
}

// Form used by both create-assigments.php and edit-assigments.php
function assignment_form($a, $button) {
    $depts = ['All', 'CSE', 'IT', 'CE', 'AIML', 'EC'];
    ?>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="title">Assignment Title</label>
                        <input class="form-control" type="text" id="title" name="title" maxlength="255" value="<?php echo e($a['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input class="form-control" type="text" id="subject" name="subject" maxlength="100" value="<?php echo e($a['subject']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input class="form-control" type="date" id="due_date" name="due_date" value="<?php echo e($a['due_date']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select class="form-control" id="department" name="department">
<?php foreach ($depts as $d) { ?>
                            <option value="<?php echo e($d); ?>"<?php echo $a['department'] === $d ? ' selected' : ''; ?>><?php echo $d === 'All' ? 'All Departments' : e($d); ?></option>
<?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select class="form-control" id="semester" name="semester">
                            <option value="All"<?php echo $a['semester'] === 'All' ? ' selected' : ''; ?>>All Semesters</option>
<?php for ($i = 1; $i <= 8; $i++) { ?>
                            <option value="<?php echo $i; ?>"<?php echo (string) $a['semester'] === (string) $i ? ' selected' : ''; ?>>Semester <?php echo $i; ?></option>
<?php } ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo e($a['description']); ?></textarea>
                    </div>
                    <div class="form-group full">
                        <label for="instructions">Instructions</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="3" placeholder="e.g. Submit PDF format only."><?php echo e($a['instructions']); ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit" name="save"><?php echo e($button); ?></button>
                    <a class="btn btn-outline" href="assigments.php">Cancel</a>
                </div>
            </form>
<?php
}

// Reads + trims the assignment form fields from $_POST
function assignment_from_post() {
    $out = [];
    foreach (['title', 'subject', 'description', 'instructions', 'department', 'semester', 'due_date'] as $k) {
        $out[$k] = trim($_POST[$k] ?? '');
    }
    if ($out['department'] === '') $out['department'] = 'All';
    if ($out['semester'] === '')   $out['semester'] = 'All';
    return $out;
}
