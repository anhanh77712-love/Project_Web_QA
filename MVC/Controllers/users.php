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
