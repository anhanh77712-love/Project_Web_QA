<?php
class vouchers extends controllers
{
    private $vouchers;
    
    public function __construct()
    {
        parent::__construct();
        $this->vouchers = $this->model('vouchers_m');
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
            'Page' => 'vouchers_v'
        ]);
    }

    // 2. API: LẤY DANH SÁCH VOUCHERS (Có Tìm kiếm & Lọc)
    public function api_get_data()
    {
        $this->setApiHeader();
        
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

        $data = [];
        $result = $this->vouchers->vouchers_select_search($search);

        if ($result && mysqli_num_rows($result) > 0) {
            $now = time();
            while ($row = mysqli_fetch_assoc($result)) {
                // Tính toán trạng thái thực tế
                $start = strtotime($row['start_date']);
                $end = strtotime($row['end_date']);
                $real_status = '';

                if ($row['status'] == 0) {
                    $real_status = 'hidden';
                } elseif ($row['used_count'] >= $row['usage_limit']) {
                    $real_status = 'empty';
                } elseif ($now > $end) {
                    $real_status = 'expired';
                } elseif ($now < $start) {
                    $real_status = 'upcoming';
                } else {
                    $real_status = 'running';
                }

                // Lọc dữ liệu theo yêu cầu
                if ($filter != 'all' && $filter != $real_status) {
                    continue; 
                }

                // Gắn thêm thuộc tính để JS dễ xử lý
                $row['real_status'] = $real_status;
                $row['start_timestamp'] = $start;
                $row['end_timestamp'] = $end;
                $data[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // 3. API: THÊM VOUCHER MỚI
    public function api_add()
    {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $code = $_POST['code'] ?? '';
        $description = $_POST['description'] ?? '';
        $discount_type = $_POST['type'] ?? 'fixed';
        $discount_value = $_POST['value'] ?? 0;
        $max_discount_amount = !empty($_POST['max_discount']) ? $_POST['max_discount'] : 'NULL';
        $min_order_value = !empty($_POST['min_order']) ? $_POST['min_order'] : 0;
        $usage_limit = !empty($_POST['usage_limit']) ? $_POST['usage_limit'] : 100;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = isset($_POST['status']) ? $_POST['status'] : 1;

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Mã voucher không được để trống']); exit;
        }
        if (!empty($start_date) && !empty($end_date) && $end_date < $start_date) {
            echo json_encode(['success' => false, 'message' => 'Ngày kết thúc phải sau ngày bắt đầu']); exit;
        }
        if ($discount_type == 'percent' && $discount_value > 100) {
            echo json_encode(['success' => false, 'message' => 'Giảm theo phần trăm không được vượt quá 100%']); exit;
        }

        $kq = $this->vouchers->vouchers_insert($code, $description, $discount_type, $discount_value, $max_discount_amount, $min_order_value, $usage_limit, $start_date, $end_date, $status);

        echo json_encode(['success' => $kq, 'message' => $kq ? 'Thêm voucher thành công' : 'Lỗi thêm dữ liệu']);
        exit;
    }

    // 4. API: CẬP NHẬT VOUCHER
    public function api_update()
    {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $id = $_POST['id'] ?? '';
        $usage_limit = $_POST['usage_limit'] ?? 0;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $discount_value = $_POST['value'] ?? 0;
        $min_order_value = $_POST['min_order'] ?? 0;
        $max_discount_amount = !empty($_POST['max_discount']) ? $_POST['max_discount'] : 'NULL';
        $status = $_POST['status'] ?? 1;

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cập nhật']); exit;
        }
        if (!empty($start_date) && !empty($end_date) && $end_date < $start_date) {
            echo json_encode(['success' => false, 'message' => 'Ngày kết thúc phải sau ngày bắt đầu']); exit;
        }

        $kq = $this->vouchers->vouchers_update($id, $usage_limit, $start_date, $end_date, $discount_value, $min_order_value, $max_discount_amount, $status);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Cập nhật voucher thành công' : 'Lỗi cập nhật dữ liệu']);
        exit;
    }

    // 5. API: XÓA VOUCHER
    public function api_delete($id)
    {
        $this->setApiHeader();
        $kq = $this->vouchers->vouchers_delete($id);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã xóa voucher' : 'Lỗi khi xóa']);
        exit;
    }

    // 6. XUẤT EXCEL (Tải trực tiếp)
    public function export_excel()
    {
        $keyword = $_GET['q'] ?? '';
        
        if (!class_exists('PHPExcel')) require_once "./MVC/Bridge.php";

        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Danh_Sach_Vouchers');

        $rowCount = 1;
        $columns = ['A'=>'ID', 'B'=>'Mã Code', 'C'=>'Mô tả', 'D'=>'Loại giảm', 'E'=>'Giá trị', 'F'=>'Giảm tối đa', 'G'=>'Đơn tối thiểu', 'H'=>'Tổng giới hạn', 'I'=>'Đã dùng', 'J'=>'Ngày bắt đầu', 'K'=>'Ngày kết thúc', 'L'=>'Trạng thái'];

        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . $rowCount, $title);
            $sheet->getStyle($col . $rowCount)->getFont()->setBold(true);
            $sheet->getStyle($col . $rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $rowCount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE0B2');
        }

        $data = $this->vouchers->vouchers_select_search($keyword);

        if ($data && mysqli_num_rows($data) > 0) {
            while ($row = mysqli_fetch_array($data)) {
                $rowCount++;
                
                $sheet->setCellValue('A'.$rowCount, $row['id']);
                $sheet->setCellValue('B'.$rowCount, $row['code']);
                $sheet->setCellValue('C'.$rowCount, $row['description']);
                $sheet->setCellValue('D'.$rowCount, ($row['discount_type'] == 'percent') ? 'Theo %' : 'Tiền mặt');
                
                $sheet->setCellValue('E'.$rowCount, $row['discount_value']);
                $sheet->getStyle('E'.$rowCount)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->setCellValue('F'.$rowCount, $row['max_discount_amount']);
                $sheet->getStyle('F'.$rowCount)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->setCellValue('G'.$rowCount, $row['min_order_value']);
                $sheet->getStyle('G'.$rowCount)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->setCellValue('H'.$rowCount, $row['usage_limit']);
                $sheet->setCellValue('I'.$rowCount, $row['used_count']);
                $sheet->setCellValue('J'.$rowCount, $row['start_date']);
                $sheet->setCellValue('K'.$rowCount, $row['end_date']);

                $now = time();
                $start = strtotime($row['start_date']);
                $end = strtotime($row['end_date']);
                
                $text_status = ''; $color_status = '000000';
                if ($row['status'] == 0) { $text_status = 'Đang ẩn'; $color_status = '808080'; } 
                elseif ($row['used_count'] >= $row['usage_limit']) { $text_status = 'Hết lượt'; $color_status = '000000'; } 
                elseif ($now > $end) { $text_status = 'Đã kết thúc'; $color_status = 'FF0000'; } 
                elseif ($now < $start) { $text_status = 'Sắp diễn ra'; $color_status = 'FFA500'; } 
                else { $text_status = 'Đang chạy'; $color_status = '008000'; }

                $sheet->setCellValue('L'.$rowCount, $text_status);
                $sheet->getStyle('L'.$rowCount)->getFont()->getColor()->setARGB($color_status);
            }
        } else {
            $rowCount++;
            $sheet->setCellValue('A'.$rowCount, "Không tìm thấy dữ liệu.");
            $sheet->mergeCells('A'.$rowCount.':L'.$rowCount);
        }

        foreach (range('A', 'L') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        if (ob_get_length()) ob_end_clean();
        $filename = "Vouchers_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

   
}
?>