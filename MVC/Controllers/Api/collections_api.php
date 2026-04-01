<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

class collections_api extends controllers {
    private $collections_model;

    public function __construct() {
        // Khởi tạo model và gán vào biến $collections_model
        $this->collections_model = $this->model("collections_m");
    }

    // ==========================================
    // CÁC HÀM TRẢ VỀ JSON (Dùng để lấy dữ liệu)
    // ==========================================

    // API GET: /web_qlsp/api/collections_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->collections_model->collections_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có bộ sưu tập nào']);
        }
    }

    // ==========================================
    // CÁC HÀM HIỂN THỊ & XỬ LÝ FORM (REDIRECT)
    // ==========================================

    public function Get_data() {
        $this->view('Master', [
            'Page'             => 'collections_v',
            'collections_list' => $this->collections_model->collections_selectAll(),
            'collec_model'     => $this->collections_model // Truyền model sang View
        ]);
    }

    public function thongBao($kq) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }

        // Quay trở lại trang danh sách
        header("Location: /web_qlsp/collections");
        exit();
    }

    public function add() {
        if (isset($_POST['add_collection'])) {
            $name = $_POST['name'];
            $slug = $_POST['slug'];

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên bộ sưu tập không được để trống'); window.location.href='/web_qlsp/collections';</script>";
                return;
            } else {
                $thumbnail = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                    }
                }
                // Insert DB
                $kq = $this->collections_model->collections_insert($name, $slug, $thumbnail);
                $this->thongBao($kq);
            }
        }
    }

    public function update() {
        if (isset($_POST['edit_collection'])) {
            $id        = $_POST['id'];
            $name      = $_POST['name'];
            $slug      = $_POST['slug'];
            $old_image = $_POST['old_image'];

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên bộ sưu tập không được để trống'); window.location.href='/web_qlsp/collections';</script>";
                return;
            } else {
                // Xử lý upload ảnh mới
                $thumbnail = $old_image; // Giữ ảnh cũ
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
                    $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                        // Xóa ảnh cũ vật lý nếu có
                        if (!empty($old_image) && file_exists($target_dir . $old_image)) {
                            unlink($target_dir . $old_image);
                        }
                    }
                }
                // Update DB
                $kq = $this->collections_model->collections_update($id, $name, $slug, $thumbnail);
                $this->thongBao($kq);
            }
        }
    }

    public function delete($id = 0) {
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id > 0) {
            $kq = $this->collections_model->collections_delete($id);
            $this->thongBao($kq);
        } else {
            $this->thongBao(false);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ TÌM KIẾM & EXCEL
    // ==========================================

 // ==========================================
    // CÁC HÀM XỬ LÝ TÌM KIẾM & EXCEL
    // ==========================================

    public function search() {
        if (isset($_POST['btnTimkiem'])) {
            $search = $_POST['txtSearch'];
            $this->view('Master', [
                'Page'             => 'collections_v',
                'collections_list' => $this->collections_model->collections_select($search),
                'search'           => $search,
            ]);
        } else if (isset($_POST['btnXuat'])) {
            // 1. Đảm bảo thư viện PHPExcel được gọi (phòng trường hợp bridge.php chưa nạp)
            $phpExcelPath = $_SERVER['DOCUMENT_ROOT'] . '/web_qlsp/Public/Classes/PHPExcel.php';
            if (file_exists($phpExcelPath) && !class_exists('PHPExcel')) {
                require_once $phpExcelPath;
            }

            $objExcel = new PHPExcel();
            $objExcel->setActiveSheetIndex(0);
            $sheet = $objExcel->getActiveSheet()->setTitle('Bo_Suu_Tap');

            // Tạo tiêu đề
            $rowCount = 1;
            $sheet->setCellValue('A' . $rowCount, 'ID');
            $sheet->setCellValue('B' . $rowCount, 'TÊN BỘ SƯU TẬP');
            $sheet->setCellValue('C' . $rowCount, 'SLUG');
            $sheet->setCellValue('D' . $rowCount, 'HÌNH ẢNH');
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);

            // Lấy dữ liệu
            $name = $_POST['txtSearch'] ?? '';
            $data = $this->collections_model->collections_select($name);

            if ($data) {
                while ($row = mysqli_fetch_array($data)) {
                    $rowCount++;
                    $sheet->setCellValue('A' . $rowCount, $row['id']);
                    $sheet->setCellValue('B' . $rowCount, $row['name']);
                    $sheet->setCellValue('C' . $rowCount, $row['slug']);
                    $sheet->setCellValue('D' . $rowCount, $row['thumbnail']);
                }
            }

            // ... (Đoạn tạo dữ liệu Excel giữ nguyên)

            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = "Bo_Suu_Tap_" . time() . ".xlsx";
            
            // Xóa SẠCH toàn bộ đệm đầu ra (Output Buffering) nhiều lần nếu cần
            if (ob_get_length() > 0) {
                ob_clean();
            }

            // TẮT TOÀN BỘ thông báo lỗi trong lúc xuất file (tránh warning làm hỏng file)
            error_reporting(0); 

            // Cập nhật các Header chuẩn nhất để ép trình duyệt hiểu đây là file Excel 2007+
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0'); 
            header('Pragma: public'); // Cần cho một số phiên bản IE cũ (nếu có)
            
            // Lệnh này ép PHP ngắt mọi thứ khác và chỉ trả về file
            $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
            
            // Bắt buộc exit để không có đoạn HTML/PHP nào chạy thêm ở phía sau
            exit;
        }
    }

    public function importExcelCollections() {
        if (isset($_POST['btn_import_collection'])) {
            // Kiểm tra xem file có được upload không
            $file = isset($_FILES['import_file_collection']['tmp_name']) ? $_FILES['import_file_collection']['tmp_name'] : '';

            if (empty($file)) {
                echo "<script>alert('Vui lòng chọn file!'); window.history.back();</script>";
                return;
            }

            try {
                // 3. FIX ĐƯỜNG DẪN THƯ VIỆN BẰNG ĐƯỜNG DẪN TUYỆT ĐỐI
                $ioFactoryPath = $_SERVER['DOCUMENT_ROOT'] . '/web_qlsp/Public/Classes/PHPExcel/IOFactory.php';
                if (!file_exists($ioFactoryPath)) {
                    die("<script>alert('Lỗi: Không tìm thấy thư viện tại $ioFactoryPath'); window.history.back();</script>");
                }
                require_once $ioFactoryPath;

                $objReader = PHPExcel_IOFactory::createReaderForFile($file);
                $objExcel  = $objReader->load($file);
                $sheet     = $objExcel->getSheet(0);
                $sheetData = $sheet->toArray(null, true, true, true);

                $projectFolder = '/web_qlsp'; 
                $subFolder     = '/Public/Picture/collections/';
                $absPath = $_SERVER['DOCUMENT_ROOT'] . $projectFolder . $subFolder;

                if (!file_exists($absPath)) {
                    mkdir($absPath, 0777, true);
                }

                $countSuccess = 0;

                for ($i = 2; $i <= count($sheetData); $i++) {
                    // 4. KIỂM TRA TỒN TẠI (ISSET) TRƯỚC KHI ĐỌC CỘT (Phòng lỗi dòng trống trong Excel)
                    $name = isset($sheetData[$i]["A"]) ? trim($sheetData[$i]["A"]) : '';
                    if (empty($name)) continue;

                    $slug = $this->create_slug($name);
                    $rawImg = isset($sheetData[$i]["B"]) ? trim($sheetData[$i]["B"]) : '';
                    $thumbnail = 'no-image.jpg'; 

                    if (!empty($rawImg)) {
                        if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                            $savedPath = $this->download_image_from_url($rawImg, $absPath);
                            if ($savedPath) {
                                $thumbnail = basename($savedPath); 
                            }
                        } else {
                            if (file_exists($absPath . $rawImg)) {
                                $thumbnail = $rawImg; 
                            } else {
                                $thumbnail = 'no-image.jpg';
                            }
                        }
                    }

                    $insertResult = $this->collections_model->collections_insert($name, $slug, $thumbnail);
                    if ($insertResult) {
                        $countSuccess++;
                    }
                }

                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            title: 'Hoàn tất!',
                            html: 'Đã nhập thành công <b>$countSuccess</b> bộ sưu tập.',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = '/web_qlsp/collections';
                        });
                    };
                </script>";

            } catch (Exception $e) {
                // Thoát các ký tự đặc biệt để không làm gãy mã Javascript
                $errorMsg = addslashes($e->getMessage());
                echo "<script>alert('Lỗi hệ thống: $errorMsg'); window.history.back();</script>";
            }
        }
    }

    // ==========================================
    // CÁC HÀM HỖ TRỢ (HELPERS)
    // ==========================================

    private function create_slug($string) {
        $search = [
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            "/[^a-zA-Z0-9\-\_]/",
        ];
        $replace = [
            'a', 'e', 'i', 'o', 'u', 'y', 'd',
            'A', 'E', 'I', 'O', 'U', 'Y', 'D',
            '-',
        ];
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        return strtolower($string);
    }

    private function download_image_from_url($url, $saveFolder) {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        try {
            $imageContent = @file_get_contents($url); 
            if ($imageContent === false) {
                return '';
            }

            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            $ext      = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

            if (! $ext || ! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $ext = 'jpg';
            }

            $newFileName = 'cat_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $savePath = $saveFolder . $newFileName;

            file_put_contents($savePath, $imageContent);
            return $savePath; 
        } catch (Exception $e) {
            return '';
        }
    }
}
?>