<?php
class register extends controllers {
    private $userModel;

    function __construct() {
                parent::__construct();
        $this->userModel = $this->model("users_m");
    }

    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // API: XỬ LÝ ĐĂNG KÝ
    function do_register() {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? ''; 
            
            $province_code = $_POST['province_code'] ?? '';
            $district_code = $_POST['district_code'] ?? '';
            $ward_code = $_POST['ward_code'] ?? '';
            $address_detail = $_POST['address_detail'] ?? '';

            $google_id = $_POST['google_id'] ?? "";
            $avatar = $_POST['avatar'] ?? "default.png";

            // Gọi hàm insert từ Model
            $kq = $this->userModel->users_insert_default(
                $full_name, $email, $phone, $password, 
                $google_id, $avatar, 
                $province_code, $district_code, $ward_code, $address_detail
            );
            
            if ($kq === "EMAIL_EXISTED") {
                echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng. Vui lòng chọn email khác.']);
            } elseif ($kq === "PHONE_EXISTED") {
                echo json_encode(['success' => false, 'message' => 'Số điện thoại này đã được sử dụng.']);
            } elseif ($kq === true) {
                echo json_encode(['success' => true, 'message' => 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay bây giờ.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đăng ký thất bại do lỗi hệ thống. Vui lòng thử lại.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']);
        }
        exit;
    }
}
?>