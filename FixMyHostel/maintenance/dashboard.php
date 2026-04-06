<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "maintenance"){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['update_status'])){
    $id = $_POST['complaint_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE complaints SET status='$status' WHERE complaint_id='$id' AND department='Maintenance'");
}

$total = $conn->query("SELECT COUNT(*) AS total FROM complaints")->fetch_assoc();
$pending = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='Pending'")->fetch_assoc();
$progress = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='In Progress'")->fetch_assoc();
$resolved = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='Resolved'")->fetch_assoc();

$result = $conn->query("SELECT * FROM complaints ORDER BY complaint_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Dashboard - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>FixMyHostel</h4>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar">
                <h3 class="section-title mb-1">Maintenance Dashboard</h3>
                <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3"><div class="stat-card bg-stat-1"><div>Total</div><h3><?php echo $total['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-1"><div>Pending</div><h3><?php echo $pending['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-2"><div>In Progress</div><h3><?php echo $progress['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-3"><div>Resolved</div><h3><?php echo $resolved['total']; ?></h3></div></div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Complaint ID</th>
                                <th>Student Email</th>
                                <th>Block</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0) { while($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><a href="../view_complaint.php?id=<?php echo $row['complaint_id']; ?>">#<?php echo $row['complaint_id']; ?></a></td>
                                    <td><?php echo $row['student_email']; ?></td>
                                    <td><?php echo $row['block_name']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td><?php echo priorityFlag($row['priority_level']); ?></td>
                                    <td>
                                        <?php
                                        if($row['status'] == 'Pending') echo "<span class='badge-pending'>Pending</span>";
                                        elseif($row['status'] == 'In Progress') echo "<span class='badge-progress'>In Progress</span>";
                                        else echo "<span class='badge-resolved'>Resolved</span>";
                                        ?>
                                    </td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td>
                                        <?php if($row['department'] == 'Maintenance') { ?>
                                            <form method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="In Progress" <?php if($row['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                                                    <option value="Resolved" <?php if($row['status']=='Resolved') echo 'selected'; ?>>Resolved</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-main btn-sm">Save</button>
                                            </form>
                                        <?php } else { ?>
                                            <span class="locked-text">Admin Only</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } } else { ?>
                                <tr><td colspan="8" class="text-center py-4">No complaints found.</td></tr>
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