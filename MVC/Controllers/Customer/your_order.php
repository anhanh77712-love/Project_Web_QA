<?php
class your_order extends controllers_customer {
    public $your_order_m;
    private $menu_categories;
    
    function __construct() {
        $this->your_order_m = $this->model('your_order_m');
        $this->menu_categories = $this->model('master_customer_m');
    }
    
    // Hiển thị danh sách đơn hàng
    function Get_data() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }
        $user_info = null;
        if (isset($_SESSION['user_id'])) {
            $user_info = $this->model('profile_m')->user_getById($_SESSION['user_id']);
        }
        
        $user_id = $_SESSION['user_id'];
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        
        // Lấy danh sách đơn hàng
        $orders = $this->your_order_m->orders_getByUserId($user_id, $status);
        
        // Đếm số đơn hàng theo trạng thái
        $counts = $this->your_order_m->orders_countByStatus($user_id);
        
        // Debug: Kiểm tra số lượng orders
        $orders_count = $orders ? mysqli_num_rows($orders) : 0;
        
        // Tạo mảng orders với item details
        $orders_with_items = [];
        if ($orders && mysqli_num_rows($orders) > 0) {
            while($order = mysqli_fetch_assoc($orders)) {
                $order_items = $this->your_order_m->orderItems_getByOrderId($order['id']);
                $order['items'] = [];
                $order['total_items'] = 0;
                
                if ($order_items && mysqli_num_rows($order_items) > 0) {
                    while($item = mysqli_fetch_assoc($order_items)) {
                        $order['items'][] = $item;
                    }
                    $order['total_items'] = count($order['items']);
                }
                
                $orders_with_items[] = $order;
            }
        }
        
        $this->view('Master_customer', [
            'Page' => 'your_order_v',
            'title' => 'Đơn hàng của bạn',
            'orders_data' => $orders_with_items,
            'counts' => $counts,
            'current_status' => $status,
            'your_order_m' => $this->your_order_m,
            'debug_orders_count' => $orders_count,
            'menu_categories' => $this->menu_categories->categories_selectAll(),
            'user_info' => $user_info
        ]);
    }
    
    // Xem chi tiết đơn hàng
    function detail() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /web_qlsp/home');
            exit;
        }
        
        if (!isset($_GET['id'])) {
            header('Location: /web_qlsp/your_order');
            exit;
        }
        
        $order_id = (int)$_GET['id'];
        $user_id = $_SESSION['user_id'];
        
        // Lấy chi tiết đơn hàng
        $order = $this->your_order_m->order_getByIdAndUserId($order_id, $user_id);
        
        if (!$order) {
            header('Location: /web_qlsp/your_order');
            exit;
        }
        
        // Lấy danh sách sản phẩm
        $order_items = $this->your_order_m->orderItems_getByOrderId($order_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => mysqli_fetch_all($order_items, MYSQLI_ASSOC)
        ]);
    }
    
    // Hủy đơn hàng
    function cancel() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }
        
        if (!isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
            exit;
        }
        
        $order_id = (int)$_POST['order_id'];
        $user_id = $_SESSION['user_id'];
        
        $result = $this->your_order_m->order_cancel($order_id, $user_id);
        
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng này']);
        }
    }
    function confirm() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }
        
        if (!isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
            exit;
        }
        
        $order_id = (int)$_POST['order_id'];
        $user_id = $_SESSION['user_id'];
        
        // Kiểm tra trạng thái đơn hàng hiện tại
        $order = $this->your_order_m->order_getByIdAndUserId($order_id, $user_id);
        
        header('Content-Type: application/json');
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }
        
        if ($order['status'] !== 'shipping') {
            echo json_encode(['success' => false, 'message' => 'Chỉ có thể xác nhận đơn hàng đang giao']);
            exit;
        }
        
        $result = $this->your_order_m->order_confirm($order_id, $user_id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã xác nhận nhận hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xác nhận đơn hàng này']);
        }
    }
}
?>
