<?php
class overview extends controllers {
    private $ov;
    
    function __construct() {
                parent::__construct();

        $this->ov = $this->model("overview_m");
    }
    
    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'overview_v'
        ]);
    }
    
    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 2. API LẤY SỐ LIỆU TỔNG QUAN VÀ BIỂU ĐỒ TRÒN
    function api_get_summary() {
        $this->setApiHeader();
        
        $status_counts = $this->ov->getOrderStatusCounts();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_orders' => $this->ov->countOrders(),
                'pending_orders' => $this->ov->countPendingOrders(),
                'total_customers' => $this->ov->countCustomers(),
                'total_products' => $this->ov->countProducts(),
                'monthly_revenue' => $this->ov->getMonthlyRevenue(),
                'status_counts' => array_values($status_counts) // Chuyển thành mảng index cho Chart.js
            ]
        ]);
        exit;
    }
    
    // 3. API LẤY DỮ LIỆU BIỂU ĐỒ DOANH THU (Có hỗ trợ lọc)
    function api_get_revenue() {
        $this->setApiHeader();
        
        // Ưu tiên lọc theo khoảng ngày nếu có from/to
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        
        if (!empty($from) && !empty($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            if (strtotime($from) > strtotime($to)) { 
                $tmp = $from; $from = $to; $to = $tmp; 
            }
            $revenue_data = $this->ov->getRevenueRange($from, $to);
        } else {
            $days = isset($_GET['days']) ? intval($_GET['days']) : 7;
            $revenue_data = $this->ov->getRevenueLast7Days($days);
        }
        
        $labels = [];
        $values = [];
        
        foreach ($revenue_data as $date => $revenue) {
            $labels[] = date('d/m', strtotime($date));
            $values[] = $revenue;
        }
        
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
        exit;
    }
}
?>