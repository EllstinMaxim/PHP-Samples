<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "maintenance_associate"){
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'notification_count') {

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $data = getUserNotifications($conn, $user_id);

    echo json_encode([
        'success' => true,
        'count' => $data['count'],
        'latest' => $data['latest']
    ]);
    exit();
}

include("../includes/sidebar.php");

if(!isset($_SESSION['role']) || !isMaintenanceRole($_SESSION['role'])){
    header("Location: ../login.php");
    exit();
}

$currentRole = $_SESSION['role'];
$user_id = (int)$_SESSION['user_id'];
$notificationCount = getUserNotificationCount($conn, $user_id);

if(isset($_POST['update_status'])){
    $id = $_POST['complaint_id'];
    $status = $_POST['status'];

    if (($currentRole === 'maintenance_associate' || $currentRole === 'maintenance') && $status === 'in_progress') {
        $conn->query("UPDATE complaints SET status='$status' WHERE complaint_id='$id' AND department='Maintenance'");
    } elseif ($currentRole === 'maintenance_supervisor' && $status === 'resolved') {
        $conn->query("UPDATE complaints SET status='$status' WHERE complaint_id='$id' AND department='Maintenance'");
    }
}

$total = $conn->query("SELECT COUNT(*) AS total FROM complaints")->fetch_assoc();
$pending = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='pending'")->fetch_assoc();
$progress = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='in_progress'")->fetch_assoc();
$resolved = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status='resolved'")->fetch_assoc();

$result = $conn->query("SELECT c.*, u.name AS student_name, u.email AS student_email, u.block_name, u.floor_number, u.room_number FROM complaints c JOIN users u ON c.student_id=u.id WHERE c.department='Maintenance' ORDER BY c.complaint_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Dashboard - FixMyHostel</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
        <?php renderSidebar('dashboard.php'); ?>
        <?php renderTopNavbar('dashboard.php'); ?>

        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
        <div class="p-2 p-md-3">
            <div class="topbar d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h3 class="section-title mb-1">Maintenance Dashboard</h3>
                    <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
                </div>
                <a href="notifications.php" class="btn btn-app mt-2 mt-md-0">
                    <span class="badge bg-danger"><?php echo (int)$notificationCount; ?></span>
                    <i class="fas fa-bell"></i> Notifications
                </a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3"><div class="stat-card bg-stat-1"><div>Total</div><h3><?php echo $total['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-1"><div>Pending</div><h3><?php echo $pending['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-2"><div>In Progress</div><h3><?php echo $progress['total']; ?></h3></div></div>
                <div class="col-md-3"><div class="stat-card bg-stat-3"><div>Resolved</div><h3><?php echo $resolved['total']; ?></h3></div></div>
            </div>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle js-datatable">
                        <thead>
                            <tr>
                                <th class="fw-600">Student Name</th>
                                <th class="fw-600">Complaint Title</th>
                                <th class="fw-600">Category</th>
                                <th class="fw-600">Priority</th>
                                <th class="fw-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0) { while($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $row['student_name']; ?></td>
                                    <td><a href="../view_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="text-decoration-none"><?php echo $row['title']; ?></a></td>
                                    <td><?php echo ucfirst($row['category']); ?></td>
                                    <td><?php echo priorityFlag($row['priority_level']); ?></td>
                                    <td>
                                        <?php
                                        if($row['status'] == 'pending') echo "<span class='badge-pending'>Pending</span>";
                                        elseif($row['status'] == 'in_progress') echo "<span class='badge-progress'>In Progress</span>";
                                        elseif($row['status'] == 'resolved') echo "<span class='badge-resolved'>Resolved</span>";
                                        else echo "<span class='badge-pending'>" . ucfirst($row['status']) . "</span>";
                                        ?>
                                    </td>
                                    
                                </tr>
                            <?php } } else { ?>
                                <tr><td colspan="6" class="text-center py-4">No complaints found.</td></tr>
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
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/toastr/toastr.min.js"></script>
<script>
$(function () {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000", // auto close after 3 sec
        "extendedTimeOut": "1000",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    if ("Notification" in window) {
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    }

    $('.js-datatable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: []
    });
    let lastCount = <?php echo (int)$notificationCount; ?>;

    function refreshNotificationBadge() {
        $.get('dashboard.php', { ajax: 'notification_count' }, function(response) {

            if (!response || !response.success) return;

            let newCount = response.count;

            // Update badge
            $('.btn-app .badge').text(newCount);

            if (newCount > lastCount && response.latest) {

                let actor = response.latest.actor_name;
                let title = response.latest.complaint_title;

                let message = actor + " commented on: " + title;

                // ✅ Toastr
                toastr.info(message);

                // ✅ Browser notification
                if (Notification.permission === "granted") {
                    new Notification("FixMyHostel", {
                        body: message,
                        icon: "../images/logo.png"
                    });
                }
            }

            lastCount = newCount;

        }, 'json');
    }

    // Initial load
    refreshNotificationBadge();

    // Refresh every 3 seconds
    setInterval(refreshNotificationBadge, 3000);
});
</script>
</body>
</html>
