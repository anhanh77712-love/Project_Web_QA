<?php
class cart_m extends connectDB {
    function __construct() {
        parent::__construct();
    }


    function vouchers_selectActive() {
    // Thêm điều kiện: AND end_date >= CURDATE() để so sánh với ngày hiện tại
    $sql = "SELECT * FROM vouchers 
        WHERE status = 1 
        AND end_date >= NOW() 
        AND start_date <= NOW() 
        ORDER BY id DESC";
    return mysqli_query($this->con, $sql);
}

    // Lấy voucher theo ID
    function voucher_getById($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT * FROM vouchers WHERE id = '$id' AND status = 1";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // Lấy voucher theo code
    function voucher_getByCode($code) {
        $code = mysqli_real_escape_string($this->con, $code);
        $sql = "SELECT * FROM vouchers WHERE code = '$code' AND status = 1";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // Cập nhật số lần sử dụng voucher
    function voucher_updateUsedCount($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "UPDATE vouchers SET used_count = used_count + 1 WHERE id = '$id'";
        return mysqli_query($this->con, $sql);
    }

    // ===== ORDERS =====
    
    // Tạo đơn hàng mới
    function order_create($data) {
        $user_id = isset($data['user_id']) && !empty($data['user_id']) ? mysqli_real_escape_string($this->con, $data['user_id']) : 'NULL';
        $customer_name = mysqli_real_escape_string($this->con, $data['customer_name']);
        $customer_phone = mysqli_real_escape_string($this->con, $data['customer_phone']);
        $customer_email = mysqli_real_escape_string($this->con, $data['customer_email']);
        $shipping_province = mysqli_real_escape_string($this->con, $data['shipping_province']);
        $shipping_district = mysqli_real_escape_string($this->con, $data['shipping_district']);
        $shipping_ward = mysqli_real_escape_string($this->con, $data['shipping_ward']);
        $shipping_address_detail = mysqli_real_escape_string($this->con, $data['shipping_address_detail']);
        $subtotal = mysqli_real_escape_string($this->con, $data['subtotal']);
        $shipping_fee = mysqli_real_escape_string($this->con, $data['shipping_fee']);
        $voucher_code = isset($data['voucher_code']) && !empty($data['voucher_code']) ? "'" . mysqli_real_escape_string($this->con, $data['voucher_code']) . "'" : 'NULL';
        $discount_amount = mysqli_real_escape_string($this->con, $data['discount_amount']);
        $points_used = isset($data['points_used']) ? mysqli_real_escape_string($this->con, $data['points_used']) : 0;
        $points_discount = isset($data['points_discount']) ? mysqli_real_escape_string($this->con, $data['points_discount']) : 0;
        $total_money = mysqli_real_escape_string($this->con, $data['total_money']);
        $points_earned = isset($data['points_earned']) ? mysqli_real_escape_string($this->con, $data['points_earned']) : 0;
        $payment_method = mysqli_real_escape_string($this->con, $data['payment_method']);
        $status = mysqli_real_escape_string($this->con, $data['status']);
        $note = isset($data['note']) && !empty($data['note']) ? "'" . mysqli_real_escape_string($this->con, $data['note']) . "'" : 'NULL';
        
        $sql = "INSERT INTO orders (
                    user_id, customer_name, customer_phone, customer_email, 
                    shipping_province, shipping_district, shipping_ward, shipping_address_detail,
                    subtotal, shipping_fee, voucher_code, discount_amount,
                    points_used, points_discount, total_money, points_earned,
                    payment_method, status, note, created_at
                ) VALUES (
                    $user_id, '$customer_name', '$customer_phone', '$customer_email',
                    '$shipping_province', '$shipping_district', '$shipping_ward', '$shipping_address_detail',
                    $subtotal, $shipping_fee, $voucher_code, $discount_amount,
                    $points_used, $points_discount, $total_money, $points_earned,
                    '$payment_method', '$status', $note, NOW()
                )";
        
        if(mysqli_query($this->con, $sql)) {
            return mysqli_insert_id($this->con);
        }
        return false;
    }
    
    // Lấy đơn hàng theo ID
    function order_getById($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT * FROM orders WHERE id = '$id'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // Lấy đơn hàng theo user ID
    function orders_getByUserId($user_id) {
        $user_id = mysqli_real_escape_string($this->con, $user_id);
        $sql = "SELECT o.*, 
                       p.name as province_name,
                       d.name as district_name,
                       w.name as ward_name
                FROM orders o
                LEFT JOIN provinces p ON o.shipping_province = p.code
                LEFT JOIN districts d ON o.shipping_district = d.code
                LEFT JOIN wards w ON o.shipping_ward = w.code
                WHERE o.user_id = '$user_id' 
                ORDER BY o.created_at DESC";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy danh sách sản phẩm từ product_id (JSON)
    function order_getProducts($order) {
        if (!empty($order['product_id'])) {
            return json_decode($order['product_id'], true);
        }
        return [];
    }
    
    // Cập nhật trạng thái đơn hàng
    function order_updateStatus($id, $status) {
        $id = mysqli_real_escape_string($this->con, $id);
        $status = mysqli_real_escape_string($this->con, $status);
        $sql = "UPDATE orders SET status = '$status' WHERE id = '$id'";
        return mysqli_query($this->con, $sql);
    }

    // ===== ORDER ITEMS =====
    
    private function orderItems_hasColumn($column) {
        $column = mysqli_real_escape_string($this->con, $column);
        $sql = "SHOW COLUMNS FROM order_items LIKE '$column'";
        $res = mysqli_query($this->con, $sql);
        return $res && mysqli_num_rows($res) > 0;
    }

    // Thêm chi tiết đơn hàng
    function orderItem_insert($data) {
        $order_id = mysqli_real_escape_string($this->con, $data['order_id']);
        $product_id = mysqli_real_escape_string($this->con, $data['product_id']);
        $quantity = mysqli_real_escape_string($this->con, $data['quantity']);
        $price = mysqli_real_escape_string($this->con, $data['price']);
        $variant_id = isset($data['variant_id']) ? mysqli_real_escape_string($this->con, $data['variant_id']) : null;
        $size = isset($data['size']) ? mysqli_real_escape_string($this->con, $data['size']) : null;
        $color = isset($data['color']) ? mysqli_real_escape_string($this->con, $data['color']) : null;
        
        // Xây dựng câu lệnh theo cột có sẵn trong DB
        $columns = ['order_id','product_id','quantity','price'];
        $values = ["'$order_id'","'$product_id'",$quantity,$price];

        if ($variant_id !== null && $this->orderItems_hasColumn('variant_id')) {
            $columns[] = 'variant_id';
            $values[] = "'$variant_id'";
        }
        if ($size !== null && $this->orderItems_hasColumn('size')) {
            $columns[] = 'size';
            $values[] = "'$size'";
        }
        if ($color !== null && $this->orderItems_hasColumn('color')) {
            $columns[] = 'color';
            $values[] = "'$color'";
        }

        $sql = 'INSERT INTO order_items (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
        
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy chi tiết đơn hàng
    function orderItems_getByOrderId($order_id) {
        $order_id = mysqli_real_escape_string($this->con, $order_id);
        $sql = "SELECT * FROM order_items WHERE order_id = '$order_id'";
        return mysqli_query($this->con, $sql);
    }

    // ===== PRODUCTS =====
    
    // Lấy thông tin sản phẩm theo ID (để hiển thị trong giỏ hàng)
    function product_getById($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT * FROM products WHERE id = '$id'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // Kiểm tra tồn kho sản phẩm
    function product_checkStock($id, $quantity) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT stock_quantity FROM products WHERE id = '$id'";
        $result = mysqli_query($this->con, $sql);
        $product = mysqli_fetch_assoc($result);
        
        if ($product && $product['stock_quantity'] >= $quantity) {
            return true;
        }
        return false;
    }
    
    // Cập nhật số lượng tồn kho sau khi đặt hàng
    function product_updateStock($id, $quantity) {
        $id = mysqli_real_escape_string($this->con, $id);
        $quantity = mysqli_real_escape_string($this->con, $quantity);
        $sql = "UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE id = '$id'";
        return mysqli_query($this->con, $sql);
    }

    // ===== CART SESSION HELPERS =====
    
    // Lấy tổng số sản phẩm trong giỏ
    function getCartCount() {
        $count = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $count += $item['quantity'];
            }
        }
        return $count;
    }
    
    // Tính tổng tiền giỏ hàng
    function calculateCartTotal() {
        $total = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        return $total;
    }
    
    // Xóa toàn bộ giỏ hàng
    function clearCart() {
        unset($_SESSION['cart']);
    }

    // ===== USERS - Liên quan đến giỏ hàng =====
    
    // Lấy thông tin user theo ID để điền vào form checkout
    function user_getById($id) {
        $id = mysqli_real_escape_string($this->con, $id);
        $sql = "SELECT * FROM users WHERE id = '$id'";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // Cập nhật thông tin địa chỉ user sau khi đặt hàng
    function user_updateAddress($user_id, $province_code, $district_code, $ward_code, $address_detail) {
        $user_id = mysqli_real_escape_string($this->con, $user_id);
        $province_code = mysqli_real_escape_string($this->con, $province_code);
        $district_code = mysqli_real_escape_string($this->con, $district_code);
        $ward_code = mysqli_real_escape_string($this->con, $ward_code);
        $address_detail = mysqli_real_escape_string($this->con, $address_detail);
        
        $sql = "UPDATE users SET 
                province_code = '$province_code',
                district_code = '$district_code', 
                ward_code = '$ward_code',
                address_detail = '$address_detail'
                WHERE id = '$user_id'";
        
        return mysqli_query($this->con, $sql);
    }

    // Trừ điểm người dùng khi sử dụng điểm đổi giảm giá
    function user_decreasePoints($user_id, $points) {
        $user_id = mysqli_real_escape_string($this->con, $user_id);
        $points = max(0, intval($points));
        $sql = "UPDATE users SET points = GREATEST(COALESCE(points,0) - $points, 0) WHERE id = '$user_id'";
        return mysqli_query($this->con, $sql);
    }
}
?>
