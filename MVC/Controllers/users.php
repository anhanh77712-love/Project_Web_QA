<?php
class users extends controllers {
    private $user;
    
    function __construct() {
                parent::__construct();
        $this->user = $this->model('users_m');
    }

    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'users_v'
        ]);
    }

    // 2. API LẤY DANH SÁCH (CÓ TÌM KIẾM)
    function api_get_data() {
        $this->setApiHeader();
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        $result = $q !== '' ? $this->user->users_searchByName($q) : $this->user->users_selectAll();
        $data = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // 3. API THÊM KHÁCH HÀNG MỚI
    function api_add() {
        $this->setApiHeader();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];

            // Cần băm mật khẩu trước khi lưu (Bảo mật)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $kq = $this->user->users_insert_default(
                $full_name, $email, $phone, $hashed_password,
                null, null, null, null, null, null
            );

            if ($kq === true) {
                echo json_encode(['success' => true, 'message' => 'Thêm khách hàng thành công!']);
            } elseif ($kq === "EMAIL_EXISTED") {
                echo json_encode(['success' => false, 'message' => 'Email này đã tồn tại!']);
            } elseif ($kq === "PHONE_EXISTED") {
                echo json_encode(['success' => false, 'message' => 'Số điện thoại này đã tồn tại!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi thêm khách hàng!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']);
        }
        exit;
    }

    // 4. API XÓA KHÁCH HÀNG
    function api_delete($id) {
        $this->setApiHeader();
        $kq = $this->user->users_delete($id);
        
        if ($kq) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa khách hàng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa khách hàng!']);
        }
        exit;
    }

    // ==========================================
    // CÁC API CŨ CỦA BẠN (Đã chuẩn JSON)
    // ==========================================

    function get_order_history($id) {
        $this->setApiHeader();
        $orders = $this->user->users_getOrderHistory($id);
        $data = [];
        
        if ($orders && mysqli_num_rows($orders) > 0) {
            while ($row = mysqli_fetch_assoc($orders)) {
                $row['created_at_format'] = date('d/m/Y H:i', strtotime($row['created_at']));
                $row['total_money_format'] = number_format($row['total_money'], 0, ',', '.') . ' VNĐ';
                $data[] = $row;
            }
        }
        echo json_encode($data);
        exit();
    }
    
    function get_order_details($order_id) {
        $this->setApiHeader();
        $orderModel = $this->model('orders_m');
        
        $orderInfo = $orderModel->order_getById($order_id);
        $itemsResult = $orderModel->orderItems_getByOrderId($order_id);
        $items = [];
        
        if ($itemsResult && mysqli_num_rows($itemsResult) > 0) {
            while ($row = mysqli_fetch_assoc($itemsResult)) {
                $items[] = $row;
            }
        }
        
        echo json_encode([
            'info' => $orderInfo,
            'items' => $items
        ]);
        exit();
    }

    // XUẤT EXCEL (Tải file trực tiếp)
    public function export_excel() {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $users = $q !== '' ? $this->user->users_searchByName($q) : $this->user->users_selectAll();

        if ($users && mysqli_num_rows($users) > 0) {
            if (!class_exists('PHPExcel')) require_once "./MVC/Bridge.php";
            
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();

            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'HỌ VÀ TÊN');
            $sheet->setCellValue('C1', 'EMAIL');
            $sheet->setCellValue('D1', 'SỐ ĐIỆN THOẠI');
            $sheet->setCellValue('E1', 'ĐIỂM TÍCH LŨY');
            $sheet->setCellValue('F1', 'ĐỊA CHỈ CHI TIẾT');
            $sheet->setCellValue('G1', 'NGÀY THAM GIA');

            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            $row = 2;
            while ($u = mysqli_fetch_assoc($users)) {
                $sheet->setCellValue('A' . $row, $u['id']);
                $sheet->setCellValue('B' . $row, $u['full_name']);
                $sheet->setCellValue('C' . $row, $u['email']);
                $sheet->setCellValue('D' . $row, $u['phone']);
                $sheet->setCellValue('E' . $row, $u['points']);
                $sheet->setCellValue('F' . $row, $u['address_detail']);
                $sheet->setCellValue('G' . $row, date('d/m/Y', strtotime($u['created_at'])));
                $row++;
            }

            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $filename = "Danh_sach_khach_hang_" . date('Ymd_His') . ".xlsx";
            if (ob_get_contents()) ob_end_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit();
        } else {
            echo "<script>alert('Không có dữ liệu để xuất!'); window.history.back();</script>";
        }
    }
}
?>