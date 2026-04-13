<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if(!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student'){
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_ajax_fetch = isset($_GET['ajax']) && $_GET['ajax'] === 'fetch';
$is_ajax_send = isset($_POST['ajax']) && $_POST['ajax'] === 'send';

if($complaint_id <= 0){
    if($is_ajax_fetch || $is_ajax_send){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid complaint ID']);
        exit();
    }

    header("Location: my_complaints.php");
    exit();
}

$complaint_id_escaped = $conn->real_escape_string((string)$complaint_id);
$user_id_escaped = $conn->real_escape_string((string)$user_id);

$complaintQuery = $conn->query("
    SELECT c.*, u.name
    FROM complaints c
    JOIN users u ON c.student_id = u.id
    WHERE c.complaint_id='$complaint_id_escaped' AND c.student_id='$user_id_escaped'
    LIMIT 1
");

if(!$complaintQuery || $complaintQuery->num_rows === 0){
    if($is_ajax_fetch || $is_ajax_send){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Complaint not found']);
        exit();
    }

    header("Location: my_complaints.php");
    exit();
}

$complaint = $complaintQuery->fetch_assoc();

function fetchComplaintComments(mysqli $conn, string $complaint_id_escaped): mysqli_result|false {
    return $conn->query("
        SELECT cc.*, u.name, u.role
        FROM complaint_comments cc
        JOIN users u ON cc.commented_by = u.id
        WHERE cc.complaint_id='$complaint_id_escaped'
        ORDER BY cc.commented_at ASC
    ");
}


function markRead(mysqli $conn, string $complaint_id_escaped, string $user_id_escaped): void {
    $conn->query("
        INSERT INTO complaint_comment_reads (complaint_id, user_id, last_read_at)
        VALUES ('$complaint_id_escaped', '$user_id_escaped', NOW())
        ON DUPLICATE KEY UPDATE last_read_at = NOW()
    ");
}

if($is_ajax_send){
    header('Content-Type: application/json');

    $comment = trim($_POST['comment'] ?? '');
    if($comment === ''){
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit();
    }

    $comment = $conn->real_escape_string($comment);

    $insertOk = $conn->query("
        INSERT INTO complaint_comments (complaint_id, commented_by, comment)
        VALUES ('$complaint_id_escaped', '$user_id_escaped', '$comment')
    ");

    if(!$insertOk){
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        exit();
    }

    $commentId = (int)$conn->insert_id;

    $conn->query("
        UPDATE complaints
        SET comment_count = comment_count + 1,
            last_commented_at = NOW()
        WHERE complaint_id='$complaint_id_escaped'
    ");

    createCommentNotifications($conn, (int)$complaint_id, $commentId, $user_id);
    markRead($conn, $complaint_id_escaped, $user_id_escaped);

    echo json_encode(['success' => true]);
    exit();
}

if($is_ajax_fetch){
    header('Content-Type: application/json');

    markRead($conn, $complaint_id_escaped, $user_id_escaped);
    $comments = fetchComplaintComments($conn, $complaint_id_escaped);

    echo json_encode([
        'success' => true,
        'html' => renderCommentsHtml($comments, $user_id)
    ]);
    exit();
}

markRead($conn, $complaint_id_escaped, $user_id_escaped);
$comments = fetchComplaintComments($conn, $complaint_id_escaped);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complaint Comments</title>

<link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php renderSidebar('my_complaints.php'); ?>
    <?php renderTopNavbar('dashboard.php'); ?>

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                    <div class="card card-widget">
                        <div class="card-header">
                            <div class="user-block user-block-no-image">
                                <span class="username">Complaint #<?php echo $complaint_id; ?> Comments</span>
                                <span class="description"><?php echo htmlspecialchars((string)$complaint['title']); ?></span>
                            </div>

                            <div class="card-tools">
                                <a href="my_complaints.php" class="btn btn-tool"><i class="fas fa-arrow-left"></i></a>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if(!empty($complaint['issue_image'])) { ?>
                                <img class="img-fluid mb-3" src="../uploads/<?php echo htmlspecialchars((string)$complaint['issue_image']); ?>" alt="Issue Image">
                            <?php } ?>

                            <p><?php echo nl2br(htmlspecialchars((string)$complaint['description'])); ?></p>

                            <span class="text-muted">
                                Status: <?php echo htmlspecialchars((string)$complaint['status']); ?> |
                                Priority: <?php echo htmlspecialchars((string)$complaint['priority_level']); ?>
                            </span>
                        </div>

                        <div class="card-footer card-comments comments-box" id="chatBox">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <?php echo renderCommentsHtml($comments, $user_id); ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form id="chatForm">
                                <div class="img-push">
                                    <textarea id="commentInput" name="comment" class="form-control" rows="2" placeholder="Write a comment..." required></textarea>
                                </div>
                                <button type="submit" id="sendBtn" class="btn btn-main btn-sm mt-2">Post Comment</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/jquery/jquery.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/js/adminlte.min.js"></script>

<script>
const complaintId = <?php echo (int)$complaint_id; ?>;
const chatBox = document.getElementById('chatBox');
const chatForm = document.getElementById('chatForm');
const commentInput = document.getElementById('commentInput');
const sendBtn = document.getElementById('sendBtn');

function isNearBottom() {
    return (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight) < 80;
}

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function refreshChat(forceScroll = false) {
    const shouldScroll = forceScroll || isNearBottom();

    $.get('complaint_comments.php', { id: complaintId, ajax: 'fetch' }, function(response) {
        if (!response || !response.success) {
            return;
        }

        chatBox.innerHTML = response.html;

        if (shouldScroll) {
            scrollToBottom();
        }
    }, 'json');
}

chatForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const message = commentInput.value.trim();
    if (!message) {
        return;
    }

    sendBtn.disabled = true;

    $.post('complaint_comments.php?id=' + complaintId, { ajax: 'send', comment: message }, function(response) {
        sendBtn.disabled = false;

        if (!response || !response.success) {
            alert((response && response.message) ? response.message : 'Unable to send message.');
            return;
        }

        commentInput.value = '';
        refreshChat(true);
    }, 'json').fail(function() {
        sendBtn.disabled = false;
        alert('Network error while sending message.');
    });
});

window.onload = () => {
    scrollToBottom();
    setInterval(() => refreshChat(false), 3000);
};
</script>

</body>
</html>
