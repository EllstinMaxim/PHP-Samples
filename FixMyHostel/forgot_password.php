<?php
session_start();
include("includes/db.php");
include("includes/functions.php");

$message = "";
$error = "";

if (isset($_POST['send_code'])) {
    $email = trim($_POST['email']);
    $code = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $check = $conn->query("SELECT * FROM users WHERE email='$email' LIMIT 1");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE users SET reset_code='$code', reset_code_expiry='$expiry' WHERE email='$email'");

        if (sendResetCodeEmail($email, $code)) {
            $_SESSION['reset_email'] = $email;
            header("Location: verify_code.php");
            exit();
        } else {
            $error = "Code could not be sent. Email sending needs localhost mail configuration.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-5">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-2">Forgot Password</h2>
            <p class="text-muted mb-4">Enter your email to receive a reset code.</p>

            <?php if($error != "") { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>
            <?php if($message != "") { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Student Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <button type="submit" name="send_code" class="btn btn-main">Send Reset Code</button>
                <a href="login.php" class="btn btn-secondary ms-2">Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>