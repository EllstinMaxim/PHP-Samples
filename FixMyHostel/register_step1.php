<?php
session_start();
$error = "";

if (isset($_POST['next'])) {
    $_SESSION['register_data'] = [
        'name' => trim($_POST['name']),
        'ic_number' => trim($_POST['ic_number']),
        'registration_number' => trim($_POST['registration_number']),
        'email' => trim($_POST['email']),
        'block_name' => trim($_POST['block_name']),
        'floor_number' => trim($_POST['floor_number']),
        'room_number' => trim($_POST['room_number'])
    ];

    header("Location: register_step2.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Step 1 - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-7">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-2">Student Registration</h2>
            <p class="text-muted mb-4">Step 1 of 2: Fill in your personal details.</p>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number</label>
                        <input type="text" name="ic_number" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Student Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Block Name</label>
                        <select name="block_name" class="form-select" required>
                            <option value="">Select block</option>
                            <option value="Jati">Jati</option>
                            <option value="Meranti">Meranti</option>
                            <option value="Cengal">Cengal</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Floor Number</label>
                        <select name="floor_number" class="form-select" required>
                            <option value="">Select floor</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" required>
                    </div>
                </div>

                <button type="submit" name="next" class="btn btn-main">Next</button>
                <a href="login.php" class="btn btn-secondary ms-2">Back to Login</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>