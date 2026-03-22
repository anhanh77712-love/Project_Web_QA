<?php
class user_api extends controllers_customer {
    private $profile_model;
    private $order_model;

    function __construct() {
        $this->profile_model = $this->model('profile_m');
        $this->order_model = $this->model('your_order_m');
    }

    // API GET: /web_qlsp/api/user_api/get_profile?user_id=1
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
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin']);
        }
    }

    // API GET: /web_qlsp/api/user_api/get_orders?user_id=1
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
}
?>