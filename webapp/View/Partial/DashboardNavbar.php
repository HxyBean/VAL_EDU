<?php
// Define navigation items for each role
$navigation = [
    'admin' => [
        ['id' => 'overview', 'icon' => 'fas fa-globe', 'label' => 'Tổng Quan', 'active' => true],
        ['id' => 'manage_students', 'icon' => 'fas fa-users', 'label' => 'Quản Lý Học Viên'],
        ['id' => 'manage_teachers', 'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Quản Lý Giáo Viên'],
        ['id' => 'manage_courses', 'icon' => 'fas fa-book-open', 'label' => 'Quản Lý Khóa Học'],
        ['id' => 'settings', 'icon' => 'fas fa-cog', 'label' => 'Cài Đặt']
    ],
    'tutor' => [
        ['id' => 'overview', 'icon' => 'fas fa-home', 'label' => 'Tổng Quan', 'active' => true],
        ['id' => 'my_classes', 'icon' => 'fas fa-chalkboard', 'label' => 'Lớp Của Tôi'],
        ['id' => 'schedule', 'icon' => 'fas fa-calendar-alt', 'label' => 'Lịch Dạy'],
        ['id' => 'students', 'icon' => 'fas fa-user-graduate', 'label' => 'Học Viên'],
        ['id' => 'attendance', 'icon' => 'fas fa-clipboard-check', 'label' => 'Điểm Danh'],
        ['id' => 'settings', 'icon' => 'fas fa-cog', 'label' => 'Cài Đặt']
    ],
    'student' => [
        ['id' => 'overview', 'icon' => 'fas fa-home', 'label' => 'Tổng Quan', 'active' => true],
        ['id' => 'parent_connections', 'icon' => 'fas fa-users', 'label' => 'Phụ Huynh'],
        ['id' => 'settings', 'icon' => 'fas fa-cog', 'label' => 'Cài Đặt']
    ],
    'parent' => [
        ['id' => 'overview', 'icon' => 'fas fa-home', 'label' => 'Tổng Quan', 'active' => true],
        ['id' => 'my_children', 'icon' => 'fas fa-child', 'label' => 'Con Của Tôi'],
        ['id' => 'payments', 'icon' => 'fas fa-credit-card', 'label' => 'Thanh Toán'],
        ['id' => 'attendance', 'icon' => 'fas fa-check-circle', 'label' => 'Điểm Danh'],
        ['id' => 'settings', 'icon' => 'fas fa-cog', 'label' => 'Cài Đặt']
    ]
];

// Get user role from session or passed variable
$user_role = $user_role ?? $_SESSION['role'] ?? 'student';
$nav_items = $navigation[$user_role] ?? $navigation['student'];
?>

<nav class="navbar" id="navbar">
    <button class="navbar-toggle" id="navbarToggle">
        <i class="fas fa-chevron-left"></i>
    </button>
    <ul>
        <?php foreach ($nav_items as $item): ?>
            <li>
                <a href="#<?= $item['id'] ?>" class="nav-link <?= isset($item['active']) && $item['active'] ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i> 
                    <span><?= $item['label'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>