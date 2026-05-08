<?php
class profile extends controllers_customer {
    private $menu_categories;
    private $provinces_model;
    private $profile_model;

    function __construct() {
        parent::__construct(); // Vẫn gọi cha để khởi động session
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || strpos($_SERVER['REQUEST_URI'], 'add') !== false) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Tài khoản Quản trị viên (Admin) không có quyền này!']);
                exit;
            }

            echo "<script>
                    alert('Lỗi: Bạn đang đăng nhập bằng quyền Admin, không có quyền này!');
                    window.location.href = '/web_qlsp/home';
                  </script>";
            exit;
        }
        $this->menu_categories = $this->model('master_customer_m');
        $this->provinces_model = $this->model('provinces_m');
        $this->profile_model = $this->model('profile_m');
    }

    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. TẢI GIAO DIỆN HỒ SƠ
    function Get_data() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $user_info = $this->profile_model->user_getById($user_id);

        $list_provinces = $this->provinces_model->provinces_selectAll();

        $user_districts = null;
        $user_wards = null;
        if ($user_info && !empty($user_info['province_code'])) {
            $user_districts = $this->provinces_model->districts_selectByProvince($user_info['province_code']);
        }
        if ($user_info && !empty($user_info['district_code'])) {
            $user_wards = $this->provinces_model->wards_selectByDistrict($user_info['district_code']);
        }

        $this->view('Master_customer', [
            'Page' => 'profile_v',
            'title' => 'Hồ sơ cá nhân',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'provinces' => $list_provinces,
            'user_info' => $user_info,
            'user_districts' => $user_districts,
            'user_wards' => $user_wards
        ]);
    }

    // 2. API CẬP NHẬT HỒ SƠ & ẢNH ĐẠI DIỆN
    public function api_update() {
        $this->setApiHeader();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức.']); exit;
        }

        $user_id = $_SESSION['user_id'];

        $full_name      = trim($_POST['full_name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $province_code  = trim($_POST['province_code'] ?? '');
        $district_code  = trim($_POST['district_code'] ?? '');
        $ward_code      = trim($_POST['ward_code'] ?? '');
        $address_detail = trim($_POST['address_detail'] ?? '');

        if ($full_name === '' || $phone === '' || $province_code === '' || $district_code === '' || $ward_code === '' || $address_detail === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin hồ sơ.']); exit;
        }

        $avatar = ''; 

        // Xử lý upload ảnh
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/users/";
            $file_name = time() . '_' . basename($_FILES["avatar"]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $avatar = $file_name; 
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi lưu ảnh lên máy chủ.']); exit;
            }
        } 
        
        // Nếu không có ảnh mới, lấy lại ảnh cũ
        if (empty($avatar)) {
            $current_user = $this->profile_model->user_getById($user_id); 
            if ($current_user) {
                $avatar = isset($current_user['avatar']) ? $current_user['avatar'] : '';
            }
        }

        $ok = $this->profile_model->user_updateProfile(
            $user_id, $full_name, $phone, $province_code, $district_code, $ward_code, $address_detail, $avatar 
        );

        if ($ok) {
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_avatar'] = $avatar; // Cập nhật luôn avatar trên Navbar

            echo json_encode(['success' => true, 'message' => 'Cập nhật hồ sơ thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật hồ sơ thất bại. Vui lòng thử lại.']);
        }
        exit;
    }

    // 3. API ĐỔI MẬT KHẨU
    function api_change_password() {
        $this->setApiHeader();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập.']); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức.']); exit;
        }

        $current_password = (string)($_POST['current_password'] ?? '');
        $new_password = (string)($_POST['new_password'] ?? '');
        $confirm_password = (string)($_POST['confirm_password'] ?? '');

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin đổi mật khẩu.']); exit;
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.']); exit;
        }

        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']); exit;
        }

        $ok = $this->profile_model->user_changePassword($_SESSION['user_id'], $current_password, $new_password);
        
        if ($ok) {
            // Xóa cookie để tránh auto-login sai mật khẩu cũ
            if (isset($_COOKIE['user_password'])) {
                setcookie('user_password', '', time() - 3600, '/');
            }
            echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng hoặc lỗi hệ thống.']);
        }
        exit;
    }

    // 4. API TRẢ VỀ QUẬN/HUYỆN/XÃ (Dùng cho form)
    function get_districts($p_code) {
        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($row = mysqli_fetch_assoc($districts)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }

    function get_wards($d_code) {
        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($row = mysqli_fetch_assoc($wards)) {
            echo "<option value='" . $row['code'] . "'>" . $row['name'] . "</option>";
        }
        exit;
    }
}
?>