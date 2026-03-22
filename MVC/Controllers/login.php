<?php

class login extends controllers {
    private $userModel;

    function __construct() {
        // Khởi tạo Model xử lý người dùng
        $this->userModel = $this->model("users_m");
        
    }

  function login() {
        if (isset($_POST['btn_login'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);

            $user = $this->userModel->users_checkLogin($email);

             // Kiểm tra mật khẩu (so sánh thuần)
            if ($user && $password == $user['password']) {
                
                // 1. Thiết lập Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role']; 

                // 2. Xử lý Cookie ghi nhớ
                if ($remember) {
                    setcookie("user_email", $email, time() + (86400 * 30), "/");
                    setcookie("user_password", $password, time() + (86400 * 30), "/");
                }

                // 3. Chuẩn bị thông tin điều hướng
                $redirectUrl = ($_SESSION['user_role'] == 'admin') ? '/web_qlsp/overview' : '/web_qlsp/home';
                $roleName = ($_SESSION['user_role'] == 'admin') ? 'Quản trị viên' : 'Khách hàng';
                $welcomeMsg = "Chào mừng " . $_SESSION['user_name'] . "!";

                // 4. HIỂN THỊ MODAL PREMIUM
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <style>
                    .my-popup-class { border-radius: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                    .my-title-class { color: #2c3e50; font-size: 24px; }
                </style>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            title: '$welcomeMsg',
                            text: 'Bạn đang đăng nhập với quyền: $roleName',
                            icon: 'success',
                            iconColor: '#28a745',
                            background: '#ffffff',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true, // Thanh thời gian chạy dưới modal
                            customClass: {
                                popup: 'my-popup-class',
                                title: 'my-title-class'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown' // Hiệu ứng hiện ra
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp' // Hiệu ứng biến mất
                            },
                            allowOutsideClick: false
                        }).then(() => {
                            // Modal Loading thiết kế đẹp
                            Swal.fire({
                                title: 'Đang khởi tạo hệ thống',
                                html: '<div class=\"mt-3\"><i class=\"fas fa-sync fa-spin fa-2x\" style=\"color: #3498db;\"></i><p class=\"mt-2\">Vui lòng đợi trong giây lát...</p></div>',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                background: '#f8f9fa',
                                customClass: {
                                    popup: 'my-popup-class'
                                },
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            setTimeout(() => {
                                window.location.href = '$redirectUrl';
                            }, 1500);
                        });
                    };
                </script>";
                exit();

            } else {
                // Trường hợp thất bại với thiết kế đồng bộ
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'error',
                            title: '<span style=\"color:#e74c3c\">Đăng nhập thất bại</span>',
                            text: 'Email hoặc mật khẩu không chính xác, vui lòng kiểm tra lại!',
                            confirmButtonText: '<i class=\"fa fa-arrow-left\"></i> Thử lại ngay',
                            confirmButtonColor: '#e74c3c',
                            background: '#fff',
                            customClass: {
                                popup: 'my-popup-class'
                            }
                        }).then(() => {
                            window.history.back();
                        });
                    };
                </script>";
            }

        }
    }
    function logout() {
        // 1. Xóa sạch Session trên Server
        if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
        session_unset();
        session_destroy();

        // 2. Xóa sạch Cookie trên trình duyệt (để không bị Bridge tự động đăng nhập lại)
        if (isset($_COOKIE['user_email'])) {
            setcookie("user_email", "", time() - 3600, "/");
            setcookie("user_password", "", time() - 3600, "/");
        }

        // 3. ĐIỀU HƯỚNG VỀ TRANG CHỦ (Trạng thái chưa đăng nhập)
        header("Location: /web_qlsp/home"); 
        exit();
    }

}