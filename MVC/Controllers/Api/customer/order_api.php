<?php
class order_api extends controllers_customer {
    private $cart_model;

    function __construct() {
        // Gọi model dành riêng cho việc đặt hàng/giỏ hàng của khách
        $this->cart_model = $this->model('cart_m');
    }

    // Hàm hỗ trợ đọc dữ liệu JSON gửi lên từ JS/Frontend
    private function getRequestData() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    // API POST: /web_qlsp/api/Customer/order_api/checkout
    function checkout() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: POST');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận phương thức POST']);
            return;
        }

        $data = $this->getRequestData();

        $user_id = $data['user_id'] ?? null;
        $customer_name = $data['customer_name'] ?? '';
        $customer_phone = $data['customer_phone'] ?? '';
        $customer_email = $data['customer_email'] ?? '';
        $province_code = $data['province_code'] ?? '';
        $district_code = $data['district_code'] ?? '';
        $ward_code = $data['ward_code'] ?? '';
        $address_detail = $data['address_detail'] ?? '';
        $payment_method = $data['payment_method'] ?? 'cod';
        $note = $data['note'] ?? '';
        
        $cart_items = $data['cart_items'] ?? []; 

        if (empty($customer_name) || empty($customer_phone) || empty($cart_items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin người nhận hoặc giỏ hàng trống!']);
            return;
        }

        // Tính toán tổng tiền
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        $shipping_fee = 30000; 
        $discount_amount = 0;
        $voucher_id = $data['voucher_id'] ?? null;
        $voucher_code = null;

        // Xử lý Voucher
        if ($voucher_id) {
            $voucher = $this->cart_model->voucher_getById($voucher_id);
            if ($voucher && $voucher['status'] == 1 && $subtotal >= $voucher['min_order_value']) {
                $voucher_code = $voucher['code'];
                if ($voucher['discount_type'] == 'fixed') {
                    $discount_amount = $voucher['discount_value'];
                } else {
                    $discount_amount = round($subtotal * $voucher['discount_value'] / 100);
                    if ($voucher['max_discount_amount'] > 0) {
                        $discount_amount = min($discount_amount, $voucher['max_discount_amount']);
                    }
                }
            }
        }

        $total_money = $subtotal + $shipping_fee - $discount_amount;
        $points_earned = floor($subtotal / 100000); 

        // Chuẩn bị dữ liệu lưu vào DB
        $order_data = [
            'user_id' => $user_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'shipping_province' => $province_code,
            'shipping_district' => $district_code,
            'shipping_ward' => $ward_code,
            'shipping_address_detail' => $address_detail,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'voucher_code' => $voucher_code,
            'discount_amount' => $discount_amount,
            'points_used' => 0,
            'points_discount' => 0,
            'total_money' => $total_money,
            'points_earned' => $points_earned,
            'payment_method' => $payment_method,
            'status' => 'pending',
            'note' => $note,
        ];

        // Tạo đơn hàng
        $order_id = $this->cart_model->order_create($order_data);

        if ($order_id) {
            foreach ($cart_items as $item) {
                $order_item_data = [
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                ];
                $this->cart_model->orderItem_insert($order_item_data);
                
                // Trừ tồn kho
                if (!empty($item['variant_id'])) {
                    $this->cart_model->variant_updateStock($item['variant_id'], $item['quantity']);
                } else {
                    $this->cart_model->product_updateStock($item['id'], $item['quantity']);
                }
            }

            // Cập nhật lượt dùng voucher
            if ($voucher_id) {
                $this->cart_model->voucher_updateUsedCount($voucher_id);
            }

            http_response_code(201); // 201 Created
            echo json_encode([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'data' => [
                    'order_id' => $order_id,
                    'total_money' => $total_money
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi server, không thể tạo đơn hàng']);
        }
    }
}
?>