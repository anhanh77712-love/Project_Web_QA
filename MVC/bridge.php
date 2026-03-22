<?php
    include_once './MVC/Core/app.php';
    include_once './MVC/Core/controllers.php';
    include_once './MVC/Core/connectDB.php';
    include_once './Public/Classes/PHPExcel.php';
    include_once './Public/Classes/PHPExcel/IOFactory.php';
     // 1. Khởi động session nếu index.php chưa khởi động
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. Logic Tự động đăng nhập
    // Nếu chưa có Session (mới mở trình duyệt) nhưng CÓ Cookie email và password
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_email']) && isset($_COOKIE['user_password'])){
        
        // Yêu cầu Model kết nối DB để kiểm tra (Sử dụng tạm biến $connect từ connectDB)
        $email_ck = $_COOKIE['user_email'];
        $pass_ck = $_COOKIE['user_password'];

        // Bạn cần khởi tạo Model ở đây hoặc dùng câu lệnh SQL thuần để check nhanh
        // Ở đây tôi hướng dẫn cách dùng SQL thuần trong Bridge để tránh lỗi khởi tạo class
        require_once "./MVC/Core/connectDB.php";
        $db = new connectDB();
        $sql = "SELECT * FROM users WHERE email = '$email_ck'";
        $result = mysqli_query($db->con, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($pass_ck, $user['password'])) {
            // Nếu khớp, tự động thiết lập lại Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
        }
    }
?>