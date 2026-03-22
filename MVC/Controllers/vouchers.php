<?php
class vouchers extends controllers
{
    private $vouchers;
    public function __construct()
    {
        $this->vouchers = $this->model('vouchers_m');
    }
    public function Get_data()
    {
        $this->view('Master', [
            'Page'          => 'vouchers_v',
            'vouchers_list' => $this->vouchers->vouchers_selectAll(),
        ]);
    }
    function reset(){
        $this->view('Master', [
            'Page'          => 'vouchers_v',
            'vouchers_list' => $this->vouchers->vouchers_selectAll(),
        ]);
    }
    function search() {
    
    if (isset($_POST['btnTimkiem'])) {
        $search = $_POST['txtSearch'];
        $vouchers_result = $this->vouchers->vouchers_select_search($search);
        $this->view('Master', [
            'Page' => 'vouchers_v',
            'vouchers_list' => $vouchers_result,
            'search' => $search
        ]);
    }

   
    else if (isset($_POST['btnXuatExcel'])) {
        
        if (!class_exists('PHPExcel')) {
             echo "<script>alert('Lỗi: Thư viện PHPExcel chưa được nạp.'); window.history.back();</script>";
             return;
        }

        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Danh_Sach_Vouchers');

        // 1. Tiêu đề cột
        $rowCount = 1;
        $columns = [
            'A'=>'ID', 'B'=>'Mã Code', 'C'=>'Mô tả', 'D'=>'Loại giảm',
            'E'=>'Giá trị', 'F'=>'Giảm tối đa', 'G'=>'Đơn tối thiểu',
            'H'=>'Tổng giới hạn', 'I'=>'Đã dùng', 
            'J'=>'Ngày bắt đầu', 'K'=>'Ngày kết thúc', 'L'=>'Trạng thái'
        ];

        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . $rowCount, $title);
            $sheet->getStyle($col . $rowCount)->getFont()->setBold(true);
            $sheet->getStyle($col . $rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $rowCount)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE0B2');
        }

        $keyword = $_POST['txtSearch'] ?? '';
        $data = $this->vouchers->vouchers_select_search($keyword);

        if ($data && mysqli_num_rows($data) > 0) {
            while ($row = mysqli_fetch_array($data)) {
                $rowCount++;
                
                // Đổ dữ liệu cơ bản
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
                
                $text_status = '';
                $color_status = '000000'; // Mặc định màu đen

               
                if ($row['status'] == 0) {
                    $text_status = 'Đang ẩn';
                    $color_status = '808080'; // Màu xám
                } 
              
                elseif ($row['used_count'] >= $row['usage_limit']) {
                    $text_status = 'Hết lượt';
                    $color_status = '000000'; // Màu đen
                } 
                
                elseif ($now > $end) {
                    $text_status = 'Đã kết thúc';
                    $color_status = 'FF0000'; // Màu đỏ
                } 
                
                elseif ($now < $start) {
                    $text_status = 'Sắp diễn ra';
                    $color_status = 'FFA500'; // Màu cam
                } 
               
                else {
                    $text_status = 'Đang chạy'; // Hoặc 'Đang hoạt động'
                    $color_status = '008000'; // Màu xanh lá
                }

                $sheet->setCellValue('L'.$rowCount, $text_status);
                
                // Tô màu chữ cho đẹp
                $sheet->getStyle('L'.$rowCount)->getFont()->getColor()->setARGB($color_status);
            }
        } else {
            $rowCount++;
            $sheet->setCellValue('A'.$rowCount, "Không tìm thấy dữ liệu.");
            $sheet->mergeCells('A'.$rowCount.':L'.$rowCount);
        }

        // Căn chỉnh độ rộng
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        
        // Tên file
        $filename = "Vouchers_" . date('d-m-Y') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
    public function thongBao($kq)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }

        header("Location: /web_qlsp/vouchers");
        exit();
    }

    public function add()
    {
        if (isset($_POST['btnAddVoucher'])) {
            // 1. Lấy dữ liệu từ Form
            $code        = $_POST['code'];
            $description = $_POST['description']; // Mô tả

            $discount_type  = $_POST['type']; // 'fixed' hoặc 'percent'
            $discount_value = $_POST['value'];

           
            $max_discount_amount = ! empty($_POST['max_discount']) ? $_POST['max_discount'] : 'NULL';
            $min_order_value     = ! empty($_POST['min_order']) ? $_POST['min_order'] : 0;

            $usage_limit = ! empty($_POST['usage_limit']) ? $_POST['usage_limit'] : 100; // Mặc định 100 nếu bỏ trống

            $start_date = $_POST['start_date'];
            $end_date   = $_POST['end_date'];

            $status = isset($_POST['status']) ? $_POST['status'] : 1;

            // 2. Validate dữ liệu (Kiểm tra lỗi)
            // Kiểm tra mã rỗng
            if (empty($code)) {
                echo "<script>alert('Mã voucher không được để trống'); window.history.back();</script>";
                return;
            }

            if (! empty($start_date) && ! empty($end_date) && $end_date < $start_date) {
                echo "<script>alert('Ngày kết thúc phải lớn hơn ngày bắt đầu'); window.history.back();</script>";
                return;
            }

            if ($discount_type == 'percent' && $discount_value > 100) {
                echo "<script>alert('Giảm giá phần trăm không được quá 100%'); window.history.back();</script>";
                return;
            }

            $kq = $this->vouchers->vouchers_insert(
                $code,
                $description,
                $discount_type,
                $discount_value,
                $max_discount_amount,
                $min_order_value,
                $usage_limit,
                $start_date,
                $end_date,
                $status
            );

            // 4. Thông báo kết quả
            $this->thongBao($kq);
        }
    }
    public function delete($id)
    {
        $kq = $this->vouchers->vouchers_delete($id);
        $this->thongBao($kq);
    }
    public function update()
    {
        if (isset($_POST['btnUpdateVoucher'])) {
                                                 // Lấy dữ liệu từ Form Modal
            $id                  = $_POST['id']; // ID lấy từ input hidden
            $usage_limit         = $_POST['usage_limit'];
            $start_date          = $_POST['start_date'];
            $end_date            = $_POST['end_date'];
            $discount_value      = $_POST['value'];        // name="value" trong form
            $min_order_value     = $_POST['min_order'];    // name="min_order" trong form
            $max_discount_amount = $_POST['max_discount']; // name="max_discount" trong form
            $status              = $_POST['status'];

            // Gọi hàm Model vừa viết ở trên
            $kq = $this->vouchers->vouchers_update(
                $id,
                $usage_limit,
                $start_date,
                $end_date,
                $discount_value,
                $min_order_value,
                $max_discount_amount,
                $status
            );

            // Thông báo và chuyển hướng
            $this->thongBao($kq);
        }
    }
   function import() {
    // Kiểm tra nút bấm và file
    if (isset($_POST['btnImport']) && isset($_FILES['fileImport'])) {
        $file = $_FILES['fileImport']['tmp_name'];

        // 1. Kiểm tra file rỗng
        if (empty($file)) {
            echo "<script>alert('Vui lòng chọn file Excel!'); window.history.back();</script>";
            return;
        }

        // 2. Kiểm tra thư viện PHPExcel
        if (!class_exists('PHPExcel')) {
             echo "<script>alert('Lỗi: Thư viện PHPExcel chưa được nạp. Hãy kiểm tra lại file bridge.php'); window.history.back();</script>";
             return;
        }

        try {
            // 3. Đọc file Excel
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objReader->setReadDataOnly(true);
            $objExcel = $objReader->load($file);
            // Lấy Sheet đầu tiên
            $sheetData = $objExcel->getActiveSheet()->toArray(null, true, true, true);

            $countSuccess = 0;
            $countFail = 0;
            $countDuplicate = 0;

            // 4. Duyệt dòng (Từ dòng 2 trở đi)
            for ($i = 2; $i <= count($sheetData); $i++) {
                
                // Lấy Mã Code ở cột B
                $code = trim($sheetData[$i]["B"]);

               
                if (empty($code) || $code == 'Mã Code' || $code == 'Mã code') {
                    continue; 
                }

                // --- LẤY DỮ LIỆU CÁC CỘT KHÁC ---
                $description    = $sheetData[$i]["C"];
                $type_raw       = $sheetData[$i]["D"]; // VD: "Theo %"
                $discount_value = $sheetData[$i]["E"];
                $max_raw        = $sheetData[$i]["F"]; 
                $min_order      = $sheetData[$i]["G"];
                $usage_limit    = $sheetData[$i]["H"];
                
                // Cột J, K là ngày tháng
                $start_raw      = $sheetData[$i]["J"];
                $end_raw        = $sheetData[$i]["K"];
                
                $status_raw     = $sheetData[$i]["L"]; // VD: "Hoạt động"

                // --- XỬ LÝ DỮ LIỆU (MAPPING) ---

                // 1. Loại giảm
                $discount_type = (trim($type_raw) == 'Theo %') ? 'percent' : 'fixed';

                if (empty($max_raw)) {
                    $max_discount_amount = "NULL"; 
                } else {
                  
                    $max_discount_amount = str_replace(',', '', $max_raw);
                }

                // 3. Xử lý Min Order & Value (Loại bỏ dấu phẩy)
                $min_order_value = empty($min_order) ? 0 : str_replace(',', '', $min_order);
                $discount_value  = str_replace(',', '', $discount_value);
                $usage_limit     = str_replace(',', '', $usage_limit);

                // 4. Xử lý Ngày tháng (Quan trọng)
               
                if (is_numeric($start_raw)) {
                    $start_date = date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($start_raw));
                } else {
                    // Nếu trả về Text (2025-01-01...) -> Dùng strtotime
                    $start_date = date('Y-m-d H:i:s', strtotime($start_raw));
                }

                if (is_numeric($end_raw)) {
                    $end_date = date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($end_raw));
                } else {
                    $end_date = date('Y-m-d H:i:s', strtotime($end_raw));
                }

                // 5. Trạng thái
                $status = (trim($status_raw) == 'Hoạt động') ? 1 : 0;

                
                $kq = $this->vouchers->vouchers_insert(
                    $code,
                    $description,
                    $discount_type,
                    $discount_value,
                    $max_discount_amount,
                    $min_order_value,
                    $usage_limit,
                    $start_date,
                    $end_date,
                    $status
                );

                if ($kq) {
                    $countSuccess++;
                } else {
                    $countFail++;
                }
            }

            // 5. HIỂN THỊ KẾT QUẢ
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Hoàn tất nhập dữ liệu!',
                        html: '<div class=\"text-start\">' +
                              '<p class=\"text-success mb-1\">✅ Thành công: <b>$countSuccess</b> dòng</p>' +
                              '<p class=\"text-danger mb-0\">❌ Thất bại (hoặc trùng mã): <b>$countFail</b> dòng</p>' +
                              '</div>',
                        icon: 'info',
                        confirmButtonText: 'Đã hiểu',
                        confirmButtonColor: '#3085d6'
                    }).then((result) => {
                        window.location.href = '/web_qlsp/vouchers';
                    });
                };
            </script>";

        } catch (Exception $e) {
            echo "<script>alert('Lỗi xử lý file Excel: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }
}
}
