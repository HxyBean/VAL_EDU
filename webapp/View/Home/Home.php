<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'VAL Edu - Trung tâm Tiếng Anh') ?></title>
    <link rel="stylesheet" href="/webapp/View/Home/Home.css">
    <link rel="stylesheet" href="/webapp/View/Partial/HomeHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/Footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="View/images/logo_transparent.png">
  </head>

  <body>
    <?php include __DIR__ . '/../Partial/HomeHeader.php';?>
    <!-- Nội dung khác -->
    <main>
      <section class="hero-section">
        <div class="hero-container">
          <!-- Bên trái -->
          <div class="hero-left">
            <p class="sub-heading">VAL CAM KẾT ĐẦU RA CÁC KHÓA HỌC</p>
            <h1>Đạt aim TOEIC – IELTS sau 1 lộ trình</h1>
            <p class="description">
              VAL ENGLISH tự hào khi có tới hơn 1500+ học viên đạt 8.0+ IELTS & SAT 1500+ overall<br>
              Tất cả là nhờ: 🌼 Phương pháp giảng dạy của VAL do đội ngũ giảng viên
            </p>
            <div class="hero-buttons">
              <a href="#" class="btn-outline">Nhận tư vấn ngay</a>
              <a href="#" class="btn-solid">Đăng ký thi chứng chỉ</a>
            </div>
            <div class="hero-tags">
              <a href="#" class="tag yellow">🎯 Lựa chọn độ tuổi</a>
              <a href="#" class="tag orange">📚 Chương trình học</a>
            </div>
          </div>

          <!-- Bên phải -->
          <div class="hero-right">
            <img src="View/images/sign_in_image.jpg" alt="Tư duy ngôn ngữ toàn diện">
          </div>
        </div>
      </section>
    </main>
    <?php include __DIR__ . '/../Partial/Footer.php';?>
  </body>
  </html>