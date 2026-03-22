<?php
class overview_m extends connectDB
{
    function __construct(){
        parent::__construct();
    }
    
      // Đếm tổng số đơn hàng
    function countOrders() {
        $sql = "SELECT COUNT(*) as total FROM orders";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Đếm đơn hàng mới (trạng thái chờ xử lý)
    function countPendingOrders() {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Đếm tổng số khách hàng (không tính admin)
    function countCustomers() {
        $sql = "SELECT COUNT(*) as total FROM users WHERE role != 'admin'";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Đếm tổng số sản phẩm
    function countProducts() {
        $sql = "SELECT COUNT(*) as total FROM products";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Tính tổng doanh thu tháng này
    // function getMonthlyRevenue() {
    //     $sql = "SELECT SUM(total_amount) as revenue FROM orders 
    //             WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    //             AND YEAR(created_at) = YEAR(CURRENT_DATE())
    //             AND status = 'completed'";
    //     $result = mysqli_query($this->con, $sql);
    //     $row = mysqli_fetch_assoc($result);
    //     return $row['revenue'] ?? 0;
    // }
    
    // Lấy dữ liệu doanh thu theo số ngày (chỉ tính đơn hoàn thành)
    function getRevenueLast7Days($days = 7) {
        $days_minus = $days - 1;
        $sql = "SELECT 
                    DATE(created_at) as date,
                    SUM(total_money) as revenue
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $days_minus DAY)
                AND status = 'completed'
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $result = mysqli_query($this->con, $sql);
        
        // Tạo mảng với giá trị mặc định là 0
        $data = [];
        for ($i = $days_minus; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data[$date] = 0;
        }
        
        // Điền dữ liệu thực tế vào mảng
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['date']] = floatval($row['revenue']);
        }
        
        return $data;
    }

    // Lấy dữ liệu doanh thu theo khoảng ngày (chỉ đơn hoàn thành)
    function getRevenueRange($from, $to) {
        $from = mysqli_real_escape_string($this->con, $from);
        $to = mysqli_real_escape_string($this->con, $to);
        // Query sum by date in inclusive range
        $sql = "SELECT DATE(created_at) as date, SUM(total_money) as revenue
                FROM orders 
                WHERE created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'
                AND status = 'completed'
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        $result = mysqli_query($this->con, $sql);

        // Build all dates in range with default 0
        $data = [];
        $start = strtotime($from);
        $end = strtotime($to);
        if ($start === false || $end === false) { return []; }
        for ($t = $start; $t <= $end; $t = strtotime('+1 day', $t)) {
            $date = date('Y-m-d', $t);
            $data[$date] = 0;
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['date']] = floatval($row['revenue']);
        }
        return $data;
    }
    
    // Tính tổng doanh thu tháng này (chỉ đơn hoàn thành)
    function getMonthlyRevenue() {
        $sql = "SELECT SUM(total_money) as revenue FROM orders 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
                AND status = 'completed'";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['revenue'] ?? 0;
    }
    
    // Đếm số lượng đơn hàng theo trạng thái
    function getOrderStatusCounts() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM orders 
                GROUP BY status";
        
        $result = mysqli_query($this->con, $sql);
        
        // Khởi tạo mảng với giá trị mặc định
        $counts = [
            'pending' => 0,
            'confirmed' => 0,
            'shipping' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        // Điền dữ liệu thực tế
        while ($row = mysqli_fetch_assoc($result)) {
            $status = trim($row['status']);
            if (isset($counts[$status])) {
                $counts[$status] = intval($row['count']);
            }
        }
        
        return $counts;
    }
}
?>