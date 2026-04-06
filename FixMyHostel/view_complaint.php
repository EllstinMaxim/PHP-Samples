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

$result = $conn->query("SELECT * FROM complaints WHERE complaint_id='$id' LIMIT 1");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="p-4">
<div class="container">
    <div class="report-card">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2 class="fw-bold mb-0">Complaint Full Report</h2>
            <div class="print-btns">
                <button onclick="window.print()" class="btn btn-main">Print Report</button>
                <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <h3 class="fw-bold mb-4">Complaint ID: #<?php echo $complaint['complaint_id']; ?></h3>

        <div class="row">
            <div class="col-md-6 report-row">
                <span class="report-label">Student Name:</span>
                <span class="report-value"><?php echo $complaint['student_name']; ?></span>
            </div>

            <div class="col-md-6 report-row">
                <span class="report-label">Student Email:</span>
                <span class="report-value">
                    <a class="email-link" href="<?php echo $gmail_reply_link; ?>" target="_blank">
                        <?php echo $complaint['student_email']; ?>
                    </a>
                </span>
            </div>

            <div class="col-md-6 report-row">
                <span class="report-label">Phone Number:</span>
                <span class="report-value"><?php echo $complaint['phone']; ?></span>
            </div>

            <div class="col-md-6 report-row">
                <span class="report-label">Department:</span>
                <span class="report-value"><?php echo $complaint['department']; ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Block:</span>
                <span class="report-value"><?php echo $complaint['block_name']; ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Floor:</span>
                <span class="report-value"><?php echo $complaint['floor_number']; ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Room:</span>
                <span class="report-value"><?php echo $complaint['room_number']; ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Bed Number:</span>
                <span class="report-value"><?php echo $complaint['bed_number']; ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Priority:</span>
                <span class="report-value"><?php echo priorityFlag($complaint['priority_level']); ?></span>
            </div>

            <div class="col-md-4 report-row">
                <span class="report-label">Status:</span>
                <span class="report-value"><?php echo $complaint['status']; ?></span>
            </div>

            <div class="col-md-6 report-row">
                <span class="report-label">Category:</span>
                <span class="report-value"><?php echo $complaint['category']; ?></span>
            </div>

            <div class="col-md-6 report-row">
                <span class="report-label">Created At:</span>
                <span class="report-value"><?php echo $complaint['created_at']; ?></span>
            </div>
        </div>

        <hr>

        <div class="report-row">
            <div class="report-label mb-2">Complaint Title</div>
            <div class="report-value"><?php echo $complaint['title']; ?></div>
        </div>

        <div class="report-row">
            <div class="report-label mb-2">Complaint Description</div>
            <div class="report-value"><?php echo nl2br($complaint['description']); ?></div>
        </div>

        <div class="report-row">
            <div class="report-label mb-2">Issue Picture</div>
            <?php if(!empty($complaint['issue_image'])) { ?>
                <img src="uploads/<?php echo $complaint['issue_image']; ?>" alt="Issue Image" class="img-fluid rounded border" style="max-width: 420px;">
            <?php } else { ?>
                <div class="text-muted">No image uploaded.</div>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>