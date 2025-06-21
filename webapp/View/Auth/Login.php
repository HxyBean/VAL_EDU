<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập</title>
  <link rel="stylesheet" href="/webapp/View/Auth/Login.css">
  <link rel="stylesheet" href="/webapp/View/Partial/Footer.css">
  <link rel="stylesheet" href="/webapp/View/Partial/HomeHeader.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../images/logo_transparent.png">
</head>
<body>
    <?php include __DIR__ . '/../Partial/HomeHeader.php';?>
    <div class="container">
        <div class="left">
        <img src="/webapp/View/images/sign_in_image.jpg" alt="Nhóm học sinh">
        </div>
        <div class="right">
        <h2>Đăng nhập</h2>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
            <div class="success-message">
                You have been successfully logged out.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/webapp/login">
            <label for="username">Tên đăng nhập hoặc Email *</label>
            <input type="text" name="username" id="username" placeholder="Nhập tên đăng nhập hoặc email" 
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <label for="password">Mật khẩu *</label>
            <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" minlength="6" required>

            <div class="remember-wrapper">
            <input type="checkbox" id="remember-check-box" name="remember" class="remember-checkbox">
            <div class="remember-options">
                <label for="remember-check-box">Ghi nhớ mật khẩu</label>
                <a href="/webapp/forgot-password" class="forgot-password">Quên mật khẩu</a>
            </div>
            </div>

            <button type="submit">Đăng nhập</button>
            <div class="note">
            Bạn chưa có tài khoản? <a href="/webapp/register">Đăng ký ngay</a>
            </div>
        </form>
        </div>
    </div>
    <?php include __DIR__ . '/../Partial/Footer.php';?>
</body>
</html>