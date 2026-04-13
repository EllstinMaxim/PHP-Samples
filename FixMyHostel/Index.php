<?php
session_start();
include("includes/db.php");
include("includes/functions.php");

$error = "";
$showForgot = false;

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $isEncryptedPassword = isset($row['reset_code']) && (string)$row['reset_code'] === '1';
        if (verifyUserPassword($password, $row['password'], $isEncryptedPassword)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];

            if (isStudentRole($row['role'])) {
                header("Location: student/dashboard.php");
                exit();
            } elseif (isMaintenanceRole($row['role'])) {
                header("Location: maintenance/dashboard.php");
                exit();
            } elseif (isSuperAdmin($row['role'])) {
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error = "Your account role is not supported.";
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
                            <div class="mb-3">✔ Submit & manage complaints</div>
                            <div class="mb-3">✔ Track complaint progress</div>
                            <div>✔ Full complaint management</div>
                        </div>
                    </div>

                    <div class="col-md-6 login-right">
                        <h3 class="mb-1 fw-bold">Welcome Backt</h3>
                        <p class="text-muted mb-4">Login as Student, Maintenance, or Admin</p>

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

                            <button type="submit" name="login" class="btn btn-main w-100 mb-3">Login</button>

                            <div class="text-center">
                                <p class="text-muted">New student?</p>
                                <a href="register_step1.php" class="btn btn-outline-primary w-100">Register as Student</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
