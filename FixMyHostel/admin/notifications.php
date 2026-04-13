<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if(!isset($_SESSION['role']) || !isSuperAdmin($_SESSION['role'])){
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user_id_escaped = $conn->real_escape_string((string)$user_id);

if(isset($_POST['clear_one'])){
    $notification_id = (int)($_POST['notification_id'] ?? 0);
    $notification_id_escaped = $conn->real_escape_string((string)$notification_id);
    $conn->query("
        DELETE FROM complaint_notifications
        WHERE notification_id='$notification_id_escaped'
          AND recipient_user_id='$user_id_escaped'
    ");
}

if(isset($_POST['clear_all'])){
    $conn->query("
        DELETE FROM complaint_notifications
        WHERE recipient_user_id='$user_id_escaped'
    ");
}

$result = $conn->query("
    SELECT n.notification_id,
           n.created_at AS notification_created_at,
           c.complaint_id,
           c.title AS complaint_title,
           u.name AS actor_name,
           cc.comment AS last_comment_message,
           cc.commented_at AS last_commented_at
    FROM complaint_notifications n
    LEFT JOIN complaints c ON n.complaint_id = c.complaint_id
    LEFT JOIN complaint_comments cc ON n.comment_id = cc.comment_id
    LEFT JOIN users u ON n.actor_user_id = u.id
    WHERE n.recipient_user_id='$user_id_escaped'
    ORDER BY n.notification_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php renderSidebar('notifications.php'); ?>
    <?php renderTopNavbar('dashboard.php'); ?>

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="p-2 p-md-3">
                    <div class="topbar d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <h3 class="section-title mb-1">Notifications</h3>
                            <div class="small-muted">New comments on admin complaints</div>
                        </div>
                        <form method="POST" class="mt-2 mt-md-0">
                            <button type="submit" name="clear_all" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Clear All
                            </button>
                        </form>
                    </div>

                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle js-datatable">
                                <thead>
                                <tr>
                                    <th>Complaint</th>
                                    <th>Last Commented By</th>
                                    <th>Message</th>
                                    <th>Last Commented Time</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if($result && $result->num_rows > 0) { ?>
                                    <?php while($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)$row['complaint_title']); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['actor_name'] ?? 'Unknown')); ?></td>
                                            <td>
                                                <?php
                                                $msg = (string)($row['last_comment_message'] ?? '');
                                                echo htmlspecialchars(strlen($msg) > 90 ? substr($msg, 0, 90) . '...' : $msg);
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if(!empty($row['last_commented_at'])){
                                                    echo date("d M Y h:i A", strtotime((string)$row['last_commented_at']));
                                                } else {
                                                    echo "<span class='text-muted'>N/A</span>";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="complaint_comments.php?id=<?php echo (int)$row['complaint_id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-comments"></i> Open
                                                </a>
                                                <form method="POST" class="d-inline-block">
                                                    <input type="hidden" name="notification_id" value="<?php echo (int)$row['notification_id']; ?>">
                                                    <button type="submit" name="clear_one" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No notifications found.</td>
                                    </tr>
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
