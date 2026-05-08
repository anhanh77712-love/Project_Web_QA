<?php
class import extends controllers {
    private $sp;
    private $cate;

    function __construct() {
                parent::__construct();

        // Khởi tạo model và gán vào thuộc tính private
        $this->sp = $this->model('product_m');
        $this->cate = $this->model('categories_m');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Load giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'import_v'
        ]);
    }

    // Thiết lập Header cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // Hàm tạo Slug (Giữ nguyên)
    private function create_slug($string) {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#', '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#', '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#', '#(ỳ|ý|ỵ|ỷ|ỹ)#', '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#', '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#', '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#', '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#', '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#', '#(Đ)#',
            '/[^a-zA-Z0-9\-\_]/'
        );
        $replace = array('a', 'e', 'i', 'o', 'u', 'y', 'd', 'A', 'E', 'I', 'O', 'U', 'Y', 'D', '-');
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower(trim($string, '-'));
        return $string;
    }

    // 2. API: Nhập Sản phẩm
    function api_upload_products() {
        $this->setApiHeader();
        
        if (!isset($_FILES['txtfile']) || empty($_FILES['txtfile']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn file Excel!']); exit;
        }

        $file = $_FILES['txtfile']['tmp_name'];

        try {
            require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objExcel = $objReader->load($file);
            $sheet = $objExcel->getSheet(0);
            $sheetData = $sheet->toArray(null, true, true, true);

            $countSuccess = 0;

            for ($i = 2; $i <= count($sheetData); $i++) {
                $name = $sheetData[$i]["A"] ?? '';
                $price = $sheetData[$i]["B"] ?? '';
                $cat_id = $sheetData[$i]["C"] ?? '';
                $description = $sheetData[$i]["D"] ?? '';

                if (empty($name) || empty($price)) continue;

                $slug = $this->create_slug($name);
                
                $kq = $this->sp->products_insert($name, $slug, $price, $cat_id, '', $description, '');
                
                if ($kq) {
                    $countSuccess++;
                } else {
                    $error = mysqli_error($this->sp->con);
                    // Dừng và báo lỗi dạng JSON thay vì die()
                    echo json_encode(['success' => false, 'message' => "Lỗi SQL tại dòng $i: $error"]); exit;
                }
            }

            echo json_encode(['success' => true, 'message' => "Đã nhập thành công $countSuccess sản phẩm."]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()]); exit;
        }
    }

    // 3. API: Nhập Danh mục
    function api_upload_categories() {
        $this->setApiHeader();

        if (!isset($_FILES['txtfile']) || empty($_FILES['txtfile']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn file Excel!']); exit;
        }

        $file = $_FILES['txtfile']['tmp_name'];

        try {
            require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objExcel = $objReader->load($file);
            $sheetData = $objExcel->getActiveSheet()->toArray(null, true, true, true);

            $successCount = 0;
            for ($i = 2; $i <= count($sheetData); $i++) {
                $name = trim($sheetData[$i]["A"] ?? ''); 
                if (empty($name)) continue; 

                $slug = !empty($sheetData[$i]["B"]) ? trim($sheetData[$i]["B"]) : $this->create_slug($name);
                $thumbnail = isset($sheetData[$i]["C"]) ? trim($sheetData[$i]["C"]) : "";

                $check = $this->cate->checkDuplicate($slug); 
                if ($check == 0) {
                    $this->cate->categories_insert($name, $slug, $thumbnail);
                    $successCount++;
                }
            }
            
            echo json_encode(['success' => true, 'message' => "Đã thêm $successCount danh mục mới."]);
            exit;

        } catch (Exception $e) {
             echo json_encode(['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()]); exit;
        }
    }
}
?>