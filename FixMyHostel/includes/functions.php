<?php

function detectDepartment($title, $category, $description)
{
    $text = strtolower($title . ' ' . $category . ' ' . $description);

    $admin_keywords = [
        'key', 'lost key', 'room key', 'access card', 'warden',
        'registration', 'document', 'check in', 'check out',
        'discipline', 'approval', 'payment', 'hostel pass', 'admin',
        'change room', 'room transfer', 'swap room', 'appeal',
        'complaint against roommate', 'visitor pass', 'hostel rules',
        'late return', 'curfew', 'identity card', 'student card',
        'lost card', 'warning letter', 'fine', 'hostel office',
        'application', 'placement', 'lock form', 'official letter'
    ];

    foreach ($admin_keywords as $word) {
        if (strpos($text, $word) !== false) {
            return 'Admin';
        }
    }

    $admin_categories = [
        'Access / Key',
        'Room Transfer / Placement',
        'Hostel Rules / Discipline',
        'Registration / Documentation',
        'Payment / Hostel Fees',
        'Visitor / Pass Issue',
        'Lost Card / ID'
    ];

    if (in_array($category, $admin_categories)) {
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

    if ($role === 'admin') {
        return true;
    }

    if ($role === 'maintenance') {
        return true;
    }

    return false;
}

function priorityFlag($priority)
{
    if ($priority === 'Urgent') {
        return "<span class='priority-flag flag-urgent'>🚩 Urgent</span>";
    } elseif ($priority === 'High') {
        return "<span class='priority-flag flag-high'>🟧 High</span>";
    } elseif ($priority === 'Medium') {
        return "<span class='priority-flag flag-medium'>🟩 Medium</span>";
    } else {
        return "<span class='priority-flag flag-low'>⚪ Low</span>";
    }
}
?>