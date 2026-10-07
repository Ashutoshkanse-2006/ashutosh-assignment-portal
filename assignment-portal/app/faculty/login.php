<?php
require_once __DIR__ . '/../includes/faculty-auth.php';

if (isset($_SESSION['faculty_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = pgc_prepare($conn, "SELECT id, faculty_name, department, password FROM faculty WHERE username = ?");
    pgc_stmt_bind_param($st, "s", $username);
    pgc_stmt_execute($st);
    $row = pgc_fetch_assoc(pgc_stmt_get_result($st));

    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['faculty_id'] = (int) $row['id'];
        $_SESSION['faculty_name'] = $row['faculty_name'];
        $_SESSION['faculty_department'] = $row['department'];
        header("Location: dashboard.php");
        exit();
    }
    $error = "Invalid username or password.";
}

faculty_header("Faculty Login", false);
?>
    <div class="container auth-wrapper">
        <div class="card auth-card">
            <h1>Faculty Login</h1>
            <p class="intro">Enter your faculty credentials to manage assignments.</p>

<?php if ($error !== "") { ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
<?php } ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input class="form-control" type="text" id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" type="password" id="password" name="password" required>
                </div>

                <button class="btn btn-primary btn-block" type="submit" name="login">Login</button>
            </form>

            <p class="form-footer">Are you a student? <a href="../login.php">Student login</a></p>
        </div>
    </div>
<?php faculty_footer(); ?>
