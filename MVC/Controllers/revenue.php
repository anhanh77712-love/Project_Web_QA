<?php
class revenue extends controllers
{
    private $revenue;

    public function __construct()
    {
                parent::__construct();
        $this->revenue = $this->model('revenue_m');
    }

    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    public function Get_data()
    {
        $this->view('Master', [
            'Page' => 'revenue_v'
        ]);
    }

    // 2. API LẤY TOÀN BỘ SỐ LIỆU DOANH THU & THỐNG KÊ
    public function api_get_data()
    {
        $this->setApiHeader();
        
        $from   = $_GET['from_date'] ?? '';
        $to     = $_GET['to_date'] ?? '';
        $status = $_GET['status'] ?? 'all';

        // Đảm bảo logic from < to
        if (!empty($from) && !empty($to)) {
            if (strtotime($from) > strtotime($to)) {
                $tmp = $from; $from = $to; $to = $tmp;
            }
        }

        // Lấy dữ liệu từ Model
        $order_stats = $this->revenue->getOrderStatusStats($from, $to);
        $stats       = $this->revenue->getRevenueStats($from, $to);
        $top_products  = $this->revenue->getTopProductsProfit($from, $to, 5);
        $payment_stats = $this->revenue->getPaymentMethodStats($from, $to);
        $stats_size  = $this->revenue->getStatsBySize($from, $to);
        $stats_color = $this->revenue->getStatsByColor($from, $to);

        if (method_exists($this->revenue, 'getAllProductsProfit')) {
            $all_products = $this->revenue->getAllProductsProfit($from, $to);
        } else {
            $all_products = $this->revenue->getTopProductsProfit($from, $to, 20);
        }

        // Lấy danh sách đơn hàng chi tiết
        $orders_result = $this->revenue->getOrdersList($from, $to, $status); 
        $orders_list = [];
        if ($orders_result && mysqli_num_rows($orders_result) > 0) {
            while ($row = mysqli_fetch_assoc($orders_result)) {
                $orders_list[] = $row;
            }
        }

        // Trả về một cục JSON khổng lồ chứa mọi thứ
        echo json_encode([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'order_stats' => $order_stats,
                'payment_stats' => $payment_stats,
                'top_products_profit' => $top_products,
                'all_products_profit' => $all_products,
                'stats_size' => $stats_size,
                'stats_color' => $stats_color,
                'orders_list' => $orders_list
            ]
        ]);
        exit;
    }

    public function export_excel()
    {
        $from   = $_GET['from_date'] ?? '';
        $to     = $_GET['to_date'] ?? '';
        $status = $_GET['status'] ?? 'all';

        // Lấy danh sách đơn hàng để xuất
        $orders_list = $this->revenue->getOrdersList($from, $to, $status);

        if (!class_exists('PHPExcel')) require_once "./MVC/Bridge.php";

        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('BaoCaoDoanhThu');

        $rowCount = 1;
        $columns  = [
            'A' => 'Mã Đơn', 'B' => 'Khách Hàng', 'C' => 'Số Điện Thoại', 'D' => 'Ngày Đặt',
            'E' => 'Doanh Thu', 'F' => 'Giá Vốn', 'G' => 'Lợi Nhuận', 'H' => '% Lãi',
            'I' => 'Thanh Toán', 'J' => 'Trạng Thái',
        ];

        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . $rowCount, $title);
            $sheet->getStyle($col . $rowCount)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'E0E0E0']],
                'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            ]);
        }

        if ($orders_list && mysqli_num_rows($orders_list) > 0) {
            mysqli_data_seek($orders_list, 0);
            while ($row = mysqli_fetch_array($orders_list)) {
                $rowCount++;
                $total  = (float) $row['total_money'];
                $cost   = (float) ($row['total_cost'] ?? 0);
                $profit = $total - $cost;
                $margin = ($total > 0) ? round(($profit / $total) * 100, 1) : 0;

                $status_vi = match ($row['status']) {
                    'completed' => 'Hoàn thành', 'shipping'  => 'Đang giao',
                    'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy',
                    default     => 'Chờ xử lý',
                };

                $sheet->setCellValue('A' . $rowCount, '#' . $row['id']);
                $sheet->setCellValue('B' . $rowCount, $row['customer_name']);
                $sheet->setCellValue('C' . $rowCount, $row['customer_phone']);
                $sheet->setCellValue('D' . $rowCount, date('d/m/Y H:i', strtotime($row['created_at'])));
                $sheet->setCellValue('E' . $rowCount, $total);
                $sheet->setCellValue('F' . $rowCount, $cost);
                $sheet->setCellValue('G' . $rowCount, $profit);
                $sheet->setCellValue('H' . $rowCount, $margin . '%');
                $sheet->setCellValue('I' . $rowCount, strtoupper($row['payment_method']));
                $sheet->setCellValue('J' . $rowCount, $status_vi);

                $sheet->getStyle('E' . $rowCount . ':G' . $rowCount)->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        foreach (array_keys($columns) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_contents()) ob_end_clean();

        $date_suffix = (!empty($from) && !empty($to)) ? "{$from}_{$to}" : date('Y-m-d');
        $filename = "Bao_cao_doanh_thu_" . $date_suffix . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
?>