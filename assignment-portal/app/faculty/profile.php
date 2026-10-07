<?php
require_once __DIR__ . '/../includes/faculty-auth.php';
require_faculty();

$fid = (int) $_SESSION['faculty_id'];

function load_faculty($conn, $fid) {
    $st = pgc_prepare($conn, "SELECT id, faculty_name, username, email, department, password FROM faculty WHERE id = ?");
    pgc_stmt_bind_param($st, "i", $fid);
    pgc_stmt_execute($st);
    return pgc_fetch_assoc(pgc_stmt_get_result($st));
}

// ---------- update profile ----------
if (isset($_POST['save_profile'])) {
    $name  = trim($_POST['faculty_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dept  = trim($_POST['department'] ?? '');
    if ($name === '') {
        flash_set('error', 'Name cannot be empty.');
    } else {
        $st = pgc_prepare($conn, "UPDATE faculty SET faculty_name = ?, email = ?, department = ? WHERE id = ?");
        pgc_stmt_bind_param($st, "sssi", $name, $email, $dept, $fid);
        pgc_stmt_execute($st);
        $_SESSION['faculty_name'] = $name;
        $_SESSION['faculty_department'] = $dept;
        flash_set('success', 'Profile updated.');
    }
    header("Location: profile.php");
    exit();
}

// ---------- change password ----------
if (isset($_POST['change_password'])) {
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $con = $_POST['confirm_password'] ?? '';
    $f = load_faculty($conn, $fid);
    if (!$f || !password_verify($cur, $f['password'])) {
        flash_set('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 6) {
        flash_set('error', 'New password must be at least 6 characters.');
    } elseif ($new !== $con) {
        flash_set('error', 'New passwords do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $st = pgc_prepare($conn, "UPDATE faculty SET password = ? WHERE id = ?");
        pgc_stmt_bind_param($st, "si", $hash, $fid);
        pgc_stmt_execute($st);
        flash_set('success', 'Password changed.');
    }
    header("Location: profile.php");
    exit();
}

$f = load_faculty($conn, $fid);
faculty_header("My Profile");
?>
    <div class="container">
        <div class="page-header">
            <h1>My Profile</h1>
            <p>Update your details and change your password.</p>
        </div>
        <?php flash_show(); ?>

        <div class="card" style="margin-bottom:24px;">
            <h2>Details</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" value="<?php echo e($f['username']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="faculty_name">Full Name</label>
                        <input class="form-control" type="text" id="faculty_name" name="faculty_name" maxlength="100" value="<?php echo e($f['faculty_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-control" type="email" id="email" name="email" maxlength="100" value="<?php echo e($f['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input class="form-control" type="text" id="department" name="department" maxlength="100" value="<?php echo e($f['department']); ?>">
                    </div>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit" name="save_profile" value="1">Save Profile</button></div>
            </form>
        </div>

        <div class="card">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="current_password">Current Password</label>
                        <input class="form-control" type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input class="form-control" type="password" id="new_password" name="new_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                    </div>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit" name="change_password" value="1">Change Password</button></div>
            </form>
        </div>
    </div>
<?php faculty_footer(); ?>
