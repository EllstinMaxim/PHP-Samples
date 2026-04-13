<?php
session_start();
include("includes/db.php");
include("includes/functions.php");

if (!isset($_SESSION['role']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT c.*, u.name AS student_name, u.email AS student_email, u.block_name, u.floor_number, u.room_number, u.bed_number AS student_bed_number FROM complaints c JOIN users u ON c.student_id=u.id WHERE c.complaint_id='$id' LIMIT 1");
$complaint = $result->fetch_assoc();

if (!allowedComplaintAccess($role, $user_id, $complaint)) {
    die("Access denied.");
}

$gmail_reply_link = "https://mail.google.com/mail/?view=cm&fs=1&to=" . urlencode($complaint['student_email']) . "&su=" . urlencode("Reply to Complaint ID #".$complaint['complaint_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Report #<?php echo $complaint['complaint_id']; ?></title>
    <link rel="stylesheet" href="AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="hold-transition">
<div class="wrapper">
<section class="content p-3">
<div class="container-fluid">
    <div class="report-card">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="fw-bold mb-0">Complaint Full Report</h4>
            <div>
                <button onclick="window.print()" class="btn btn-primary btn-sm">Print</button>
                <a href="javascript:history.back()" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <h5 class="fw-bold mb-3">
            Complaint ID: #<?php echo $complaint['complaint_id']; ?>
        </h5>

        <!-- TWO COLUMN GRID -->
        <div class="row">

            <div class="col-6 report-row">
                <span class="report-label">Student Name</span>
                <span class="report-value"><?php echo $complaint['student_name']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Student Email</span>
                <span class="report-value">
                    <?php echo $complaint['student_email']; ?>
                </span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Phone</span>
                <span class="report-value"><?php echo $complaint['phone']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Department</span>
                <span class="report-value"><?php echo $complaint['department']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Block</span>
                <span class="report-value"><?php echo $complaint['block_name']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Floor</span>
                <span class="report-value"><?php echo $complaint['floor_number']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Room</span>
                <span class="report-value"><?php echo $complaint['room_number']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Bed</span>
                <span class="report-value"><?php echo $complaint['student_bed_number']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Priority</span>
                <span class="report-value"><?php echo priorityFlag($complaint['priority_level']); ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Status</span>
                <span class="report-value"><?php echo $complaint['status']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Category</span>
                <span class="report-value"><?php echo $complaint['category']; ?></span>
            </div>

            <div class="col-6 report-row">
                <span class="report-label">Created</span>
                <span class="report-value"><?php echo $complaint['created_at']; ?></span>
            </div>

        </div>

        <hr>

        <!-- FULL WIDTH SECTIONS -->
        <div class="mb-2">
            <div class="report-label">Complaint Title</div>
            <div class="report-value text-start"><?php echo $complaint['title']; ?></div>
        </div>

        <div class="mb-2">
            <div class="report-label">Complaint Description</div>
            <div class="report-value text-start">
                <?php echo nl2br($complaint['description']); ?>
            </div>
        </div>

        <div class="mb-2">
            <div class="report-label">Issue Picture</div>

            <?php if(!empty($complaint['issue_image'])) { ?>
                <img src="uploads/<?php echo $complaint['issue_image']; ?>" 
                     class="img-fluid border rounded mt-1">
            <?php } else { ?>
                <div class="text-muted">No image uploaded.</div>
            <?php } ?>
        </div>

    </div>
</div>
 </section>
</div>
<script src="AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/jquery/jquery.min.js"></script>
<script src="AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/js/adminlte.min.js"></script>
</body>
</html>
