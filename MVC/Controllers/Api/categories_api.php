<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

class categories_api extends controllers {
    private $categories_model;

    function __construct() {
        $this->categories_model = $this->model('categories_m');
    }

    // ==========================================
    // CÁC HÀM TRẢ VỀ JSON (Dùng để lấy dữ liệu)
    // ==========================================

    // API GET: /web_qlsp/api/categories_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->categories_model->categories_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có danh mục nào']);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ FORM & CHUYỂN TRANG (REDIRECT)
    // ==========================================

    function thongBao($kq) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }

        // Quay trở lại trang danh sách
        header("Location: /web_qlsp/categories_list");
        exit();
    }

    // Thêm danh mục mới
    function add() {
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $slug = isset($_POST['slug']) ? $_POST['slug'] : '';

        // Validate
        if(empty($name)){
            echo "<script>alert('Tên danh mục không được để trống'); window.location.href='/web_qlsp/categories_list';</script>";
            return;
        } else {
            $thumbnail = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/categories/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_name = time() . '_' . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                    $thumbnail = $file_name;
                }
            }
            // Insert DB
            $kq = $this->categories_model->categories_insert($name, $slug, $thumbnail);
            $this->thongBao($kq);
        }
    }

    // Cập nhật danh mục
    function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
        $old_image = isset($_POST['old_image']) ? $_POST['old_image'] : '';

        // Validate
        if(empty($name) || $id <= 0){
            echo "<script>alert('Dữ liệu không hợp lệ'); window.location.href='/web_qlsp/categories_list';</script>";
            return;
        } else {
            // Xử lý upload ảnh mới
            $thumbnail = $old_image; // Giữ ảnh cũ
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/categories/";
                $file_name = time() . '_' . basename($_FILES["image"]["name"]);
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                    $thumbnail = $file_name;
                    // Xóa ảnh cũ nếu có
                    if(!empty($old_image) && file_exists($target_dir . $old_image)){
                        unlink($target_dir . $old_image);
                    }
                }
            }

            // Update DB
            $kq = $this->categories_model->categories_update($id, $name, $slug, $thumbnail);
            $this->thongBao($kq);
        }
    }

    // Xóa danh mục
    function delete($id = 0) {
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id >= 0) {
            $kq = $this->categories_model->categories_delete($id);
            $this->thongBao($kq);
        } else {
            $this->thongBao(false);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ EXCEL (IMPORT/EXPORT)
    // ==========================================

    // Xuất Excel
    function export_excel() {
        // Khởi tạo PHPExcel (Đã được nạp từ bridge.php)
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Danh_Muc_San_Pham');

        // 1. Tạo tiêu đề cột
        $rowCount = 1;
        $sheet->setCellValue('A'.$rowCount, 'ID');
        $sheet->setCellValue('B'.$rowCount, 'TÊN DANH MỤC');
        $sheet->setCellValue('C'.$rowCount, 'SLUG');
        $sheet->setCellValue('D'.$rowCount, 'HÌNH ẢNH');
        
        // Định dạng in đậm tiêu đề
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // 2. Lấy dữ liệu
        $name = isset($_POST['txtSearch']) ? $_POST['txtSearch'] : ''; 
        $data = $this->categories_model->categories_select($name); 

        if($data){
            while($row = mysqli_fetch_array($data)){
                $rowCount++;
                $sheet->setCellValue('A'.$rowCount, $row['id']);
                $sheet->setCellValue('B'.$rowCount, $row['name']);
                $sheet->setCellValue('C'.$rowCount, $row['slug']);
                $sheet->setCellValue('D'.$rowCount, $row['thumbnail']);
            }
        }

        // Tự động giãn chiều rộng cột
        foreach(range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 3. Xuất file
        $filename = "Danh_Muc_" . time() . ".xlsx";
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    // Nhập Excel
    public function import_excel() {
        $file = isset($_FILES['import_file']['tmp_name']) ? $_FILES['import_file']['tmp_name'] : '';

        if (empty($file)) {
            echo "<script>alert('Vui lòng chọn file!'); window.history.back();</script>";
            return;
        }

        try {
            require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objExcel  = $objReader->load($file);
            $sheet     = $objExcel->getSheet(0);
            $sheetData = $sheet->toArray(null, true, true, true);

            $uploadFolder = ''; 
            $countSuccess = 0;

            // Cấu trúc Excel: Cột A = Tên, Cột B = Link Ảnh/Tên Ảnh
            for ($i = 2; $i <= count($sheetData); $i++) {
                $name = trim($sheetData[$i]["A"]);
                if (empty($name)) {
                    continue; 
                }

                $slug = $this->create_slug($name);
                $rawImg = trim($sheetData[$i]["B"]);
                $thumbnail = '';

                if (!empty($rawImg)) {
                    if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                        $savedPath = $this->download_image_from_url($rawImg, $uploadFolder);
                        if ($savedPath) {
                            $thumbnail = $savedPath;
                        }
                    } else {
                        $thumbnail = $uploadFolder . $rawImg;
                    }
                } else {
                    $thumbnail = 'Public/Picture/no-image.jpg';
                }

                $insertResult = $this->categories_model->categories_insert($name, $slug, $thumbnail);
                if ($insertResult) {
                    $countSuccess++;
                }
            }

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Thành công!',
                        html: 'Đã nhập được <b>$countSuccess</b> danh mục mới.',
                        icon: 'success'
                    }).then(() => { 
                        window.location.href = '/web_qlsp/categories_list'; 
                    });
                };
            </script>";

        } catch (Exception $e) {
            echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "'); window.history.back();</script>";
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
            $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
            
            if (!$ext || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
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
    function search() {
        // 1. Xử lý khi nhấn nút Tìm kiếm
        if(isset($_POST['btnTimkiem'])){
            $search = $_POST['txtSearch'];
            
            // Gọi lại View để hiển thị danh sách đã lọc (Giữ nguyên logic cũ)
            $this->view('Master', [
                'Page' => 'categories_list_v',
                'categories_list' => $this->categories_model->categories_select($search),
                'search' => $search
            ]);
        }
        // 2. Xử lý khi nhấn nút Xuất Excel từ cùng một form
        else if(isset($_POST['btnXuat'])){
            // Gọi thẳng tới hàm export_excel() đã được định nghĩa ở dưới
            $this->export_excel();
        }
    }
}
?>