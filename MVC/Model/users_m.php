<?php
class users_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    function users_selectAll() {
        $sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function users_searchByName($q) {
        $q = mysqli_real_escape_string($this->con, $q);
        $sql = "SELECT * FROM users WHERE role != 'admin' AND full_name LIKE '%$q%' ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function users_delete($id) {
        $sql = "DELETE FROM users WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    public function users_insert_default($full_name, $email, $phone, $password, $google_id, $avatar, $province, $district, $ward, $address) {
    // 1. BẢO MẬT: Làm sạch dữ liệu đầu vào để tránh SQL Injection
    // (Lưu ý: $this->con là biến kết nối CSDL của bạn)
    $full_name = mysqli_real_escape_string($this->con, $full_name);
    $email     = mysqli_real_escape_string($this->con, $email);
    $phone     = mysqli_real_escape_string($this->con, $phone);
    $password  = mysqli_real_escape_string($this->con, $password); 
    

    // 2. KIỂM TRA TRÙNG EMAIL
    $check_email = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
    $rs_email = mysqli_query($this->con, $check_email);
    if (mysqli_num_rows($rs_email) > 0) {
        return "EMAIL_EXISTED"; 
    }

    // 3. KIỂM TRA TRÙNG SỐ ĐIỆN THOẠI
    $check_phone = "SELECT id FROM users WHERE phone = '$phone' LIMIT 1";
    $rs_phone = mysqli_query($this->con, $check_phone);
    if (mysqli_num_rows($rs_phone) > 0) {
        return "PHONE_EXISTED"; 
    }

    // 4. NẾU KHÔNG TRÙNG, TIẾN HÀNH THÊM MỚI (INSERT)
    $sql = "INSERT INTO users (full_name, email, phone, password, google_id, avatar, province_code, district_code, ward_code, address_detail) 
            VALUES ('$full_name', '$email', '$phone', '$password', '$google_id', '$avatar', '$province', '$district', '$ward', '$address')";
    
    $insert_result = mysqli_query($this->con, $sql);

    if ($insert_result) {
        return true; // Hoặc return "SUCCESS";
    } else {
        return false; // Lỗi do Database
    }
}
    function users_checkLogin($email) {
        // Truy vấn lấy thông tin người dùng theo email
        $sql = "SELECT * FROM users WHERE email = '$email' ";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result); // Trả về mảng thông tin người dùng hoặc null
    }
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    public function getUserById($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    // Thêm hàm này vào file users_m.php (bên dưới các hàm đã có)
    public function users_getOrderHistory($user_id) {
        $user_id = mysqli_real_escape_string($this->con, $user_id);
        // Lấy các thông tin cơ bản của đơn hàng
        $sql = "SELECT id, total_money, payment_method, status, created_at 
                FROM orders 
                WHERE user_id = '$user_id' 
                ORDER BY created_at DESC";
        return mysqli_query($this->con, $sql);
    }
}