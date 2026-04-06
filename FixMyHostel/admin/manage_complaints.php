<?php
session_start();
include("../includes/db.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';

if(isset($_POST['update_status']))
{
    $complaint_id = $_POST['complaint_id'];
    $status = $_POST['status'];

    $conn->query("UPDATE complaints SET status='$status' WHERE complaint_id='$complaint_id'");
}

$result = $conn->query("SELECT * FROM complaints ORDER BY complaint_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - FixMyHostel Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>FixMyHostel</h4>
            <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="manage_complaints.php" class="active"><i class="bi bi-folder-check me-2"></i>Manage Complaints</a>
            <a href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar">
                <h3 class="section-title mb-1">Manage Complaints</h3>
                <div class="small-muted">Welcome, <?php echo $name; ?></div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Block</th>
                                <th>Floor</th>
                                <th>Room</th>
                                <th>Bed</th>
                                <th>Priority</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Picture</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0) { while($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $row['complaint_id']; ?></td>
                                    <td><?php echo $row['student_name']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['block_name']; ?></td>
                                    <td><?php echo $row['floor_number']; ?></td>
                                    <td><?php echo $row['room_number']; ?></td>
                                    <td><?php echo $row['bed_number']; ?></td>
                                    <td><?php echo $row['priority_level']; ?></td>
                                    <td><?php echo $row['title']; ?></td>
                                    <td><?php echo $row['category']; ?></td>
                                    <td>
                                        <?php if(!empty($row['issue_image'])) { ?>
                                            <a href="../uploads/<?php echo $row['issue_image']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                        <?php } else { ?>
                                            <span class="text-muted">No Image</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td>
                                        <?php
                                        if($row['status'] == 'Pending'){
                                            echo "<span class='badge-pending'>Pending</span>";
                                        } elseif($row['status'] == 'In Progress'){
                                            echo "<span class='badge-progress'>In Progress</span>";
                                        } else {
                                            echo "<span class='badge-resolved'>Resolved</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="Pending" <?php if($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="In Progress" <?php if($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                                <option value="Resolved" <?php if($row['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-main btn-sm">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } } else { ?>
                                <tr>
                                    <td colspan="15" class="text-center py-4">No complaints available.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>