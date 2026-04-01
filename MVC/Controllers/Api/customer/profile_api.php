<?php
class profile_api extends controllers_customer {
    private $profile_model;
    private $order_model;
    private $provinces_model;

    function __construct() {
        $this->profile_model = $this->model('profile_m');
        $this->order_model = $this->model('your_order_m');
        $this->provinces_model = $this->model('provinces_m'); // Thêm model tỉnh thành
    }

    // ==========================================
    // 1. API GET: Lấy thông tin user
    // URL: /web_qlsp/api/customer/profile_api/get_profile?user_id=1
    // ==========================================
    function get_profile() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID người dùng']);
            return;
        }

        $user_info = $this->profile_model->user_getById($user_id);
        
        if ($user_info) {
            unset($user_info['password']); 
            echo json_encode(['success' => true, 'data' => $user_info]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng']);
        }
    }

    // ==========================================
    // 2. API GET: Lấy danh sách đơn hàng của user
    // URL: /web_qlsp/api/customer/profile_api/get_orders?user_id=1&status=all
    // ==========================================
    function get_orders() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        $status = isset($_GET['status']) && $_GET['status'] !== 'all' ? $_GET['status'] : null;

        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID người dùng']);
            return;
        }

        $orders = $this->order_model->orders_getByUserId($user_id, $status);
        $order_list = [];

        if ($orders && mysqli_num_rows($orders) > 0) {
            while($order = mysqli_fetch_assoc($orders)) {
                $items_res = $this->order_model->orderItems_getByOrderId($order['id']);
                $items = [];
                if ($items_res && mysqli_num_rows($items_res) > 0) {
                    while($item = mysqli_fetch_assoc($items_res)) {
                        $items[] = $item;
                    }
                }
                
                $order['items'] = $items;
                $order_list[] = $order;
            }
        }

        echo json_encode(['success' => true, 'data' => $order_list]);
    }

    // ==========================================
    // 3. API POST: Cập nhật hồ sơ & ảnh đại diện
    // Phương thức: POST (multipart/form-data)
    // URL: /web_qlsp/api/customer/profile_api/update
    // ==========================================
    function update() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Content-Type: application/json; charset=utf-8');

        // Bắt đầu session nếu cần để lấy user_id hoặc set lỗi (nếu bạn vẫn muốn dùng form submit redirect cũ)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ. Vui lòng dùng POST.']);
            return;
        }

        // Lấy user_id từ Session thay vì POST nếu form không gửi user_id
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0);
        
        if ($user_id <= 0) {
            $_SESSION['error'] = 'Thiếu ID người dùng hoặc chưa đăng nhập.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        $full_name      = trim($_POST['full_name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $province_code  = trim($_POST['province_code'] ?? '');
        $district_code  = trim($_POST['district_code'] ?? '');
        $ward_code      = trim($_POST['ward_code'] ?? '');
        $address_detail = trim($_POST['address_detail'] ?? '');

        if ($full_name === '' || $phone === '' || $province_code === '' || $district_code === '' || $ward_code === '' || $address_detail === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        // --- XỬ LÝ ẢNH ---
        $avatar = ''; 

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/users/";
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Tách phần mở rộng của file ảnh
            $file_extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            
            // Đảm bảo chỉ cho phép upload ảnh hợp lệ
            $allowed_extensions = array("jpg", "jpeg", "png", "gif", "webp");
            if (in_array($file_extension, $allowed_extensions)) {
                $file_name = 'user_' . $user_id . '_' . time() . '.' . $file_extension; 
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    $avatar = $file_name; 
                }
            } else {
                 $_SESSION['error'] = 'Định dạng file không hỗ trợ. Vui lòng chọn ảnh JPG, PNG, GIF, WEBP.';
                 header('Location: /web_qlsp/profile');
                 exit;
            }
        } 
        
        // Nếu không có ảnh mới, lấy ảnh cũ
        if (empty($avatar)) {
            $current_user = $this->profile_model->user_getById($user_id); 
            if ($current_user) {
                $avatar = isset($current_user['avatar']) ? $current_user['avatar'] : '';
            }
        }

        // Gọi model cập nhật
        $ok = $this->profile_model->user_updateProfile(
            $user_id, $full_name, $phone, $province_code, $district_code, $ward_code, $address_detail, $avatar 
        );

        // Do form bên giao diện của bạn đang dùng action redirect truyền thống, ta sẽ xử lý session thay vì JSON
        if ($ok) {
            $_SESSION['user_name'] = $full_name;
            $_SESSION['success'] = 'Cập nhật hồ sơ thành công.';
        } else {
            $_SESSION['error'] = 'Cập nhật hồ sơ thất bại. Vui lòng thử lại.';
        }

        header('Location: /web_qlsp/profile');
        exit;
    }

    // ==========================================
    // 4. API POST: Đổi mật khẩu
    // Phương thức: POST (nhận form-data hoặc raw JSON)
    // URL: /web_qlsp/api/customer/profile_api/change_password
    // ==========================================
    function change_password() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $_SESSION['error'] = 'Phương thức không hợp lệ. Vui lòng dùng POST.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($input['user_id']) ? (int)$input['user_id'] : 0);
        $current_password = (string)($input['current_password'] ?? '');
        $new_password = (string)($input['new_password'] ?? '');
        $confirm_password = (string)($input['confirm_password'] ?? '');

        if ($user_id <= 0) {
            $_SESSION['error'] = 'Thiếu ID người dùng hoặc chưa đăng nhập.';
            header('Location: /web_qlsp/profile');
            exit;
        }

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin mật khẩu.';
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

        $ok = $this->profile_model->user_changePassword($user_id, $current_password, $new_password);
        
        if ($ok) {
            if (isset($_COOKIE['user_password'])) {
                setcookie('user_password', '', time() - 3600, '/');
            }
            $_SESSION['success'] = 'Đổi mật khẩu thành công.';
        } else {
             $_SESSION['error'] = 'Mật khẩu hiện tại không đúng hoặc hệ thống gặp lỗi.';
        }
        
        header('Location: /web_qlsp/profile');
        exit;
    }

    // ==========================================
    // 5. API GET: Lấy danh sách Tỉnh/Thành phố
    // URL: /web_qlsp/api/customer/profile_api/get_provinces
    // ==========================================
    function get_provinces() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $provinces = $this->provinces_model->provinces_selectAll();
        $data = [];

        if ($provinces && mysqli_num_rows($provinces) > 0) {
            while ($row = mysqli_fetch_assoc($provinces)) {
                $data[] = $row;
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
    }

    // ==========================================
    // 6. API GET: Lấy Quận/Huyện theo mã Tỉnh/Thành
    // URL: /web_qlsp/api/customer/profile_api/get_districts?province_code=...
    // ==========================================
    function get_districts() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $p_code = trim($_GET['province_code'] ?? '');

        if ($p_code === '') {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã Tỉnh/Thành phố']);
            return;
        }

        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        $data = [];

        if ($districts && mysqli_num_rows($districts) > 0) {
            while ($row = mysqli_fetch_assoc($districts)) {
                $data[] = $row;
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
    }

    // ==========================================
    // 7. API GET: Lấy Phường/Xã theo mã Quận/Huyện
    // URL: /web_qlsp/api/customer/profile_api/get_wards?district_code=...
    // ==========================================
    function get_wards() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $d_code = trim($_GET['district_code'] ?? '');

        if ($d_code === '') {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã Quận/Huyện']);
            return;
        }

        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        $data = [];

        if ($wards && mysqli_num_rows($wards) > 0) {
            while ($row = mysqli_fetch_assoc($wards)) {
                $data[] = $row;
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
    }
}
?>