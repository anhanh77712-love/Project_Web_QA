<?php
class orders extends controllers {
    private $orders;
    function __construct() {
        // Gọi hàm khởi tạo của lớp cha
        $this->orders = $this->model('orders_m');
    }
    
    function Get_data() {
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        // Server-side safeguard: if both dates provided and out of order, swap
        if (!empty($from) && !empty($to)) {
            // Basic YYYY-MM-DD validation
            $isFromFmt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $from);
            $isToFmt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $to);
            if ($isFromFmt && $isToFmt) {
                if (strtotime($from) > strtotime($to)) {
                    $tmp = $from; $from = $to; $to = $tmp;
                }
            }
        }
        
        $this->view('Master', [
            'Page' => 'orders_v',
            'orders_list' => $this->orders->orders_selectFiltered($status, $from, $to),
            'filter_from' => $from,
            'filter_to' => $to
        ]);
    }
    
    // Xem chi tiết đơn hàng
    function view_detail() {
        if (isset($_GET['id'])) {
            $order_id = $_GET['id'];
            
            // Lấy thông tin đơn hàng
            $order = $this->orders->order_getById($order_id);
            
            if (!$order) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
                return;
            }
            
            // Lấy danh sách sản phẩm từ bảng order_items
            $order_items = $this->orders->orderItems_getByOrderId($order_id);
            $items_array = [];
            
            if ($order_items && mysqli_num_rows($order_items) > 0) {
                while($item = mysqli_fetch_assoc($order_items)) {
                    $items_array[] = [
                        'id' => $item['product_id'],
                        'name' => $item['product_name'],
                        'price' => $item['price'],
                        'image' => $item['product_image'],
                        'size' => isset($item['size']) ? $item['size'] : null,
                        'color' => isset($item['color']) ? $item['color'] : null,
                        'variant_id' => isset($item['variant_id']) ? $item['variant_id'] : null,
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['total']
                    ];
                }
            }
            
            // Trả về JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items_array
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        }
    }
    
    // Cập nhật trạng thái đơn hàng
    function update_status() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            $new_status = $_POST['status'] ?? null;
            
            if ($order_id && $new_status) {
                // Lấy đơn hàng hiện tại để kiểm tra status trước và subtotal
                $order = $this->orders->order_getById($order_id);
                if (!$order) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
                    return;
                }

                $old_status = $order['status'] ?? 'pending';
                $subtotal = isset($order['subtotal']) ? (int)$order['subtotal'] : 0;
                $user_id = $order['user_id'] ?? null;
                $message = 'Cập nhật trạng thái thành công';

                // Nếu chuyển sang hoàn thành và chưa hoàn thành trước đó thì cộng điểm
                if ($new_status === 'completed' && $old_status !== 'completed') {
                    $earned = max(0, intval($subtotal / 1000));
                    $pointsResult = true;
                    if (!empty($user_id)) {
                        $pointsResult = $this->orders->user_addPoints($user_id, $earned);
                    }
                    // Lưu điểm đã cộng vào đơn hàng để tham chiếu
                    $result = $this->orders->order_updateStatusWithPoints($order_id, $new_status, $earned);
                    header('Content-Type: application/json');
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => $message]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                    }
                    return;
                }

                // Trường hợp các trạng thái khác: chỉ cập nhật
                $result = $this->orders->order_updateStatus($order_id, $new_status);
                header('Content-Type: application/json');
                if ($result) {
                    echo json_encode(['success' => true, 'message' => $message]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            }
        }
    }
    
    // Xóa đơn hàng
    function delete_order() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            
            if ($order_id) {
                // Xóa các items của đơn hàng trước
                $delete_items = $this->orders->orderItems_deleteByOrderId($order_id);
                
                // Sau đó xóa đơn hàng
                $delete_order = $this->orders->order_delete($order_id);
                
                header('Content-Type: application/json');
                if ($delete_order) {
                    echo json_encode(['success' => true, 'message' => 'Xóa đơn hàng thành công']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Xóa đơn hàng thất bại']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            }
        }
    }
}
