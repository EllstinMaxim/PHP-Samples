<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
$email = $_SESSION['reset_email'];

if (isset($_POST['verify'])) {
    $code = trim($_POST['code']);
    $now = date("Y-m-d H:i:s");

    $result = $conn->query("SELECT * FROM users WHERE email='$email' AND reset_code='$code' AND reset_code_expiry >= '$now' LIMIT 1");
    if ($result->num_rows > 0) {
        $_SESSION['reset_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid or expired code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-5">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-2">Verify Reset Code</h2>
            <p class="text-muted mb-4">Enter the code sent to your email.</p>

            <?php if($error != "") { ?><div class="alert alert-danger"><?php echo $error; ?></div><?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Reset Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>

                <button type="submit" name="verify" class="btn btn-main">Verify Code</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>