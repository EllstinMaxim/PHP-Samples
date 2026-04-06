<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
$email = $_SESSION['reset_email'];

if (isset($_POST['reset'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $conn->query("UPDATE users SET password='$password', reset_code=NULL, reset_code_expiry=NULL WHERE email='$email'");
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_verified']);
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-5">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-2">Reset Password</h2>
            <p class="text-muted mb-4">Create your new password.</p>

            <?php if($error != "") { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Re-enter Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" name="reset" class="btn btn-main">Reset Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>