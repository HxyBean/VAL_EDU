<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quên mật khẩu</title>
  <link rel="stylesheet" href="/webapp/View/Auth/Login.css">
  <link rel="stylesheet" href="/webapp/View/Partial/Footer.css">
  <link rel="stylesheet" href="/webapp/View/Partial/HomeHeader.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/webapp/View/images/logo_transparent.png">
</head>

<body>
<?php include __DIR__ . '/../Partial/HomeHeader.php';?>
  <div class="container">
    <div class="left">
      <img src="/webapp/View/images/sign_in_image.jpg" alt="Nhóm học sinh">
    </div>
    <div class="right">
      <h2>Lấy lại mật khẩu</h2>
      
      <?php if (isset($error_message) && !empty($error_message)): ?>
        <div class="error-message">
            <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($success_message) && !empty($success_message)): ?>
        <div class="success-message">
            <?= htmlspecialchars($success_message) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="/webapp/forgot-password">
        <label for="email">Nhập email của bạn *</label>
        <input type="email" name="email" id="email" placeholder="Nhập Email của bạn" required>

        <button type="submit">Lấy lại mật khẩu</button>
        <div class="note">
          Nhớ mật khẩu? <a href="/webapp/login">Đăng nhập ngay</a>
        </div>
      </form>
    </div>
  </div>
<?php include __DIR__ . '/../Partial/Footer.php';?>
</body>
</html>