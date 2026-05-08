<?php
class orders extends controllers {
    private $orders;
    function __construct() {
                parent::__construct();

        $this->orders = $this->model('orders_m');
    }
    
    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'orders_v'
        ]);
    }
    
    // 2. LUỒNG DÀNH CHO JAVASCRIPT (Lấy danh sách đơn hàng + Lọc)
    function api_get_data() {
        $this->setApiHeader();
        
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        
        // Đảo ngày nếu người dùng chọn ngày bắt đầu lớn hơn ngày kết thúc
        if (!empty($from) && !empty($to)) {
            $isFromFmt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from);
            $isToFmt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to);
            if ($isFromFmt && $isToFmt) {
                if (strtotime($from) > strtotime($to)) {
                    $tmp = $from; $from = $to; $to = $tmp;
                }
            }
        }
        
        $result = $this->orders->orders_selectFiltered($status, $from, $to);
        $data = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // API: Xem chi tiết đơn hàng
    function view_detail() {
        $this->setApiHeader();
        
        if (isset($_GET['id'])) {
            $order_id = $_GET['id'];
            $order = $this->orders->order_getById($order_id);
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']); return;
            }
            
            $order_items = $this->orders->orderItems_getByOrderId($order_id);
            $items_array = [];
            
            if ($order_items && mysqli_num_rows($order_items) > 0) {
                while($item = mysqli_fetch_assoc($order_items)) {
                    $items_array[] = [
                        'id' => $item['product_id'],
                        'name' => $item['product_name'],
                        'price' => $item['price'],
                        'image' => $item['product_image'],
                        'size' => $item['size'] ?? null,
                        'color' => $item['color'] ?? null,
                        'variant_id' => $item['variant_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['total']
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'order' => $order, 'items' => $items_array]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        }
        exit;
    }
    
    // API: Cập nhật trạng thái đơn hàng
    function update_status() {
        $this->setApiHeader();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            $new_status = $_POST['status'] ?? null;
            
            if ($order_id && $new_status) {
                $order = $this->orders->order_getById($order_id);
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']); return;
                }

                $old_status = $order['status'] ?? 'pending';
                $subtotal = isset($order['subtotal']) ? (int)$order['subtotal'] : 0;
                $user_id = $order['user_id'] ?? null;
                $message = 'Cập nhật trạng thái thành công';

                if ($new_status === 'completed' && $old_status !== 'completed') {
                    $earned = max(0, intval($subtotal / 1000));
                    if (!empty($user_id)) {
                        $this->orders->user_addPoints($user_id, $earned);
                    }
                    $result = $this->orders->order_updateStatusWithPoints($order_id, $new_status, $earned);
                    echo json_encode(['success' => $result, 'message' => $result ? $message : 'Cập nhật thất bại']);
                    return;
                }

                $result = $this->orders->order_updateStatus($order_id, $new_status);
                echo json_encode(['success' => $result, 'message' => $result ? $message : 'Cập nhật thất bại']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            }
        }
        exit;
    }
    
    // API: Xóa đơn hàng
    function delete_order() {
        $this->setApiHeader();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            if ($order_id) {
                $this->orders->orderItems_deleteByOrderId($order_id);
                $delete_order = $this->orders->order_delete($order_id);
                
                echo json_encode(['success' => $delete_order, 'message' => $delete_order ? 'Xóa đơn hàng thành công' : 'Xóa đơn hàng thất bại']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            }
        }
        exit;
    }
}
?>