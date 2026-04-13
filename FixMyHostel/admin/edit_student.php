<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$studentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($studentId <= 0) {
    header("Location: manage_users.php");
    exit();
}

$error = "";
$message = "";

$stmt = $conn->prepare("SELECT id, name, email, ic_number, registration_number, block_name, floor_number, room_number, bed_number FROM users WHERE id = ? AND role = 'student' LIMIT 1");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: manage_users.php");
    exit();
}

if (isset($_POST['update_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $ic_number = trim($_POST['ic_number']);
    $registration_number = trim($_POST['registration_number']);
    $block_name = trim($_POST['block_name']);
    $floor_number = trim($_POST['floor_number']);
    $room_number = trim($_POST['room_number']);
    $bed_number = trim($_POST['bed_number']);

    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->bind_param("si", $email, $studentId);
        $stmt->execute();
        $checkEmail = $stmt->get_result();

        if ($checkEmail->num_rows > 0) {
            $error = "This email is already used by another user.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, ic_number = ?, registration_number = ?, block_name = ?, floor_number = ?, room_number = ?, bed_number = ? WHERE id = ? AND role = 'student'");
            $stmt->bind_param("ssssssssi", $name, $email, $ic_number, $registration_number, $block_name, $floor_number, $room_number, $bed_number, $studentId);
            if ($stmt->execute()) {
                $message = "Student details updated successfully.";
                $student['name'] = $name;
                $student['email'] = $email;
                $student['ic_number'] = $ic_number;
                $student['registration_number'] = $registration_number;
                $student['block_name'] = $block_name;
                $student['floor_number'] = $floor_number;
                $student['room_number'] = $room_number;
                $student['bed_number'] = $bed_number;
            } else {
                $error = "Unable to update student details. Please try again.";
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
    <title>Edit Student - FixMyHostel Admin</title>
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
                <h3 class="section-title mb-1">Edit Student Details</h3>
                <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
            </div>

            <?php if ($message != "") { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } ?>
            <?php if ($error != "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <div class="dashboard-card p-4 mb-4">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IC Number</label>
                            <input type="text" name="ic_number" class="form-control" value="<?php echo htmlspecialchars($student['ic_number']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control" value="<?php echo htmlspecialchars($student['registration_number']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block Name</label>
                            <input type="text" name="block_name" class="form-control" value="<?php echo htmlspecialchars($student['block_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Floor Number</label>
                            <input type="text" name="floor_number" class="form-control" value="<?php echo htmlspecialchars($student['floor_number']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" value="<?php echo htmlspecialchars($student['room_number']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bed Number</label>
                            <input type="text" name="bed_number" class="form-control" value="<?php echo htmlspecialchars($student['bed_number']); ?>">
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" name="update_student" class="btn btn-main">Save Changes</button>
                        <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
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
