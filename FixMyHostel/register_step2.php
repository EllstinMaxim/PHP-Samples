<?php
session_start();
include("includes/db.php");
include("includes/functions.php");

if (!isset($_SESSION['register_data'])) {
    header("Location: register_step1.php");
    exit();
}

$error = "";

if (isset($_POST['register'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $data = $_SESSION['register_data'];
        $name = $conn->real_escape_string($data['name']);
        $ic_number = $conn->real_escape_string($data['ic_number']);
        $registration_number = $conn->real_escape_string($data['registration_number']);
        $email = $conn->real_escape_string($data['email']);
        $block_name = $conn->real_escape_string($data['block_name']);
        $floor_number = $conn->real_escape_string($data['floor_number']);
        $room_number = $conn->real_escape_string($data['room_number']);
        $hashed_password = hashPassword($password);

        $check = $conn->query("SELECT * FROM users WHERE email='$email' OR registration_number='$registration_number'");
        if ($check->num_rows > 0) {
            $error = "Email or registration number already exists.";
        } else {
            $sql = "INSERT INTO users (name, ic_number, registration_number, email, block_name, floor_number, room_number, role, password, reset_code)
                    VALUES (
                        '$name',
                        '$ic_number',
                        '$registration_number',
                        '$email',
                        '$block_name',
                        '$floor_number',
                        '$room_number',
                        'student',
                        '$hashed_password',
                        '1'
                    )";

            if ($conn->query($sql) === TRUE) {
                unset($_SESSION['register_data']);
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Step 2 - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-6">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-2">Create Password</h2>
            <p class="text-muted mb-4">Step 2 of 2: Create your account password.</p>

            <?php if($error != "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Create New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Re-enter Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" name="register" class="btn btn-main">Create Account</button>
                <a href="register_step1.php" class="btn btn-secondary ms-2">Back</a>
            </form>
        </div>
    </div>
</div>

<script>
const password = document.getElementById('password');
const strength = document.getElementById('passwordStrength');

password.addEventListener('input', function() {
    const val = password.value;
    let text = "";
    let cls = "";

    if (val.length < 6) {
        text = "Weak";
        cls = "strength-weak";
    } else if (val.length < 10) {
        text = "Medium";
        cls = "strength-medium";
    } else {
        text = "Strong";
        cls = "strength-strong";
    }

    strength.className = "password-strength " + cls;
    strength.textContent = "Password Strength: " + text;
});
</script>
</body>
</html>
