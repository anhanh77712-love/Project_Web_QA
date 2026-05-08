<?php
class your_order extends controllers_customer {
    public $your_order_m;
    private $menu_categories;
    
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
        $this->your_order_m = $this->model('your_order_m');
        $this->menu_categories = $this->model('master_customer_m');
    }
    
    // 1. CHỈ TẢI GIAO DIỆN TRỐNG (Cực nhanh)
    function Get_data() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }
        
        $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        
        $this->view('Master_customer', [
            'Page' => 'your_order_v',
            'title' => 'Đơn hàng của bạn',
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'user_info' => $user_info
        ]);
    }
    
    // 2. API LẤY DANH SÁCH ĐƠN HÀNG (AJAX)
    function api_get_orders() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null;
        
        // Lấy danh sách đơn hàng
        $orders_rs = $this->your_order_m->orders_getByUserId($user_id, $status);
        $counts = $this->your_order_m->orders_countByStatus($user_id);
        
        // Đóng gói đơn hàng + sản phẩm (Chống N+1 bằng Array)
        $orders_with_items = [];
        if ($orders_rs && mysqli_num_rows($orders_rs) > 0) {
            while($order = mysqli_fetch_assoc($orders_rs)) {
                $order_items = $this->your_order_m->orderItems_getByOrderId($order['id']);
                $items = [];
                if ($order_items && mysqli_num_rows($order_items) > 0) {
                    while($item = mysqli_fetch_assoc($order_items)) {
                        $items[] = $item;
                    }
                }
                $order['items'] = $items;
                $order['total_items'] = count($items);
                $orders_with_items[] = $order;
            }
        }
        
        echo json_encode([
            'success' => true,
            'orders' => $orders_with_items,
            'counts' => $counts,
            'current_status' => $status
        ]);
        exit;
    }
    
    // 3. API Xem chi tiết đơn hàng
    function detail() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']); exit;
        }
        
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID đơn hàng']); exit;
        }
        
        $order_id = (int)$_GET['id'];
        $user_id = $_SESSION['user_id'];
        
        $order = $this->your_order_m->order_getByIdAndUserId($order_id, $user_id);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']); exit;
        }
        
        $order_items = $this->your_order_m->orderItems_getByOrderId($order_id);
        $items = [];
        if ($order_items && mysqli_num_rows($order_items) > 0) {
            while($it = mysqli_fetch_assoc($order_items)) {
                $items[] = $it;
            }
        }
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
        exit;
    }
    
    // 4. API Hủy đơn hàng
    function cancel() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']); exit;
        }
        
        if (!isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']); exit;
        }
        
        $order_id = (int)$_POST['order_id'];
        $user_id = $_SESSION['user_id'];
        
        $result = $this->your_order_m->order_cancel($order_id, $user_id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng này']);
        }
        exit;
    }
    
    // 5. API Xác nhận nhận hàng
    function confirm() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']); exit;
        }
        
        if (!isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']); exit;
        }
        
        $order_id = (int)$_POST['order_id'];
        $user_id = $_SESSION['user_id'];
        
        $order = $this->your_order_m->order_getByIdAndUserId($order_id, $user_id);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']); exit;
        }
        if ($order['status'] !== 'shipping') {
            echo json_encode(['success' => false, 'message' => 'Chỉ có thể xác nhận đơn hàng đang giao']); exit;
        }
        
        $result = $this->your_order_m->order_confirm($order_id, $user_id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã xác nhận nhận hàng. Điểm thưởng đã được cộng vào tài khoản!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xác nhận đơn hàng này']);
        }
        exit;
    }
}
?>