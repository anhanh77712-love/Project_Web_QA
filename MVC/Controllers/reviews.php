<?php
class reviews extends controllers {
    private $review;
    
    function __construct() {
        // Khởi tạo model đánh giá (đảm bảo file model đã lưu tên là reviews_m.php)
        $this->review = $this->model('reviews_m');
    }
    
    // =========================================================
    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Dành cho trang Admin)
    // =========================================================
    function Get_data() {
        // Tải giao diện quản lý đánh giá cho Admin
        $this->view('Master', [
            'Page' => 'reviews_v' // Bạn có thể tạo file view này sau cho phần Admin
        ]);
    }

    // Thiết lập Header cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // =========================================================
    // 2. LUỒNG API DÀNH CHO JAVASCRIPT (AJAX Fetch)
    // =========================================================

    // API: Lấy danh sách đánh giá của 1 sản phẩm (Dành cho trang chi tiết sản phẩm)
    function api_get_by_product() {
        $this->setApiHeader();
        
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        $reviews_result = $this->review->reviews_selectByProduct($product_id);
        
        $reviews = [];
        if ($reviews_result && mysqli_num_rows($reviews_result) > 0) {
            while ($row = mysqli_fetch_assoc($reviews_result)) {
                $reviews[] = $row;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $reviews
        ]);
        exit;
    }

    // API: Lấy tất cả đánh giá (Dành cho bảng quản trị Admin)
    function api_get_all() {
        $this->setApiHeader();

        $reviews_result = $this->review->reviews_selectAllAdmin();
        $reviews = [];
        if ($reviews_result && mysqli_num_rows($reviews_result) > 0) {
            while ($row = mysqli_fetch_assoc($reviews_result)) {
                $reviews[] = $row;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $reviews
        ]);
        exit;
    }
    
    // API: Khách hàng thêm đánh giá mới
    function add() {
        $this->setApiHeader();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']); 
            exit;
        }

        // Đảm bảo session đã bật để lấy user_id
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá!']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung đánh giá!']);
            exit;
        }

        // Kiểm tra điều kiện mua hàng từ Model
        $order_id = $this->review->reviews_checkEligible($user_id, $product_id);

        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Bạn chỉ được đánh giá 1 lần cho sản phẩm đã mua và nhận hàng thành công!']);
            exit;
        }

        // Tiến hành thêm dữ liệu
        $kq = $this->review->reviews_insert($user_id, $product_id, $order_id, $rating, $comment);
        
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Cảm ơn bạn đã đánh giá sản phẩm!' : 'Lỗi hệ thống, vui lòng thử lại']);
        exit;
    }
    
    // API: Đổi trạng thái hiển thị / ẩn (Dành cho Admin)
    function toggle($id) {
        $this->setApiHeader();
        
        $kq = $this->review->reviews_toggleStatus($id);
        
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã thay đổi trạng thái' : 'Lỗi thay đổi trạng thái']);
        exit;
    }
    
    // API: Xóa đánh giá (Dành cho Admin)
    function delete($id) {
        $this->setApiHeader();
        
        $kq = $this->review->reviews_delete($id);
        
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã xóa đánh giá thành công' : 'Lỗi khi xóa']);
        exit;
    }
}
?>