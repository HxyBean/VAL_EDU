<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng ký</title>
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
      <img src="/webapp/View/images/register_image.jpg" alt="Nhóm học sinh">
    </div>
    <div class="right">
      <h2>Đăng ký tài khoản</h2>
      
      <?php if (isset($error_message) && !empty($error_message)): ?>
        <div class="error-message">
            <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="/webapp/register">
        <label for="fullname">Họ và Tên *</label>
        <input type="text" name="fullname" id="fullname" placeholder="Nhập Họ và Tên của bạn" 
               value="<?= htmlspecialchars($old_data['fullname'] ?? '') ?>" required>

        <label for="email">Email *</label>
        <input type="email" name="email" id="email" placeholder="Nhập email của bạn" 
               value="<?= htmlspecialchars($old_data['email'] ?? '') ?>" required>

        <label for="username">Tên đăng nhập *</label>
        <input type="text" name="username" id="username" placeholder="Nhập tên đăng nhập (chỉ chữ, số và _)" 
               value="<?= htmlspecialchars($old_data['username'] ?? '') ?>" required 
               pattern="[a-zA-Z0-9_]+" title="Username can only contain letters, numbers, and underscores">

        <label for="password">Mật khẩu *</label>
        <input type="password" name="password" id="password" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" minlength="6" required>

        <label for="phone">Số điện thoại</label>
        <input type="tel" name="phone" id="phone" placeholder="Nhập số điện thoại (không bắt buộc)" 
               value="<?= htmlspecialchars($old_data['phone'] ?? '') ?>">

        <label for="role">Bạn là? *</label>
        <select name="role" id="role" required>
          <option value="">-- Chọn vai trò --</option>
          <option value="student" <?= ($old_data['role'] ?? '') === 'student' ? 'selected' : '' ?>>Học viên</option>
          <option value="parent" <?= ($old_data['role'] ?? '') === 'parent' ? 'selected' : '' ?>>Phụ huynh</option>
        </select>

        <div id="birthdateContainer" class="<?= ($old_data['role'] ?? '') === 'student' ? '' : 'hidden' ?>">
          <label for="birthdate">Ngày tháng năm sinh *</label>
          <input type="date" name="birthdate" id="birthdate" value="<?= htmlspecialchars($old_data['birthdate'] ?? '') ?>">
        </div>

        <div class="remember-wrapper">
            <input type="checkbox" id="agree-terms" name="agree_terms" class="remember-checkbox" required>
          <div class="remember-options">
            <label for="agree-terms">Tôi đồng ý với điều khoản sử dụng</label>
          </div>
        </div>
        
        <button type="submit">Đăng ký</button>
        <div class="note">
          Bạn đã có tài khoản? <a href="/webapp/login">Đăng nhập ngay</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const roleSelect = document.getElementById('role');
    const birthdateContainer = document.getElementById('birthdateContainer');
    const birthdateInput = document.getElementById('birthdate');

    roleSelect.addEventListener('change', function () {
      if (this.value === 'student') {
        birthdateContainer.classList.remove('hidden');
        birthdateInput.required = true;
      } else {
        birthdateContainer.classList.add('hidden');
        birthdateInput.required = false;
      }
    });
  </script>
  <?php include __DIR__ . '/../Partial/Footer.php';?>
</body>
</html>