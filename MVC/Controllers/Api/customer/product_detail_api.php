<?php
class product_detail_api extends controllers_customer {
    private $product_model;
    private $menu_categories;
    private $profile_model;

    function __construct() {
        // Khởi tạo các Model cần thiết cho hiển thị và dữ liệu
        $this->product_model = $this->model("product_detail_m");
        $this->menu_categories = $this->model('master_customer_m');
        $this->profile_model = $this->model('profile_m');
    }

    // ============================================================
    // 1. GIAO DIỆN CHI TIẾT SẢN PHẨM (WEB VIEW)
    // ============================================================

    function Get_data() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Lấy slug từ URL
        $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
        
        if(empty($slug)) {
            header('Location: /web_qlsp/product_list_customer');
            exit;
        }

        // Lấy thông tin người dùng nếu đã đăng nhập
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->profile_model->user_getById($_SESSION['user_id']);
        }

        // Truy vấn thông tin sản phẩm chính
        $product = $this->product_model->product_selectBySlug($slug);
        
        if(!$product || mysqli_num_rows($product) == 0) {
            $_SESSION['error'] = 'Sản phẩm không tồn tại hoặc đã bị ẩn.';
            header('Location: /web_qlsp/product_list_customer');
            exit;
        }
        
        $product_data = mysqli_fetch_assoc($product);
        
        // Xử lý các dữ liệu liên quan: Sản phẩm tương tự & Tăng lượt xem
        $related_products = $this->product_model->products_selectRelated($product_data['category_id'], $product_data['id']);
        $this->product_model->product_increaseViews($product_data['id']);
        
        // Trả về View chi tiết cho khách hàng
        $this->view('Master_customer', [
            'Page' => 'product_details_v',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'product' => $product_data,
            'related_products' => $related_products,
            'detail_model' => $this->product_model,
            'user_info' => $user_info
        ]);
    }

    // ============================================================
    // 2. CÁC PHƯƠNG THỨC API (JSON)
    // ============================================================

    // API lấy thông tin thô của sản phẩm và các biến thể đi kèm
    // URL: /web_qlsp/product_detail_api/get_info?slug=ten-san-pham
    function get_info() {
        header('Content-Type: application/json; charset=utf-8');
        $slug = $_GET['slug'] ?? '';
        
        $product_query = $this->product_model->product_selectBySlug($slug);
        if ($product_query && mysqli_num_rows($product_query) > 0) {
            $product = mysqli_fetch_assoc($product_query);
            
            // Lấy danh sách biến thể (Size/Color) cho API
            $variants = [];
            if (method_exists($this->product_model, 'get_variants_by_product')) {
                $v_res = $this->product_model->get_variants_by_product($product['id']);
                if ($v_res) {
                    while ($v = mysqli_fetch_assoc($v_res)) {
                        $variants[] = $v;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $product,
                'variants' => $variants
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        }
        exit;
    }

    // API kiểm tra nhanh số lượng tồn kho của một biến thể
    // URL: /web_qlsp/product_detail_api/check_stock
    function check_stock() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
             exit;
        }

        $variant_id = intval($_POST['variant_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        // Gọi hàm kiểm tra tồn kho từ Model
        $is_available = $this->product_model->variant_checkStock($variant_id, $quantity);

        echo json_encode([
            'success' => true,
            'variant_id' => $variant_id,
            'is_available' => $is_available
        ]);
        exit;
    }
}
?>