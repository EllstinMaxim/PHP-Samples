<?php

function renderSidebar($activePage = '')
{
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
    $userName = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
    $menuItems = [];

    if ($role === 'student') {
        $menuItems = [
            ['href' => 'dashboard.php', 'label' => 'Dashboard'],
            ['href' => 'submit_complaint.php', 'label' => 'Submit Complaint'],
            ['href' => 'my_complaints.php', 'label' => 'My Complaints'],
        ];
    } elseif (in_array($role, ['maintenance_associate', 'maintenance_supervisor', 'maintenance'], true)) {
        $menuItems = [
            ['href' => 'dashboard.php', 'label' => 'Dashboard'],
            ['href' => 'manage_complaints.php', 'label' => 'Manage Complaints'],
        ];
    } elseif (isSuperAdmin($role) || $role === 'admin') {
        $menuItems = [
            ['href' => 'dashboard.php', 'label' => 'Dashboard'],
            ['href' => 'manage_users.php', 'label' => 'Manage Users'],
            ['href' => 'manage_complaints.php', 'label' => 'Complaint Review'],
        ];
    }

    $iconMap = [
        'dashboard.php' => 'fas fa-tachometer-alt',
        'submit_complaint.php' => 'fas fa-plus-circle',
        'my_complaints.php' => 'fas fa-list',
        'manage_users.php' => 'fas fa-users-cog',
        'manage_complaints.php' => 'fas fa-clipboard-check',
    ];

    echo '<aside class="main-sidebar sidebar-dark-primary elevation-4">';
    echo '<a href="dashboard.php" class="brand-link">';
    echo '<i class="fas fa-tools brand-image img-circle elevation-3" style="opacity: .8; font-size: 1rem; line-height: 33px; text-align:center;"></i>';
    echo '<span class="brand-text font-weight-light">FixMyHostel</span>';
    echo '</a>';
    echo '<div class="sidebar">';
    echo '<div class="user-panel mt-3 pb-3 mb-3 d-flex">';
    echo '<div class="image">';
    echo '<i class="fas fa-user-circle text-light" style="font-size: 2rem;"></i>';
    echo '</div>';
    echo '<div class="info">';
    echo '<a href="#" class="d-block">' . htmlspecialchars($userName) . '</a>';
    echo '</div>';
    echo '</div>';
    echo '<nav class="mt-2">';
    echo '<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">';

    foreach ($menuItems as $item) {
        $isActive = ($item['href'] === $activePage);
        $activeClass = $isActive ? ' active' : '';
        $iconClass = isset($iconMap[$item['href']]) ? $iconMap[$item['href']] : 'fas fa-circle';
        echo '<li class="nav-item">';
        echo '<a href="' . $item['href'] . '" class="nav-link' . $activeClass . '">';
        echo '<i class="nav-icon ' . $iconClass . '"></i>';
        echo '<p>' . $item['label'] . '</p>';
        echo '</a>';
        echo '</li>';
    }

    echo '<li class="nav-item mt-2">';
    echo '<a href="../logout.php" class="nav-link nav-logout">';
    echo '<i class="nav-icon fas fa-sign-out-alt"></i>';
    echo '<p>Logout</p>';
    echo '</a>';
    echo '</li>';
    echo '</ul>';
    echo '</nav>';
    echo '</div>';
    echo '</aside>';
}

function renderTopNavbar($homeHref = 'dashboard.php')
{
    echo '<nav class="main-header navbar navbar-expand navbar-white navbar-light">';
    echo '<ul class="navbar-nav">';
    echo '<li class="nav-item">';
    echo '<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>';
    echo '</li>';
    echo '<li class="nav-item d-none d-sm-inline-block">';
    echo '<a href="' . $homeHref . '" class="nav-link">Home</a>';
    echo '</li>';
    echo '<li class="nav-item d-none d-sm-inline-block">';
    echo '<a href="#" class="nav-link">Contact</a>';
    echo '</li>';
    echo '</ul>';
    echo '</nav>';
}
