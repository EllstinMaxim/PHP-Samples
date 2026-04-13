<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if ($_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$user_id_escaped = $conn->real_escape_string((string)$user_id);

if (isset($_GET['ajax']) && $_GET['ajax'] === 'unread_counts') {
    header('Content-Type: application/json');

    $unreadResult = $conn->query("
        SELECT c.complaint_id, COUNT(cc.comment_id) AS unread_count
        FROM complaints c
        LEFT JOIN complaint_comment_reads r
            ON r.complaint_id = c.complaint_id AND r.user_id='$user_id_escaped'
        LEFT JOIN complaint_comments cc
            ON cc.complaint_id = c.complaint_id
           AND cc.commented_by != '$user_id_escaped'
           AND (r.last_read_at IS NULL OR cc.commented_at > r.last_read_at)
        WHERE c.department='Admin'
        GROUP BY c.complaint_id
    ");

    $counts = [];
    if ($unreadResult) {
        while ($unreadRow = $unreadResult->fetch_assoc()) {
            $counts[(string)$unreadRow['complaint_id']] = (int)$unreadRow['unread_count'];
        }
    }

    echo json_encode(['success' => true, 'counts' => $counts]);
    exit();
}

$result = $conn->query("
    SELECT u.*, c.*,
           c.comment_count,
           c.last_commented_at,
           r.last_read_at,
           (
               SELECT COUNT(*)
               FROM complaint_comments cc
               WHERE cc.complaint_id = c.complaint_id
                 AND cc.commented_by != '$user_id_escaped'
                 AND (r.last_read_at IS NULL OR cc.commented_at > r.last_read_at)
           ) AS unread_count
    FROM complaints c
    LEFT JOIN users u ON c.student_id = u.id
    LEFT JOIN complaint_comment_reads r
        ON r.complaint_id = c.complaint_id AND r.user_id='$user_id_escaped'
    WHERE c.department='Admin'
    ORDER BY c.complaint_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints</title>

    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php renderSidebar('manage_complaints.php'); ?>
    <?php renderTopNavbar('dashboard.php'); ?>

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="p-2 p-md-3">
                    <div class="topbar">
                        <h3>Manage Complaints</h3>
                        <div class="small-muted">Welcome, <?php echo $name; ?></div>
                    </div>

                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="table align-middle js-datatable">
                                <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <!-- <th>Chat</th> -->
                                    <th>Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $row['name']; ?></td>

                                        <td>
                                            <a href="../view_complaint.php?id=<?php echo $row['complaint_id']; ?>">
                                                <?php echo $row['title']; ?>
                                            </a>
                                        </td>

                                        <td><?php echo ucfirst($row['category']); ?></td>
                                        <td style="vertical-align: middle;">
                                            <?php echo priorityFlag($row['priority_level']); ?>
                                        </td>

                                        <td style="vertical-align: middle;">
                                            <?php echo statusBadge($row['status']); ?>
                                        </td>

                                        <!-- <td>
                                            <?php $unreadCount = (int)($row['unread_count'] ?? 0); ?>
                                            <a href="complaint_comments.php?id=<?php echo $row['complaint_id']; ?>" class="btn btn-app btn-app-custom m-0">
                                                <span class="badge bg-info unread-badge" data-complaint-id="<?php echo (int)$row['complaint_id']; ?>"><?php echo $unreadCount; ?></span>
                                                <i class="fas fa-envelope"></i> Comments
                                            </a>
                                        </td> -->

                                        <td>
                                            <div class="d-flex align-items-center flex-nowrap">

                                                <?php $unreadCount = (int)($row['unread_count'] ?? 0); ?>

                                                <!-- Comments Button -->
                                                 <span class="pr-3">
                                                     <a href="complaint_comments.php?id=<?php echo $row['complaint_id']; ?>" class="btn btn-app btn-app-custom gap-2 m-1">
                                                         <span class="badge bg-info unread-badge" data-complaint-id="<?php echo (int)$row['complaint_id']; ?>"><?php echo $unreadCount; ?></span>
                                                         <i class="fas fa-comments"></i> Comments
                                                     </a>
                                                 </span>
                                                <?php if ($row['status'] !== 'closed') { ?>
                                                    <!-- Status Form -->
                                                    <form method="POST" class="d-flex align-items-center">
                                                        <input type="hidden" name="complaint_id" value="<?php echo $row['complaint_id']; ?>">

                                                        <select name="status" class="form-select form-select-sm me-2" style="width:auto;">
                                                            <?php
                                                                $statuses = [
                                                                    'pending' => 'Pending',
                                                                    'in_progress' => 'In Progress',
                                                                    'resolved' => 'Resolved',
                                                                    'closed' => 'Closed'
                                                                ];

                                                                foreach ($statuses as $value => $label) {
                                                                    $selected = ($row['status'] === $value) ? 'selected' : '';
                                                                    echo "<option value='$value' $selected>$label</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                        <span class="pr-2"></span>
                                                        <button type="submit" name="update_status" class="btn btn-main btn-sm">
                                                            Update
                                                        </button>
                                                    </form>

                                                <?php } else { ?>
                                                    <span class="badge-closed ms-2">Closed</span>
                                                <?php } ?>

                                            </div>
                                        </td>
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
        columns: [
            { width: "15%" }, // Student
            { width: "20%" }, // Title
            { width: "15%" }, // Category
            { width: "10%" }, // Priority
            { width: "10%" }, // Status
            { width: "30%" }  // Action
        ]
    });

    function refreshUnreadBadges() {
        $.get('manage_complaints.php', { ajax: 'unread_counts' }, function(response) {
            if (!response || !response.success || !response.counts) {
                return;
            }

            $('.unread-badge').each(function() {
                var complaintId = String($(this).data('complaint-id'));
                var unread = Object.prototype.hasOwnProperty.call(response.counts, complaintId)
                    ? parseInt(response.counts[complaintId], 10)
                    : 0;

                $(this).text(isNaN(unread) ? 0 : unread);
            });
        }, 'json');
    }

    refreshUnreadBadges();
    setInterval(refreshUnreadBadges, 3000);
});
</script>

</body>
</html>
