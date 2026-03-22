<?php
class profile extends controllers_customer {
    private $menu_categories;
    private $provinces_model;
    private $profile_model;

    function __construct() {
        $this->menu_categories = $this->model('master_customer_m');
        $this->provinces_model = $this->model('provinces_m');
        $this->profile_model = $this->model('profile_m');
    }

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

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /web_qlsp/profile');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        $full_name      = trim($_POST['full_name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $province_code  = trim($_POST['province_code'] ?? '');
        $district_code  = trim($_POST['district_code'] ?? '');
        $ward_code      = trim($_POST['ward_code'] ?? '');
        $address_detail = trim($_POST['address_detail'] ?? '');

        if ($full_name === '' || $phone === '' || $province_code === '' || $district_code === '' || $ward_code === '' || $address_detail === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin hồ sơ.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        // --- BẮT ĐẦU XỬ LÝ ẢNH ---
        
        $avatar = ''; // Khởi tạo biến avatar

        // 1. Kiểm tra xem có file ảnh mới được upload không
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/users/";
            $file_name = time() . '_' . basename($_FILES["avatar"]["name"]);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                $avatar = $file_name; // Lấy tên ảnh mới upload
            }
        } 
        
        // 2. Nếu $avatar vẫn rỗng (không upload mới), lấy ảnh cũ từ Database
        if (empty($avatar)) {
            // SỬA: Dùng hàm user_getById (đã dùng ở Get_data) thay vì hàm get_user_info bị lỗi
            $current_user = $this->profile_model->user_getById($user_id); 

            if ($current_user) {
                // Nếu hàm model trả về mảng, lấy trường avatar
                // Đảm bảo tên trường trong Database là 'avatar'
                $avatar = isset($current_user['avatar']) ? $current_user['avatar'] : '';
            }
        }

        // --- KẾT THÚC XỬ LÝ ẢNH ---

        // Gọi user_updateProfile
        $ok = $this->profile_model->user_updateProfile(
            $user_id,
            $full_name,
            $phone,
            $province_code,
            $district_code,
            $ward_code,
            $address_detail,
            $avatar 
        );

        if ($ok) {
            $_SESSION['user_name'] = $full_name;
            $_SESSION['success'] = 'Cập nhật hồ sơ thành công.';
        } else {
            $_SESSION['error'] = 'Cập nhật hồ sơ thất bại. Vui lòng thử lại.';
        }

        header('Location: /web_qlsp/profile');
        exit;
    }

    function change_password() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: /web_qlsp/profile');
            exit;
        }

        $current_password = (string)($_POST['current_password'] ?? '');
        $new_password = (string)($_POST['new_password'] ?? '');
        $confirm_password = (string)($_POST['confirm_password'] ?? '');

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin đổi mật khẩu.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        if (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        $ok = $this->profile_model->user_changePassword($_SESSION['user_id'], $current_password, $new_password);
        if ($ok) {
            // Nếu user có dùng cookie remember, mật khẩu cũ trong cookie sẽ gây lỗi/auto login sai
            if (isset($_COOKIE['user_password'])) {
                setcookie('user_password', '', time() - 3600, '/');
            }
            $_SESSION['success'] = 'Đổi mật khẩu thành công.';
        } else {
            $_SESSION['error'] = 'Mật khẩu hiện tại không đúng hoặc đổi mật khẩu thất bại.';
        }

        header('Location: /web_qlsp/profile');
        exit;
    }

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