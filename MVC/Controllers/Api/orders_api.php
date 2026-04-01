<?php
class orders_api extends controllers {
    private $orders;

    function __construct() {
        // Gọi model dành riêng cho Admin
        $this->orders = $this->model('orders_m');
    }
    
    // 1. Render giao diện danh sách đơn hàng
    function Get_data() {
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        
        // Kiểm tra và đảo lại ngày nếu from > to
        if (!empty($from) && !empty($to)) {
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
    
    // 2. API Xem chi tiết đơn hàng
    function view_detail() {
                header('Content-Type: application/json'); // Bắt buộc phải có để tránh lỗi Syntax JSON
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
    
    // 3. API Cập nhật trạng thái đơn hàng
    function update_status() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: POST');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            $new_status = $_POST['status'] ?? null;
            
            if ($order_id && $new_status) {
                $order = $this->orders->order_getById($order_id);
                if (!$order) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
                    return;
                }

                $old_status = $order['status'] ?? 'pending';
                $subtotal = isset($order['subtotal']) ? (int)$order['subtotal'] : 0;
                $user_id = $order['user_id'] ?? null;
                $message = 'Cập nhật trạng thái thành công';

                // Cộng điểm nếu đơn hàng hoàn thành
                if ($new_status === 'completed' && $old_status !== 'completed') {
                    $earned = max(0, intval($subtotal / 1000));
                    if (!empty($user_id)) {
                        $this->orders->user_addPoints($user_id, $earned);
                    }
                    $result = $this->orders->order_updateStatusWithPoints($order_id, $new_status, $earned);
                    
                    if ($result) {
                        http_response_code(200);
                        echo json_encode(['success' => true, 'message' => $message]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                    }
                    return;
                }

                $result = $this->orders->order_updateStatus($order_id, $new_status);
                
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => $message]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cập nhật']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        }
    }
    
    // 4. API Xóa đơn hàng
    function delete_order() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: POST');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_id = $_POST['order_id'] ?? null;
            
            if ($order_id) {
                $this->orders->orderItems_deleteByOrderId($order_id);
                $delete_order = $this->orders->order_delete($order_id);
                
                if ($delete_order) {
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Xóa đơn hàng thành công']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Xóa đơn hàng thất bại']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        }
    }
}
?>