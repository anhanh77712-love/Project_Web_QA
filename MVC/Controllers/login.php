<?php
class login extends controllers {
    private $userModel;

    function __construct() {
        $this->userModel = $this->model("users_m");
    }

    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // API: XỬ LÝ ĐĂNG NHẬP
    function login() {
        $this->setApiHeader();
         

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // $email = $_POST['email'] ?? '';
            // $password = $_POST['password'] ?? '';
            // $remember = isset($_POST['remember']);
            // Đọc dữ liệu từ raw JSON body
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                // Lấy thông tin từ JSON, nếu không có thì thử lấy từ $_POST để tương thích ngược
                $email = $data['email'] ?? ($_POST['email'] ?? '');
                $password = $data['password'] ?? ($_POST['password'] ?? '');
                $remember = isset($data['remember']) || isset($_POST['remember']);

            $user = $this->userModel->users_checkLogin($email);

            // Kiểm tra mật khẩu (So sánh thuần theo logic hiện tại của bạn)
            if ($user && $password == $user['password']) {
                
                if (session_status() === PHP_SESSION_NONE) session_start();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role']; 
                $_SESSION['user_avatar'] = $user['avatar'] ?? 'default-avatar.png'; 

                // Xử lý Cookie ghi nhớ
                if ($remember) {
                    setcookie("user_email", $email, time() + (86400 * 30), "/");
                    setcookie("user_password", $password, time() + (86400 * 30), "/");
                } else {
                    setcookie("user_email", "", time() - 3600, "/");
                    setcookie("user_password", "", time() - 3600, "/");
                }

                $redirectUrl = ($_SESSION['user_role'] == 'admin') ? '/web_qlsp/overview' : '/web_qlsp/home';
                $roleName = ($_SESSION['user_role'] == 'admin') ? 'Quản trị viên' : 'Khách hàng';
                
                echo json_encode([
                    'success' => true,
                    'redirect' => $redirectUrl,
                    'roleName' => $roleName,
                    'userName' => $_SESSION['user_name']
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email hoặc mật khẩu không chính xác, vui lòng kiểm tra lại!'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']);
        }
        exit;
    }

    // ĐĂNG XUẤT (Giữ nguyên luồng Redirect vì đây là thao tác chuyển trang)
    function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        if (isset($_COOKIE['user_email'])) {
            setcookie("user_email", "", time() - 3600, "/");
            setcookie("user_password", "", time() - 3600, "/");
        }

        header("Location: /web_qlsp/home"); 
        exit();
    }
}
?>