<?php
class overview extends controllers{
    private $ov;
    function __construct(){
        $this->ov=$this->model("overview_m");
    }
    
    function Get_data(){
        // Hỗ trợ lọc theo khoảng ngày nếu có
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        if (!empty($from) && !empty($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            if (strtotime($from) > strtotime($to)) { $tmp = $from; $from = $to; $to = $tmp; }
            $revenue_data = $this->ov->getRevenueRange($from, $to);
        } else {
            // Mặc định: Lấy dữ liệu doanh thu 7 ngày
            $revenue_data = $this->ov->getRevenueLast7Days(7);
        }
        
        // Chuẩn bị dữ liệu cho Chart.js
        $labels = [];
        $values = [];
        
        foreach ($revenue_data as $date => $revenue) {
            $labels[] = date('d/m', strtotime($date));
            $values[] = $revenue;
        }
        
        // Lấy số lượng đơn hàng theo trạng thái
        $status_counts = $this->ov->getOrderStatusCounts();
        
        $this->view('Master',[
            'Page'=>'overview_v',
            'total_orders' => $this->ov->countOrders(),
            'pending_orders' => $this->ov->countPendingOrders(),
            'total_customers' => $this->ov->countCustomers(),
            'total_products' => $this->ov->countProducts(),
            'monthly_revenue' => $this->ov->getMonthlyRevenue(),
            'revenue_labels' => json_encode($labels),
            'revenue_values' => json_encode($values),
            'status_counts' => json_encode(array_values($status_counts)),
            'rev_from' => !empty($from) ? $from : null,
            'rev_to' => !empty($to) ? $to : null
        ]);
    }
    
    // API lấy dữ liệu doanh thu theo số ngày
    function get_revenue_data() {
        // Ưu tiên lọc theo khoảng ngày nếu có from/to
        $from = isset($_GET['from']) ? trim($_GET['from']) : '';
        $to = isset($_GET['to']) ? trim($_GET['to']) : '';
        if (!empty($from) && !empty($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            if (strtotime($from) > strtotime($to)) { $tmp = $from; $from = $to; $to = $tmp; }
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
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'values' => $values
        ]);
    }
}
