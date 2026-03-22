<?php
class revenue_m extends connectDB
{
    public function __construct()
    {
        parent::__construct();
    }

    public function buildTimeCondition($startDate, $endDate, $column = 'created_at')
    {
        $condition = "";

        // Nếu rỗng, không chặn dữ liệu để xem kết quả tổng quát
        if (empty($startDate) && empty($endDate)) {
            return "";
        }

        // Đảm bảo định dạng Y-m-d
        if (! empty($startDate)) {
            $startDate = date('Y-m-d', strtotime($startDate));
        }

        if (! empty($endDate)) {
            $endDate = date('Y-m-d', strtotime($endDate));
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $condition = " AND $column BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
        } elseif (! empty($startDate)) {
            $condition = " AND $column >= '$startDate 00:00:00'";
        } elseif (! empty($endDate)) {
            $condition = " AND $column <= '$endDate 23:59:59'";
        }

        return $condition;
    }

    public function getRevenueStats($startDate = '', $endDate = '')
    {
        $status_completed = 'completed';
        // Giả sử hàm buildTimeCondition trả về chuỗi kiểu "AND created_at BETWEEN..."
        $timeCondition = $this->buildTimeCondition($startDate, $endDate);

        // --- 1. TÍNH DOANH THU THỰC TẾ (Tổng tiền thu về) ---
        // Lợi nhuận = Doanh thu thực - Giá vốn hàng bán
        $sql_revenue = "SELECT
                        SUM(subtotal) as total_subtotal,
                        SUM(shipping_fee) as total_shipping,
                        SUM(discount_amount) as total_voucher_discount,
                        SUM(points_discount) as total_point_discount,
                        SUM(total_money) as total_final_money
                    FROM orders o
                    WHERE o.status = '$status_completed' $timeCondition";

        $query_revenue = mysqli_query($this->con, $sql_revenue);
        $data_revenue  = mysqli_fetch_assoc($query_revenue);

        // Xử lý số liệu doanh thu
        $subtotal = (float) ($data_revenue['total_subtotal'] ?? 0);
        $shipping = (float) ($data_revenue['total_shipping'] ?? 0);
        $discount = (float) ($data_revenue['total_voucher_discount'] ?? 0) + (float) ($data_revenue['total_point_discount'] ?? 0);

        // Doanh thu thực tế (Tiền đút túi)
        $real_revenue = ($subtotal + $shipping) - $discount;

        // --- 2. TÍNH GIÁ VỐN HÀNG BÁN & TỔNG SỐ LƯỢNG BÁN ---
        // (Dùng để tính Lợi nhuận và đếm số lượng bán ra trong khoảng thời gian)
        $sql_sold = "SELECT
                    SUM(oi.quantity * pv.input_price) as total_cogs, -- Giá vốn của hàng ĐÃ BÁN
                    SUM(oi.quantity) as total_sold_qty             -- Tổng số lượng ĐÃ BÁN
                 FROM order_items oi
                 JOIN product_variants pv ON oi.variant_id = pv.id
                 JOIN orders o ON oi.order_id = o.id
                 WHERE o.status = '$status_completed' $timeCondition";

        $query_sold = mysqli_query($this->con, $sql_sold);
        $data_sold  = mysqli_fetch_assoc($query_sold);

        $cost_of_goods_sold  = (float) ($data_sold['total_cogs'] ?? 0);
        $total_sold_quantity = (int) ($data_sold['total_sold_qty'] ?? 0);

        // --- 3. TÍNH TỔNG TIỀN TRONG KHO (VỐN TỒN KHO) ---
        // (Đây là giá trị hàng đang nằm trong kho hiện tại, không phụ thuộc vào timeCondition)
        $sql_stock = "SELECT SUM(stock * input_price) as total_stock_value FROM product_variants";

        $query_stock = mysqli_query($this->con, $sql_stock);
        $data_stock  = mysqli_fetch_assoc($query_stock);

        $total_stock_value = (float) ($data_stock['total_stock_value'] ?? 0);

        // --- 4. TRẢ VỀ KẾT QUẢ ---
        return [
            'revenue'       => $real_revenue,                       // Tổng doanh thu (Thực tế thu về)
            'stock_capital' => $total_stock_value,                  // Tiền vốn đang nằm trong kho (Chưa bán)
            'profit'        => $real_revenue - $cost_of_goods_sold, // Lợi nhuận = Doanh thu - Vốn hàng đã bán
            'sold_quantity' => $total_sold_quantity,                // Tổng số lượng bán
        ];
    }

    public function getOrdersList($startDate = '', $endDate = '', $status = 'all')
    {
        $timeCondition = $this->buildTimeCondition($startDate, $endDate);

        // Thêm logic lọc theo trạng thái đơn hàng
        $statusCondition = "";
        if ($status !== 'all' && ! empty($status)) {
            $status          = mysqli_real_escape_string($this->con, $status);
            $statusCondition = " AND o.status = '$status'";
        }

        $sql = "SELECT o.*,
                   (o.discount_amount + o.points_discount) as total_discount_row,
                   (SELECT SUM(oi.quantity * pv.input_price)
                    FROM order_items oi
                    JOIN product_variants pv ON oi.variant_id = pv.id
                    WHERE oi.order_id = o.id) as total_cost
            FROM orders o
            WHERE 1=1 $timeCondition $statusCondition
            ORDER BY o.created_at DESC";

        return mysqli_query($this->con, $sql);
    }
    // FILE: models/revenue_m.php

    public function getTopProductsProfit($startDate = '', $endDate = '', $limit = 10)
    {
        // 1. Lấy điều kiện thời gian
        $timeCondition = $this->buildTimeCondition($startDate, $endDate, 'o.created_at');

        // 2. SQL UPDATE: Phải JOIN bảng product_variants để lấy giá vốn (input_price)
        $sql = "SELECT
                p.id as product_id,
                p.name as product_name,
                SUM(oi.quantity) as total_sold_qty,
                -- Tính lợi nhuận: (Giá bán - Giá vốn biến thể) * Số lượng
                SUM(oi.quantity * (oi.price - COALESCE(pv.input_price, 0))) as total_profit
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            -- JOIN với bảng biến thể để lấy giá vốn
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            -- JOIN với bảng sản phẩm để lấy tên
            JOIN products p ON oi.product_id = p.id
            WHERE o.status = 'completed' $timeCondition
            GROUP BY p.id, p.name
            ORDER BY total_profit DESC
            LIMIT $limit";

        $result = mysqli_query($this->con, $sql);
        $data   = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
// Trong file revenue_m.php

    public function getAllProductsProfit($startDate = '', $endDate = '')
    {
        $timeCondition = $this->buildTimeCondition($startDate, $endDate, 'o.created_at');

        // Câu lệnh SQL lấy TẤT CẢ sản phẩm, sắp xếp theo Lợi nhuận giảm dần
        $sql = "SELECT
                p.id as product_id,
                p.name as product_name,
                SUM(oi.quantity) as total_sold_qty,
                SUM(oi.quantity * (oi.price - COALESCE(pv.input_price, 0))) as total_profit
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.status = 'completed' $timeCondition
            GROUP BY p.id, p.name
            ORDER BY total_profit DESC"; // Không có LIMIT

        $result = mysqli_query($this->con, $sql);
        $data   = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getStatsBySize($startDate = '', $endDate = '')
    {
        $timeCondition = $this->buildTimeCondition($startDate, $endDate, 'o.created_at');
        $sql           = "SELECT oi.size, SUM(oi.quantity) as tong_so_luong
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed' $timeCondition
            GROUP BY oi.size";
        $result = mysqli_query($this->con, $sql);
        $data   = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data; // Trả về mảng chứa Size và tong_so_luong
    }

    public function getStatsByColor($startDate = '', $endDate = '')
    {
        $timeCondition = $this->buildTimeCondition($startDate, $endDate, 'o.created_at');
        $sql           = "SELECT oi.color, SUM(oi.quantity) as tong_so_luong
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed' $timeCondition
            GROUP BY oi.color";
        $result = mysqli_query($this->con, $sql);
        $data   = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data; // Trả về mảng chứa Color và tong_so_luong
    }

    public function getOrderStatusStats($from = '', $to = '')
    {
        $data       = ['pending' => 0, 'confirmed' => 0, 'shipping' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => 0];
        $sql        = "SELECT status, COUNT(*) as qty FROM orders";
        $conditions = [];

        if (! empty($from)) {
            $from         = mysqli_real_escape_string($this->con, $from);
            $conditions[] = "created_at >= '$from 00:00:00'";
        }
        if (! empty($to)) {
            $to           = mysqli_real_escape_string($this->con, $to);
            $conditions[] = "created_at <= '$to 23:59:59'";
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY status";
        $result = mysqli_query($this->con, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $st = trim($row['status']);
                if (isset($data[$st])) {
                    $data[$st] = (int) $row['qty'];
                    $data['total'] += (int) $row['qty'];
                }
            }
        }
        return $data;
    }

    public function getOrderStatusStats_Integrated($from = '', $to = '')
    {
        // 1. Khởi tạo mảng mặc định giống hệt overview_m để tránh lỗi Undefined
        $counts = [
            'pending'   => 0,
            'confirmed' => 0,
            'shipping'  => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total'     => 0,
        ];

        // 2. Xây dựng SQL lọc linh hoạt: Không có ngày thì lấy hết, có ngày thì lọc
        $sql        = "SELECT status, COUNT(*) as qty FROM orders";
        $conditions = [];

        if (! empty($from)) {
            $from         = mysqli_real_escape_string($this->con, $from);
            $conditions[] = "created_at >= '$from 00:00:00'";
        }
        if (! empty($to)) {
            $to           = mysqli_real_escape_string($this->con, $to);
            $conditions[] = "created_at <= '$to 23:59:59'";
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY status";
        $result = mysqli_query($this->con, $sql);

        // 3. Đổ dữ liệu vào mảng
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $status = trim($row['status']);
                if (isset($counts[$status])) {
                    $counts[$status] = (int) $row['qty'];
                    $counts['total'] += (int) $row['qty'];
                }
            }
        }
        return $counts;
    }
    // Thêm vào file models/revenue_m.php

    public function getPaymentMethodStats($startDate = '', $endDate = '')
    {
        $timeCondition = $this->buildTimeCondition($startDate, $endDate, 'created_at');

        // Chỉ tính các đơn ĐÃ HOÀN THÀNH (completed) để đảm bảo doanh thu chính xác
        $sql = "SELECT
                payment_method,
                COUNT(id) as qty,
                SUM(total_money) as revenue
            FROM orders
            WHERE status = 'completed' $timeCondition
            GROUP BY payment_method
            ORDER BY revenue DESC";

        $result = mysqli_query($this->con, $sql);
        $data   = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
}
