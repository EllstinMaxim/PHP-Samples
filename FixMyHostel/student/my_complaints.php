<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM complaints WHERE student_id='$user_id' ORDER BY complaint_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - FixMyHostel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>FixMyHostel</h4>
            <a href="dashboard.php">Dashboard</a>
            <a href="submit_complaint.php">Submit Complaint</a>
            <a href="my_complaints.php" class="active">My Complaints</a>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="topbar">
                <h3 class="section-title mb-1">My Complaints</h3>
                <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
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
                                        <?php if($row['status'] == 'Pending') { ?>
                                            <a href="edit_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this complaint?')">Delete</a>
                                        <?php } else { ?>
                                            <span class="locked-text">Locked</span>
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