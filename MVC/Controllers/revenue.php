<?php
class revenue extends controllers
{
    private $revenue;

    public function __construct()
    {
        $this->revenue = $this->model('revenue_m');
    }
    public function Get_data()
    {
        $from   = $_POST['from_date'] ?? $_GET['from_date'] ?? '';
        $to     = $_POST['to_date'] ?? $_GET['to_date'] ?? '';
        $status = $_POST['status'] ?? $_GET['status'] ?? 'all';

        $order_stats = $this->revenue->getOrderStatusStats($from, $to);
        $stats       = $this->revenue->getRevenueStats($from, $to);
        $orders_list = $this->revenue->getOrdersList($from, $to, $status); 

        $top_products  = $this->revenue->getTopProductsProfit($from, $to, 5);
        $payment_stats = $this->revenue->getPaymentMethodStats($from, $to);

      
        if (method_exists($this->revenue, 'getAllProductsProfit')) {
            $all_products = $this->revenue->getAllProductsProfit($from, $to);
        } else {
            $all_products = $this->revenue->getTopProductsProfit($from, $to, 20);
        }

        $stats_size  = $this->revenue->getStatsBySize($from, $to);
        $stats_color = $this->revenue->getStatsByColor($from, $to);

      
        if (isset($_POST['export']) && $_POST['export'] == '1') {
            // SỬA Ở ĐÂY: Truyền thêm $from và $to vào hàm
            $this->xuatExcel($orders_list, $from, $to);

            // Dừng chương trình ngay lập tức
            return;
        }

        $this->view('Master', [
            'Page'                => 'revenue_v',
            'order_stats'         => $order_stats,
            'stats'               => $stats,
            'orders_list'         => $orders_list,
            'top_products_profit' => $top_products,
            'all_products_profit' => $all_products,
            'payment_stats'       => $payment_stats,
            'stats_size'          => $stats_size,
            'stats_color'         => $stats_color,
            'filter_from'         => $from,
            'filter_to'           => $to,
        ]);
    }

    // Thêm tham số $orders_list vào trong ngoặc
    // SỬA Ở ĐÂY: Thêm tham số $from, $to vào định nghĩa hàm
    public function xuatExcel($orders_list, $from, $to)
    {
        // 1. Kiểm tra thư viện
        if (! class_exists('PHPExcel')) {
            require_once "./MVC/Bridge.php";
            if (! class_exists('PHPExcel')) {
                die("Lỗi: Không tìm thấy thư viện PHPExcel.");
            }
        }

        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('BaoCaoDoanhThu');

        // 2. Cấu hình Header
        $rowCount = 1;
        $columns  = [
            'A' => 'Mã Đơn',
            'B' => 'Khách Hàng',
            'C' => 'Số Điện Thoại',
            'D' => 'Ngày Đặt',
            'E' => 'Doanh Thu',
            'F' => 'Giá Vốn',
            'G' => 'Lợi Nhuận',
            'H' => '% Lãi',
            'I' => 'Thanh Toán',
            'J' => 'Trạng Thái',
        ];

        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . $rowCount, $title);
            $sheet->getStyle($col . $rowCount)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'E0E0E0']],
                'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            ]);
        }

        // 3. Đổ dữ liệu
        if ($orders_list && mysqli_num_rows($orders_list) > 0) {
            mysqli_data_seek($orders_list, 0);
            while ($row = mysqli_fetch_array($orders_list)) {
                $rowCount++;

                // Tính toán
                $total  = (float) $row['total_money'];
                $cost   = (float) ($row['total_cost'] ?? 0);
                $profit = $total - $cost;
                $margin = ($total > 0) ? round(($profit / $total) * 100, 1) : 0;

                // Trạng thái
                $status_vi = match ($row['status']) {
                    'completed' => 'Hoàn thành',
                    'shipping'  => 'Đang giao',
                    'confirmed' => 'Đã xác nhận',
                    'cancelled' => 'Đã hủy',
                    default     => 'Chờ xử lý',
                };

                // Gán dữ liệu
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

        // 4. Auto-size
        foreach (array_keys($columns) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5. Xuất file & Xử lý tên file (SỬA Ở ĐÂY)
        if (ob_get_contents()) {
            ob_end_clean();
        }

        
        if (! empty($from) && ! empty($to)) {
            $date_suffix = $from . '_' . $to; // Ví dụ: 2023-10-01_2023-10-30
        } else {
            // Nếu thiếu 1 trong 2 hoặc không có thì dùng ngày hiện tại
            $date_suffix = date('Y-m-d');
        }

        $filename = "Bao_cao_doanh_thu_" . $date_suffix . ".xlsx";
        // ----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

}
