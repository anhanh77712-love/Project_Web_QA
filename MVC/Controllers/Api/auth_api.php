<?php
class auth_api extends controllers_customer {
    private $userModel;

    function __construct() {
        $this->userModel = $this->model("users_m");
    }

    private function getRequestData() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return $data ? $data : $_POST;
    }

    // API POST: /web_qlsp/api/auth_api/login
    function login() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: POST');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']);
            return;
        }

        $data = $this->getRequestData();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ']);
            return;
        }

        $user = $this->userModel->users_checkLogin($email);

        if ($user && $password == $user['password']) {
            unset($user['password']);
            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'data' => ['user' => $user]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu sai']);
        }
    }

    // API POST: /web_qlsp/api/auth_api/register
    function register() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: POST');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']);
            return;
        }

        $data = $this->getRequestData();

        $full_name = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $password = $data['password'] ?? '';
        $province_code = $data['province_code'] ?? '';
        $district_code = $data['district_code'] ?? '';
        $ward_code = $data['ward_code'] ?? '';
        $address_detail = $data['address_detail'] ?? '';
        $google_id = $data['google_id'] ?? '';
        $avatar = $data['avatar'] ?? 'default-avatar.png';

        if (empty($full_name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Tên, Email, Mật khẩu không được rỗng']);
            return;
        }

        $existingUser = $this->userModel->getUserByEmail($email);
        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }

        $kq = $this->userModel->users_insert_default($full_name, $email, $phone, $password, $google_id, $avatar, $province_code, $district_code, $ward_code, $address_detail);

        if ($kq) {
            $newUser = $this->userModel->getUserByEmail($email);
            unset($newUser['password']);
            echo json_encode([
                'success' => true,
                'message' => 'Đăng ký thành công',
                'data' => ['user' => $newUser]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
    }
}
?>