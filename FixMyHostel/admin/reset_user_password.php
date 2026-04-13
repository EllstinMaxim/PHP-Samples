<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($userId <= 0) {
    header("Location: manage_users.php");
    exit();
}

$error = "";
$message = "";

$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

if (isset($_POST['update_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = hashPassword($new_password);
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_code = '1', reset_code_expiry = NULL WHERE id = ? LIMIT 1");
        $updateStmt->bind_param("si", $hashed_password, $userId);
        if ($updateStmt->execute()) {
            $message = "Password updated successfully for " . htmlspecialchars($user['name']) . ".";
        } else {
            $error = "Unable to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset User Password - FixMyHostel Admin</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
        <?php renderSidebar('manage_users.php'); ?>
        <?php renderTopNavbar('dashboard.php'); ?>
        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
        <div class="p-2 p-md-3">
            <div class="topbar">
                <h3 class="section-title mb-1">Reset Password</h3>
                <div class="small-muted">Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></div>
            </div>

            <?php if ($message != "") { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } ?>
            <?php if ($error != "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <div class="dashboard-card p-4 mb-4">
                <h5 class="fw-bold mb-3">Reset password for <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</h5>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="update_password" class="btn btn-main">Update Password</button>
                        <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
                </div>
            </section>
        </div>
    </div>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/jquery/jquery.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/js/adminlte.min.js"></script>
</body>
</html>
