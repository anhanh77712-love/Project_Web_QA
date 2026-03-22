<?php
class orders_m extends connectDB {
    
    // Lấy danh sách tất cả đơn hàng
    function orders_selectAll($status = null) {
        $sql = "SELECT 
                    id,
                    user_id,
                    customer_name,
                    customer_phone,
                    customer_email,
                    shipping_province,
                    shipping_district,
                    shipping_ward,
                    shipping_address_detail,
                    subtotal,
                    shipping_fee,
                    voucher_code,
                    discount_amount,
                    points_used,
                    points_discount,
                    total_money,
                    points_earned,
                    payment_method,
                    status,
                    note,
                    created_at
                FROM orders";
        
        if ($status && $status != 'all') {
            $status = mysqli_real_escape_string($this->con, $status);
            $sql .= " WHERE status = '$status'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        return mysqli_query($this->con, $sql);
    }

    // Lấy đơn hàng theo bộ lọc trạng thái và khoảng thời gian
    function orders_selectFiltered($status = 'all', $from = '', $to = '') {
        $conditions = [];
        $sql = "SELECT 
                    id,
                    user_id,
                    customer_name,
                    customer_phone,
                    customer_email,
                    shipping_province,
                    shipping_district,
                    shipping_ward,
                    shipping_address_detail,
                    subtotal,
                    shipping_fee,
                    voucher_code,
                    discount_amount,
                    points_used,
                    points_discount,
                    total_money,
                    points_earned,
                    payment_method,
                    status,
                    note,
                    created_at
                FROM orders";

        if ($status && $status != 'all') {
            $status = mysqli_real_escape_string($this->con, $status);
            $conditions[] = "status = '$status'";
        }

        // Normalize date inputs (YYYY-MM-DD) and build timestamps
        if (!empty($from)) {
            $from = mysqli_real_escape_string($this->con, $from);
            $from_dt = $from . ' 00:00:00';
            $conditions[] = "created_at >= '$from_dt'";
        }
        if (!empty($to)) {
            $to = mysqli_real_escape_string($this->con, $to);
            $to_dt = $to . ' 23:59:59';
            $conditions[] = "created_at <= '$to_dt'";
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY created_at DESC";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy đơn hàng theo ID
    function order_getById($id) {
        $sql = "SELECT 
                    o.id,
                    o.user_id,
                    o.customer_name,
                    o.customer_phone,
                    o.customer_email,
                    p.name as shipping_province,
                    d.name as shipping_district,
                    w.name as shipping_ward,
                    o.shipping_address_detail,
                    o.subtotal,
                    o.shipping_fee,
                    o.voucher_code,
                    o.discount_amount,
                    o.points_used,
                    o.points_discount,
                    o.total_money,
                    o.points_earned,
                    o.payment_method,
                    o.status,
                    o.note,
                    o.created_at
                FROM orders o
                LEFT JOIN provinces p ON o.shipping_province = p.code
                LEFT JOIN districts d ON o.shipping_district = d.code
                LEFT JOIN wards w ON o.shipping_ward = w.code
                WHERE o.id = $id";
        $result = mysqli_query($this->con, $sql);
        return mysqli_fetch_array($result);
    }
    
    // Cập nhật trạng thái đơn hàng
    function order_updateStatus($id, $status) {
        $sql = "UPDATE orders 
                SET status = '$status' 
                WHERE id = $id";
        return mysqli_query($this->con, $sql);
    }

    // Cập nhật trạng thái và ghi nhận điểm đã cộng
    function order_updateStatusWithPoints($id, $status, $points_earned) {
        $id = intval($id);
        $status = mysqli_real_escape_string($this->con, $status);
        $points_earned = intval($points_earned);
        $sql = "UPDATE orders SET status = '$status', points_earned = $points_earned WHERE id = $id";
        return mysqli_query($this->con, $sql);
    }

    // Cộng điểm cho người dùng
    function user_addPoints($user_id, $points) {
        $user_id = intval($user_id);
        $points = intval($points);
        // Đảm bảo cột points tồn tại và là số, cộng dồn
        $sql = "UPDATE users SET points = COALESCE(points, 0) + $points WHERE id = $user_id";
        return mysqli_query($this->con, $sql);
    }
    
    // Kiểm tra cột tồn tại
    private function orderItems_hasColumn($column) {
        $column = mysqli_real_escape_string($this->con, $column);
        $sql = "SHOW COLUMNS FROM order_items LIKE '$column'";
        $res = mysqli_query($this->con, $sql);
        return $res && mysqli_num_rows($res) > 0;
    }

    // Lấy chi tiết sản phẩm trong đơn hàng từ bảng order_items
    function orderItems_getByOrderId($order_id) {
        $hasVariantId = $this->orderItems_hasColumn('variant_id');
        $hasSize = $this->orderItems_hasColumn('size');
        $hasColor = $this->orderItems_hasColumn('color');

        // Base select
        $select = [
            'oi.id',
            'oi.product_id',
            'oi.quantity',
            'oi.price',
            '(oi.price * oi.quantity) as total',
            'p.name as product_name',
            'p.thumbnail as product_image'
        ];

        // Add variant-related fields
        $join = '';
        if ($hasVariantId) {
            $select[] = 'oi.variant_id';
            $join .= ' LEFT JOIN product_variants pv ON oi.variant_id = pv.id ';
            // Prefer size/color from order_items if present, else from pv
            if ($hasSize) {
                $select[] = 'oi.size as size';
            } else {
                $select[] = 'pv.size as size';
            }
            if ($hasColor) {
                $select[] = 'oi.color as color';
            } else {
                $select[] = 'pv.color as color';
            }
        } else {
            if ($hasSize) { $select[] = 'oi.size as size'; }
            if ($hasColor) { $select[] = 'oi.color as color'; }
        }

        $sql = 'SELECT ' . implode(',', $select) . ' FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id ' . $join . ' WHERE oi.order_id = ' . intval($order_id);
        return mysqli_query($this->con, $sql);
    }
    
    // Xóa các items của đơn hàng
    function orderItems_deleteByOrderId($order_id) {
        $order_id = mysqli_real_escape_string($this->con, $order_id);
        $sql = "DELETE FROM order_items WHERE order_id = $order_id";
        return mysqli_query($this->con, $sql);
    }
    
    // Xóa đơn hàng
    function order_delete($order_id) {
        $order_id = mysqli_real_escape_string($this->con, $order_id);
        $sql = "DELETE FROM orders WHERE id = $order_id";
        return mysqli_query($this->con, $sql);
    }
    
    // Đếm số đơn hàng theo trạng thái
    function orders_countByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as total FROM orders WHERE status = '$status'";
        } else {
            $sql = "SELECT COUNT(*) as total FROM orders";
        }
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_array($result);
        return $row['total'];
    }
}
?>