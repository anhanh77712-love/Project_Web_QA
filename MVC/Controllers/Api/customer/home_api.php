<?php
class home_api extends controllers_customer {
    private $home_model;
    private $menu_categories;
    private $provinces_model;
    private $prd_list_model;
    private $profile_model;

    function __construct() {
        // Khởi tạo tất cả các Model cần thiết từ cả hai file
        $this->home_model = $this->model('home_m');
        $this->menu_categories = $this->model('master_customer_m');
        $this->provinces_model = $this->model('provinces_m');
        $this->prd_list_model = $this->model("product_list_m");
        $this->profile_model = $this->model('profile_m');

        // Bảo mật: Nếu Admin truy cập trang chủ, đẩy về trang quản trị
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
            header("Location: /web_qlsp/overview");
            exit();
        }
    }

    // ============================================================
    // 1. GIAO DIỆN TRANG CHỦ (VIEW)
    // ============================================================

    function Get_data() {
        $list_provinces = $this->provinces_model->provinces_selectAll();
        
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->profile_model->user_getById($_SESSION['user_id']);
        }

        // Truyền dữ liệu sang View Master_customer
        $this->view('Master_customer', [
            'Page' => 'home_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'banners' => $this->home_model->banners_getActive(),
            'sections' => $this->home_model->sections_getActive(),
            'home_model' => $this->home_model,
            'provinces' => $list_provinces,
            'user_info' => $user_info
        ]);
    }

    // ============================================================
    // 2. CÁC PHƯƠNG THỨC API (JSON)
    // ============================================================

    // API: /web_qlsp/home_api/get_home_data
    function get_home_data() {
        header('Access-Control-Allow-Origin: *'); 
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $data = [
            'banners' => [],
            'categories' => [],
            'new_products' => []
        ];

        // 1. Lấy danh sách Banners đang hoạt động
        $banners = $this->home_model->banners_getActive();
        if ($banners) {
            while ($row = mysqli_fetch_assoc($banners)) {
                $data['banners'][] = $row;
            }
        }

        // 2. Lấy danh sách tất cả Danh mục
        $categories = $this->home_model->categories_selectAll();
        if ($categories) {
            while ($row = mysqli_fetch_assoc($categories)) {
                $data['categories'][] = $row;
            }
        }

        // 3. Lấy Top 10 Sản phẩm mới nhất
        $new_products = $this->prd_list_model->products_selectNew('default');
        if ($new_products) {
            $count = 0;
            while ($row = mysqli_fetch_assoc($new_products)) {
                if ($count >= 10) break;
                $data['new_products'][] = $row;
                $count++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Lấy dữ liệu trang chủ thành công',
            'data' => $data
        ]);
        exit;
    }

    // ============================================================
    // 3. TIỆN ÍCH ĐỊA CHỈ (AJAX)
    // ============================================================

    // Lấy Quận/Huyện dựa trên mã Tỉnh
    function get_districts($p_code) {
        $districts = $this->provinces_model->districts_selectByProvince($p_code);
        echo '<option value="">Chọn Quận/Huyện</option>';
        while ($row = mysqli_fetch_assoc($districts)) {
            echo "<option value='".$row['code']."'>".$row['name']."</option>";
        }
        exit;
    }

    // Lấy Phường/Xã dựa trên mã Quận
    function get_wards($d_code) {
        $wards = $this->provinces_model->wards_selectByDistrict($d_code);
        echo '<option value="">Chọn Phường/Xã</option>';
        while ($row = mysqli_fetch_assoc($wards)) {
            echo "<option value='".$row['code']."'>".$row['name']."</option>";
        }
        exit;
    }
}