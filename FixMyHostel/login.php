<?php
session_start();
include("includes/db.php");

$error = "";
$showForgot = false;

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['password'] === $password) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];

            if ($row['role'] == "student") {
                header("Location: student/dashboard.php");
                exit();
            } elseif ($row['role'] == "admin") {
                header("Location: admin/dashboard.php");
                exit();
            } elseif ($row['role'] == "maintenance") {
                header("Location: maintenance/dashboard.php");
                exit();
            }
        } else {
            $error = "Wrong password.";
            $showForgot = true;
        }
    } else {
        $error = "Account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixMyHostel Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="row w-100 justify-content-center">
        <div class="col-lg-10">
            <div class="card login-card">
                <div class="row g-0">
                    <div class="col-md-6 login-left d-flex flex-column justify-content-center">
                        <h1>FixMyHostel Portal</h1>
                        <p class="mt-3">A smart hostel complaint portal for students, management, and maintenance staff.</p>

                        <div class="mt-4">
                            <div class="mb-3">✔ Submit complaints online</div>
                            <div class="mb-3">✔ Track complaint progress</div>
                            <div>✔ Print full complaint reports</div>
                        </div>
                    </div>

                    <div class="col-md-6 login-right">
                        <h3 class="mb-1 fw-bold">Welcome Back</h3>
                        <p class="text-muted mb-4">Please login to continue.</p>

                        <?php if($error != "") { ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                                <?php if($showForgot) { ?>
                                    <br><a href="forgot_password.php">Forgot password?</a>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <a href="forgot_password.php">Forgot password?</a>
                            </div>

                            <div class="mb-3">
                                <a href="register_step1.php" class="btn btn-outline-primary w-100">Register New Student Account</a>
                            </div>

                            <button type="submit" name="login" class="btn btn-main w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>