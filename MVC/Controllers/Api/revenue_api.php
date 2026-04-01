<?php
class revenue_api extends controllers
{
    private $revenueModel;

    public function __construct()
    {
        $this->revenueModel = $this->model('revenue_m');
    }

    // Lay tong quan doanh thu JSON
    public function get_summary()
    {
        header('Content-Type: application/json; charset=utf-8');
        $from = $_GET['from_date'] ?? '';
        $to   = $_GET['to_date'] ?? '';

        $stats = $this->revenueModel->getRevenueStats($from, $to);
        
        echo json_encode([
            'success' => true,
            'data'    => $stats
        ]);
    }

    // Lay thong ke trang thai don hang JSON
    public function get_order_status()
    {
        header('Content-Type: application/json; charset=utf-8');
        $from = $_GET['from_date'] ?? '';
        $to   = $_GET['to_date'] ?? '';

        $order_stats = $this->revenueModel->getOrderStatusStats($from, $to);

        echo json_encode([
            'success' => true,
            'data'    => $order_stats
        ]);
    }

    // Lay top san pham theo loi nhuan JSON
    public function get_top_products()
    {
        header('Content-Type: application/json; charset=utf-8');
        $from = $_GET['from_date'] ?? '';
        $to   = $_GET['to_date'] ?? '';
        $limit = $_GET['limit'] ?? 5;

        $top = $this->revenueModel->getTopProductsProfit($from, $to, (int)$limit);

        echo json_encode([
            'success' => true,
            'data'    => $top
        ]);
    }

    // Lay thong ke theo size va mau sac JSON
    public function get_attribute_stats()
    {
        header('Content-Type: application/json; charset=utf-8');
        $from = $_GET['from_date'] ?? '';
        $to   = $_GET['to_date'] ?? '';

        $size  = $this->revenueModel->getStatsBySize($from, $to);
        $color = $this->revenueModel->getStatsByColor($from, $to);

        echo json_encode([
            'success' => true,
            'data'    => [
                'size'  => $size,
                'color' => $color
            ]
        ]);
    }
}