<?php
class register extends controllers {
    private $userModel;

    function __construct() {

        $this->userModel = $this->model("users_m");
    }

    // Hàm xử lý dữ liệu từ Modal gửi lên
    function do_register() {
        if (isset($_POST['btn_register'])) {

            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $password = $_POST['password']; 
            
            
            $province_code = $_POST['province_code'];
            $district_code = $_POST['district_code'];
            $ward_code = $_POST['ward_code'];
            $address_detail = $_POST['address_detail'];

            $google_id = $_POST['google_id'] ?? "";
            $avatar = $_POST['avatar'] ?? "default.png";

            // 2. Gọi hàm insert từ Model
            $kq = $this->userModel->users_insert_default(
                $full_name, $email, $phone, $password, 
                $google_id, $avatar, 
                $province_code, $district_code, $ward_code, $address_detail
            );

            
            
            if ($kq === "EMAIL_EXISTED") {
                $this->show_alert('Lỗi!', 'Email này đã được sử dụng. Vui lòng chọn email khác.', 'error');
            } 
           
            elseif ($kq === "PHONE_EXISTED") {
                $this->show_alert('Lỗi!', 'Số điện thoại này đã được sử dụng.', 'error');
            } 
           
            elseif ($kq === true) {
                $this->show_alert('Thành công!', 'Đăng ký tài khoản thành công.', 'success', '/web_qlsp/home');
            } 
           
            else {
                $this->show_alert('Lỗi!', 'Đăng ký thất bại do lỗi hệ thống. Vui lòng thử lại.', 'error');
            }
        }
    }

    // Hàm phụ để hiển thị SweetAlert cho gọn code
    private function show_alert($title, $text, $icon, $redirectUrl = '/web_qlsp/home') {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script> 
        <script> 
            window.onload = function() { 
                Swal.fire({ 
                    title: '$title', 
                    text: '$text', 
                    icon: '$icon', 
                    confirmButtonText: 'OK', 
                    confirmButtonColor: '#3085d6' 
                }).then((result) => { 
                    if (result.isConfirmed) { 
                        // Nếu là lỗi thì reload lại trang hiện tại hoặc giữ nguyên
                        // Nếu thành công thì chuyển hướng
                        " . ($icon == 'success' ? "window.location.href = '$redirectUrl';" : "history.back();") . " 
                    } 
                }); 
            }; 
        </script>";
        die(); 
    }
}
?>