<?php
include('includes/header.php');

// Xử lý form liên hệ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Lưu vào database hoặc gửi email
    // Ở đây chỉ hiển thị thông báo thành công
    $success = true;
}
?>

<div class="container">
    <div class="page-header">
        <h1>Liên hệ với chúng tôi</h1>
    </div>
    
    <div class="contact-container">
        <div class="contact-info">
            <h2>Thông tin liên hệ</h2>
            <div class="info-item">
                <div class="info-icon">
                    <img src="assets/images/location-icon.png" alt="Địa chỉ">
                </div>
                <div class="info-text">
                    <h3>Địa chỉ</h3>
                    <p>123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon">
                    <img src="assets/images/phone-icon.png" alt="Điện thoại">
                </div>
                <div class="info-text">
                    <h3>Điện thoại</h3>
                    <p>(028) 1234 5678</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon">
                    <img src="assets/images/email-icon.png" alt="Email">
                </div>
                <div class="info-text">
                    <h3>Email</h3>
                    <p>support@shoppink.com</p>
                </div>
            </div>
            
            <div class="info-item">
                <div class="info-icon">
                    <img src="assets/images/time-icon.png" alt="Thời gian làm việc">
                </div>
                <div class="info-text">
                    <h3>Thời gian làm việc</h3>
                    <p>Thứ 2 - Thứ 6: 8:00 - 18:00</p>
                    <p>Thứ 7 - Chủ nhật: 9:00 - 17:00</p>
                </div>
            </div>
            
            <div class="social-links">
                <h3>Kết nối với chúng tôi</h3>
                <a href="#"><img src="assets/images/facebook.png" alt="Facebook"></a>
                <a href="#"><img src="assets/images/instagram.png" alt="Instagram"></a>
                <a href="#"><img src="assets/images/youtube.png" alt="YouTube"></a>
                <a href="#"><img src="assets/images/zalo.png" alt="Zalo"></a>
            </div>
        </div>
        
        <div class="contact-form">
            <h2>Gửi tin nhắn cho chúng tôi</h2>
            
            <?php if (isset($success) && $success) { ?>
                <div class="alert alert-success">
                    Cảm ơn bạn đã gửi tin nhắn. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất!
                </div>
            <?php } ?>
            
            <form method="post" action="contact.php">
                <div class="form-group">
                    <label for="name">Họ và tên *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại *</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Chủ đề *</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Nội dung tin nhắn *</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="map-container">
        <h2>Bản đồ</h2>
        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5037246467177!2d106.699611314749!3d10.776769992321632!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4102bd3a1f%3A0xdfb8126cde5d7c0!2sNguyen%20Hue%2C%20Ben%20Nghe%2C%20District%201%2C%20Ho%20Chi%20Minh%20City%2C%20Vietnam!5e0!3m2!1sen!2s!4v1623795489249!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>