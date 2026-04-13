<?php

function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyUserPassword($password, $storedPassword, $isEncrypted = false)
{
    if ($isEncrypted) {
        return password_verify($password, $storedPassword);
    }

    return $storedPassword === $password;
}

function isSuperAdmin($role)
{
    return $role === 'admin';
}

function isMaintenanceRole($role)
{
    return in_array($role, ['maintenance_associate', 'maintenance_supervisor', 'maintenance'], true);
}

function isStudentRole($role)
{
    return $role === 'student';
}

function detectDepartment($title, $category, $description)
{
    $maintenance_categories = [
        'Electrical',
        'Water Leakage',
        'Furniture Damage',
        'Cleanliness',
        'WiFi Problem',
        'Other'
    ];

    $admin_categories = [
        'Access / Key',
        'Room Transfer / Placement',
        'Hostel Rules / Discipline',
        'Registration / Documentation',
        'Payment / Hostel Fees',
        'Visitor / Pass Issue',
        'Lost Card / ID'
    ];

    if (in_array($category, $maintenance_categories, true)) {
        return 'Maintenance';
    } elseif (in_array($category, $admin_categories, true)) {
        return 'Admin';
    }

    return 'Maintenance';
}

function sendResetCodeEmail($to, $code)
{
    $subject = "FixMyHostel Password Reset Code";
    $message = "Your FixMyHostel password reset code is: " . $code;
    $headers = "From: no-reply@fixmyhostel.com";

    return mail($to, $subject, $message, $headers);
}

function allowedComplaintAccess($role, $sessionUserId, $complaint)
{
    if (!$complaint) {
        return false;
    }

    if ($role === 'student' && $complaint['student_id'] == $sessionUserId) {
        return true;
    }

    if (isSuperAdmin($role)) {
        return true;
    }

    if (isMaintenanceRole($role)) {
        return true;
    }

    return false;
}

function generateTemporaryPassword($length = 10)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $password;
}

function priorityFlag($priority)
{
    $priority = strtolower($priority); // Convert to lowercase for case-insensitive comparison

    if ($priority === 'urgent') {
        return "<span class='priority-flag flag-urgent'>🚩Urgent</span>";
    } elseif ($priority === 'high') {
        return "<span class='priority-flag flag-high'>🟧 High</span>";
    } elseif ($priority === 'medium') {
        return "<span class='priority-flag flag-medium'>🟩 Medium</span>";
    }

    return "<span class='priority-flag flag-low'>⚪ Low</span>";
}

function statusBadge($status)
{
    $status = strtolower($status); // Convert to lowercase for case-insensitive comparison

    if ($status === 'pending') {
        return "<span class='badge-pending'>Pending</span>";
    } elseif ($status === 'in_progress') {
        return "<span class='badge-progress'>In Progress</span>";
    }
    return "<span class='badge-resolved'>Resolved</span>";
}

function createCommentNotifications(mysqli $conn, int $complaintId, int $commentId, int $actorUserId)
{
    $complaintIdEscaped = $conn->real_escape_string((string)$complaintId);
    $commentIdEscaped = $conn->real_escape_string((string)$commentId);
    $actorUserIdEscaped = $conn->real_escape_string((string)$actorUserId);

    $complaintResult = $conn->query("
        SELECT complaint_id, student_id, department
        FROM complaints
        WHERE complaint_id='$complaintIdEscaped'
        LIMIT 1
    ");

    if (!$complaintResult || $complaintResult->num_rows === 0) {
        return;
    }

    $complaint = $complaintResult->fetch_assoc();
    $studentId = (int)$complaint['student_id'];
    $department = (string)($complaint['department'] ?? '');
    $recipientIds = [];

    if ($studentId > 0 && $studentId !== $actorUserId) {
        $recipientIds[$studentId] = true;
    }

    if ($department === 'Maintenance') {
        $deptUsers = $conn->query("
            SELECT id
            FROM users
            WHERE role IN ('maintenance_associate', 'maintenance_supervisor')
        ");

        if ($deptUsers) {
            while ($deptUser = $deptUsers->fetch_assoc()) {
                $deptUserId = (int)$deptUser['id'];
                if ($deptUserId !== $actorUserId) {
                    $recipientIds[$deptUserId] = true;
                }
            }
        }
    } elseif ($department === 'Admin') {
        $adminUsers = $conn->query("
            SELECT id
            FROM users
            WHERE role='admin'
        ");

        if ($adminUsers) {
            while ($adminUser = $adminUsers->fetch_assoc()) {
                $adminUserId = (int)$adminUser['id'];
                if ($adminUserId !== $actorUserId) {
                    $recipientIds[$adminUserId] = true;
                }
            }
        }
    }

    foreach (array_keys($recipientIds) as $recipientId) {
        $recipientIdEscaped = $conn->real_escape_string((string)$recipientId);
        $conn->query("
            INSERT INTO complaint_notifications (recipient_user_id, complaint_id, comment_id, actor_user_id)
            VALUES ('$recipientIdEscaped', '$complaintIdEscaped', '$commentIdEscaped', '$actorUserIdEscaped')
        ");
    }
}

function getUserNotificationCount(mysqli $conn, int $userId)
{
    $userIdEscaped = $conn->real_escape_string((string)$userId);
    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM complaint_notifications
        WHERE recipient_user_id='$userIdEscaped'
    ");

    if (!$result || $result->num_rows === 0) {
        return 0;
    }

    $row = $result->fetch_assoc();
    return (int)($row['total'] ?? 0);
}
function getUserNotifications(mysqli $conn, int $userId)
{
    $userIdEscaped = $conn->real_escape_string((string)$userId);

    // Get count
    $countResult = $conn->query("
        SELECT COUNT(*) AS total
        FROM complaint_notifications
        WHERE recipient_user_id='$userIdEscaped'
    ");

    $count = 0;
    if ($countResult && $countResult->num_rows > 0) {
        $row = $countResult->fetch_assoc();
        $count = (int)$row['total'];
    }

    // Get latest notification (JOIN users + complaints)
    $notificationResult = $conn->query("
        SELECT 
            u.name AS actor_name,
            c.title AS complaint_title,
            cn.created_at
        FROM complaint_notifications cn
        JOIN users u ON cn.actor_user_id = u.id
        JOIN complaints c ON cn.complaint_id = c.complaint_id
        WHERE cn.recipient_user_id='$userIdEscaped'
        ORDER BY cn.created_at DESC
        LIMIT 1
    ");

    $latest = null;

    if ($notificationResult && $notificationResult->num_rows > 0) {
        $latest = $notificationResult->fetch_assoc();
    }

    return [
        'count' => $count,
        'latest' => $latest
    ];
}


function renderCommentsHtml(mysqli_result|false $comments, int $user_id): string {
    ob_start();

    if(!$comments || $comments->num_rows === 0){
        echo '<div class="comment-empty text-muted">No comments yet. Be the first to post.</div>';
        return (string)ob_get_clean();
    }

    while($row = $comments->fetch_assoc()){
        $isMine = ((int)$row['commented_by'] === $user_id);
        $roleLabel = ucwords(str_replace('_', ' ', (string)$row['role']));
        ?>
        <div class="card-comment comment-item <?php echo $isMine ? 'mine' : 'other'; ?>">
            <div class="comment-text">
                <span class="username">
                    <?php echo htmlspecialchars((string)$row['name']); ?>
                    <span class="text-muted float-right">
                        <?php echo date("d M Y h:i A", strtotime((string)$row['commented_at'])); ?>
                    </span>
                </span>
                <div class="comment-message">
                    <span class="pr-2">
                        <?php echo nl2br(htmlspecialchars((string)$row['comment'])); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php
    }

    return (string)ob_get_clean();
}



?>
