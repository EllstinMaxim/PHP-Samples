<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

if (isset($_POST['create_user'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $role = $conn->real_escape_string(trim($_POST['role']));
    $password = generateTemporaryPassword(10);
    $hashed_password = hashPassword($password);

    if (empty($name) || empty($email) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!in_array($role, ['maintenance_associate', 'maintenance_supervisor'], true)) {
        $error = "Invalid role selected.";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1");
        if ($check->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $sql = "INSERT INTO users (name, email, role, password, reset_code) VALUES ('$name', '$email', '$role', '$hashed_password', '1')";
            if ($conn->query($sql) === TRUE) {
                $message = "User created successfully! Email: $email | Temporary Password: $password";
            } else {
                $error = "Failed to create user: " . $conn->error;
            }
        }
    }
}

$result = $conn->query("SELECT id, name, email, role, registration_number, block_name, floor_number, room_number FROM users ORDER BY role, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - FixMyHostel Admin</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
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
                <h3 class="section-title mb-1">User Accounts</h3>
                <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
            </div>

            <?php if ($message != "") { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } ?>
            <?php if ($error != "") { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <div class="dashboard-card p-4 mb-4">
                <h5 class="fw-bold mb-3">Create Maintenance Staff</h5>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Select role</option>
                                <option value="maintenance_associate">Maintenance Associate</option>
                                <option value="maintenance_supervisor">Maintenance Supervisor</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-main">Create User</button>
                </form>
            </div>

            <?php if ($message != "") { ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php } ?>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle js-datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registration</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0) { while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                    <td><?php echo htmlspecialchars($row['registration_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['block_name'] . ' / ' . $row['floor_number'] . ' / ' . $row['room_number']); ?></td>
                                    <td>
                                        <?php if ($row['role'] === 'student') { ?>
                                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <?php } ?>
                                        <a href="reset_user_password.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning ms-2">Reset Password</a>
                                    </td>
                                </tr>
                            <?php } } else { ?>
                                <tr><td colspan="7" class="text-center py-4">No users found.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
                </div>
            </section>
        </div>
    </div>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/jquery/jquery.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/js/adminlte.min.js"></script>
<script>
$(function () {
    $('.js-datatable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: []
    });
});
</script>
</body>
</html>
