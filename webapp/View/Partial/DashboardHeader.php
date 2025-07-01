<header class="header">
    <div class="logo">
        <img src="/webapp/View/images/logo_transparent.png" alt="VAL Edu" style="height: 40px;">
    </div>
    <h1>VAL Edu - Xin Chào!</h1>
    <div class="tutor-info">
        <i class="fas fa-user-circle"></i>
        <span>
            <?php if (isset($user_name) && !empty($user_name)): ?>
                Chào mừng, <?= htmlspecialchars($user_name) ?>
            <?php elseif (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])): ?>
                Chào mừng, <?= htmlspecialchars($_SESSION['user_name']) ?>
            <?php elseif (isset($username) && !empty($username)): ?>
                Chào mừng, <?= htmlspecialchars($username) ?>
            <?php elseif (isset($_SESSION['username']) && !empty($_SESSION['username'])): ?>
                Chào mừng, <?= htmlspecialchars($_SESSION['username']) ?>
            <?php else: ?>
                Chào mừng, Student
            <?php endif; ?>
        </span>
        <button class="logout-btn" onclick="showLogoutModal()">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </button>
    </div>
</header>
<div id="logout-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-sign-out-alt"></i> Xác nhận đăng xuất</h3>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
        </div>
        <div class="modal-footer">
            <button class="cancel-btn" onclick="closeLogoutModal()">
                <i class="fas fa-times"></i> Hủy
            </button>
            <button class="logout-confirm-btn" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </button>
        </div>
    </div>
</div>
<script src="/webapp/View/Partial/DashboardHeader.js" defer></script>