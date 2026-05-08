<?php
class your_order_m extends connectDB {
    
    // Lấy danh sách đơn hàng của user
    function orders_getByUserId($user_id, $status = null) {
        $user_id = mysqli_real_escape_string($this->con, $user_id);

        // NOTE: schema orders uses shipping_province/shipping_district/shipping_ward
        // and UI expects some friendly alias fields.
        $sql = "SELECT 
                    o.*,
                    o.customer_name as full_name,
                    o.customer_phone as phone,
                    o.customer_email as email,
                    o.shipping_address_detail as address,
                    o.note as notes,
                    o.total_money as grand_total,
                    p.name as province_name,
                    d.name as district_name,
                    w.name as ward_name
                FROM orders o
                LEFT JOIN provinces p ON o.shipping_province = p.code
                LEFT JOIN districts d ON o.shipping_district = d.code
                LEFT JOIN wards w ON o.shipping_ward = w.code
                WHERE o.user_id = '$user_id'";
        
        if ($status) {
            $status = mysqli_real_escape_string($this->con, $status);
            $sql .= " AND o.status = '$status'";
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy chi tiết đơn hàng của user
    function order_getByIdAndUserId($order_id, $user_id) {
        $order_id = (int)$order_id;
        $user_id = (int)$user_id;

        $sql = "SELECT 
                    o.*,
                    o.customer_name as full_name,
                    o.customer_phone as phone,
                    o.customer_email as email,
                    o.shipping_address_detail as address,
                    o.note as notes,
                    o.total_money as grand_total,
                    p.name as province_name,
                    d.name as district_name,
                    w.name as ward_name
                FROM orders o
                LEFT JOIN provinces p ON o.shipping_province = p.code
                LEFT JOIN districts d ON o.shipping_district = d.code
                LEFT JOIN wards w ON o.shipping_ward = w.code
                WHERE o.id = $order_id AND o.user_id = $user_id";

        $result = mysqli_query($this->con, $sql);
        if (!$result) {
            return null;
        }
        return mysqli_fetch_assoc($result);
    }
    
    // Lấy chi tiết sản phẩm trong đơn hàng
    function orderItems_getByOrderId($order_id) {
        $order_id = (int)$order_id;
        // Detect optional columns to keep compatible with existing schemas
        $hasVariantId = $this->hasOrderItemsColumn('variant_id');
        $hasSize = $this->hasOrderItemsColumn('size');
        $hasColor = $this->hasOrderItemsColumn('color');

        $select = [
            'oi.id',
            'oi.product_id',
            'oi.quantity',
            'oi.price',
            '(oi.price * oi.quantity) as total',
            'p.name as product_name',
            'p.thumbnail as product_image'
        ];

        $join = '';
        if ($hasVariantId) {
            $select[] = 'oi.variant_id';
            $join .= ' LEFT JOIN product_variants pv ON oi.variant_id = pv.id ';
            $select[] = $hasSize ? 'oi.size as size' : 'pv.size as size';
            $select[] = $hasColor ? 'oi.color as color' : 'pv.color as color';
            // Prefer variant image if exists
            $select[] = "(SELECT image_url FROM product_images WHERE variant_id = oi.variant_id ORDER BY id ASC LIMIT 1) as variant_image";
        } else {
            if ($hasSize) { $select[] = 'oi.size as size'; }
            if ($hasColor) { $select[] = 'oi.color as color'; }
        }

        $sql = 'SELECT ' . implode(',', $select) . ' FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id ' . $join . ' WHERE oi.order_id = ' . $order_id;
        return mysqli_query($this->con, $sql);
    }

    // Helper: check if order_items has a specific column
    private function hasOrderItemsColumn($column) {
        $column = mysqli_real_escape_string($this->con, $column);
        $res = mysqli_query($this->con, "SHOW COLUMNS FROM order_items LIKE '$column'");
        return $res && mysqli_num_rows($res) > 0;
    }
    
    // Hủy đơn hàng (chỉ được hủy khi đơn hàng đang ở trạng thái pending)
    function order_cancel($order_id, $user_id) {
        $order_id = (int)$order_id;
        $user_id = (int)$user_id;
        // Kiểm tra đơn hàng có thuộc về user và đang pending không
        $check = "SELECT id, status FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'pending'";
        $result = mysqli_query($this->con, $check);
        
        if (mysqli_num_rows($result) == 0) {
            return false; // Không thể hủy
        }
        
        // Cập nhật trạng thái
        $sql = "UPDATE orders SET status = 'cancelled' WHERE id = $order_id";
        return mysqli_query($this->con, $sql);
    }
    
    // Đếm số đơn hàng theo trạng thái
    function orders_countByStatus($user_id) {
        $user_id = (int)$user_id;
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM orders
                WHERE user_id = $user_id
                GROUP BY status";
        
        $result = mysqli_query($this->con, $sql);
        $counts = [
            'all' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'shipping' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $counts[$row['status']] = $row['count'];
            $counts['all'] += $row['count'];
        }
        
        return $counts;
    }
    function order_confirm($order_id, $user_id) {
        $order_id = (int)$order_id;
        $user_id = (int)$user_id;
        
        // Kiểm tra đơn hàng có thuộc về user và đang shipping không
        $check = "SELECT id, status, total_money FROM orders WHERE id = $order_id AND user_id = $user_id AND status = 'shipping'";
        $result = mysqli_query($this->con, $check);
        
        if (mysqli_num_rows($result) == 0) {
            return false; // Không thể xác nhận
        }
        
        $order = mysqli_fetch_assoc($result);
        
        // Cập nhật trạng thái
        $sql = "UPDATE orders SET status = 'completed' WHERE id = $order_id";
        if (!mysqli_query($this->con, $sql)) {
            return false;
        }
        
        // Cộng điểm cho user: tổng tiền chia 1000 (1 điểm = 1.000đ)
        $points_earned = floor($order['total_money'] / 1000);
        if ($points_earned > 0) {
            $update_points = "UPDATE users SET points = COALESCE(points, 0) + $points_earned WHERE id = $user_id";
            mysqli_query($this->con, $update_points);
        }
        
        return true;
    }
}
?>
