<?php
class connectDB {
    public $con;
    function __construct() {
        // Lấy thông tin từ cấu hình của Render, nếu không có thì mặc định dùng XAMPP
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: 'quanao'; // Đảm bảo đây là tên DB đúng trên XAMPP của bạn
        $port = getenv('DB_PORT') ?: '3306';

        $this->con = mysqli_init();

        // Kiểm tra xem có đang chạy trên Render (biến môi trường DB_HOST tồn tại) hay không
        if (getenv('DB_HOST')) {
            // Đang trên mạng (Aiven) -> Bắt buộc bật khiên bảo mật SSL
            mysqli_real_connect($this->con, $host, $user, $pass, $name, $port, null, MYSQLI_CLIENT_SSL);
        } else {
            // Đang ở nhà (Localhost XAMPP) -> Tắt SSL, kết nối bình thường
            mysqli_real_connect($this->con, $host, $user, $pass, $name, $port);
        }

        if (!$this->con) {
            die("Không thể kết nối đến Database: " . mysqli_connect_error());
        }
        mysqli_query($this->con, "SET NAMES 'utf8'");
    }
}
?>