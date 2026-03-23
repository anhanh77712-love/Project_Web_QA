<?php
class users extends controllers {
    private $user;
    function __construct() {
        $this->user = $this->model('users_m');
    }
    function Get_data() {
        $this->view('Master', [
            'Page' => 'users_v',
            'users_list' => $this->user->users_selectAll()
        ]);
    }

    function search() {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $result = $q !== '' ? $this->user->users_searchByName($q) : $this->user->users_selectAll();
        $this->view('Master', [
            'Page' => 'users_v',
            'users_list' => $result,
            'search_q' => $q
        ]);
    }

    // Thêm hàm này vào class users trong file users.php
    function get_order_history($id) {
        $orders = $this->user->users_getOrderHistory($id);
        $data = [];
        
        if ($orders && mysqli_num_rows($orders) > 0) {
            while ($row = mysqli_fetch_assoc($orders)) {
                // Format lại dữ liệu cho đẹp trước khi gửi về client
                $row['created_at_format'] = date('d/m/Y H:i', strtotime($row['created_at']));
                $row['total_money_format'] = number_format($row['total_money'], 0, ',', '.') . ' VNĐ';
                $data[] = $row;
            }
        }
        
        // Trả về dữ liệu dạng JSON
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    // Thêm vào class users trong file users.php
    function get_order_details($order_id) {
        // Gọi model orders_m để lấy dữ liệu
        $orderModel = $this->model('orders_m');
        
        // 1. Lấy thông tin chung của đơn hàng
        $orderInfo = $orderModel->order_getById($order_id);
        
        // 2. Lấy danh sách sản phẩm trong đơn hàng
        $itemsResult = $orderModel->orderItems_getByOrderId($order_id);
        $items = [];
        if ($itemsResult && mysqli_num_rows($itemsResult) > 0) {
            while ($row = mysqli_fetch_assoc($itemsResult)) {
                $items[] = $row;
            }
        }
        
        // 3. Trả về JSON
        $response = [
            'info' => $orderInfo,
            'items' => $items
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    // Thêm hàm này vào file users.php
    function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];

    

            // 2. Gọi Model để thêm người dùng
            // Truyền null cho các trường chưa có (google_id, avatar, địa chỉ...)
            $kq = $this->user->users_insert_default(
                $full_name, $email, $phone, $password,
                null, null, null, null, null, null
            );

            // 3. Xử lý thông báo trả về
            if (session_status() === PHP_SESSION_NONE) session_start();

            if ($kq === true) {
                $_SESSION['status_msg'] = "success";
            } elseif ($kq === "EMAIL_EXISTED") {
                $_SESSION['status_msg'] = "email_existed";
            } elseif ($kq === "PHONE_EXISTED") {
                $_SESSION['status_msg'] = "phone_existed";
            } else {
                $_SESSION['status_msg'] = "error";
            }

            // 4. Quay lại trang danh sách
            header("Location: /web_qlsp/users");
            exit();
        }
    }

     function thongBao($kq){
        if (session_status() === PHP_SESSION_NONE) session_start();
                 if ($kq) {
                        $_SESSION['status_msg'] = "success";
                    } else {
                        $_SESSION['status_msg'] = "error";
                    }

                    // 3. Quay trở lại trang danh sách
                    header("Location: /web_qlsp/users");
                    exit();
    }
    
    function delete($id) {
        $kq = $this->user->users_delete($id);
        $this->thongBao($kq);
    }
    public function export_excel() {
        // 1. Lấy dữ liệu từ Model
        // Giả sử Model của bạn có hàm lấy tất cả khách hàng
        $users = $this->user->users_selectAll(); 

        if (mysqli_num_rows($users) > 0) {
            // 2. Khởi tạo PHPExcel (hoặc PhpSpreadsheet)
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $sheet = $objPHPExcel->getActiveSheet();

            // 3. Đặt tiêu đề cho các cột (Dựa theo ảnh CSDL bạn gửi)
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'HỌ VÀ TÊN');
            $sheet->setCellValue('C1', 'EMAIL');
            $sheet->setCellValue('D1', 'SỐ ĐIỆN THOẠI');
            $sheet->setCellValue('E1', 'ĐIỂM TÍCH LŨY');
            $sheet->setCellValue('F1', 'ĐỊA CHỈ CHI TIẾT');
            $sheet->setCellValue('G1', 'NGÀY THAM GIA');

            // Định dạng tiêu đề in đậm
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            // 4. Đổ dữ liệu từ CSDL vào file
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

            // Tự động căn chỉnh độ rộng cột
            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // 5. Cấu hình Header để tải file về máy
            $filename = "Danh_sach_khach_hang_" . date('Ymd_His') . ".xlsx";
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
