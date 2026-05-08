<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

class collections extends controllers {
    private $collec;

    public function __construct() {
                parent::__construct();

        $this->collec = $this->model("collections_m");
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Load giao diện rỗng)
    public function Get_data() {
        $this->view('Master', [
            'Page' => 'collections_v'
        ]);
    }

    // Thiết lập Header cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 2. LUỒNG API DÀNH CHO JAVASCRIPT (Lấy dữ liệu + Tìm kiếm + Đếm SP)
    public function api_get_data() {
        $this->setApiHeader();
        
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        // Nếu có từ khóa thì tìm kiếm, không thì lấy tất cả
        if ($search !== '') {
            $result = $this->collec->collections_select($search);
        } else {
            $result = $this->collec->collections_selectAll();
        }

        $data = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Gọi model để đếm số lượng sản phẩm trong bộ sưu tập này
                $row['product_count'] = $this->collec->countProductsInCollection($row['id']);
                $data[] = $row;
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // API: Thêm Bộ sưu tập
    public function add() {
        $this->setApiHeader();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Tên bộ sưu tập không được để trống']); exit;
        }

        $thumbnail = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                $thumbnail = $file_name;
            }
        }

        $kq = $this->collec->collections_insert($name, $slug, $thumbnail);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Thêm thành công' : 'Lỗi thêm dữ liệu']);
        exit;
    }

    // API: Sửa Bộ sưu tập
    public function update() {
        $this->setApiHeader();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $id        = $_POST['id'];
        $name      = $_POST['name'];
        $slug      = $_POST['slug'];
        $old_image = $_POST['old_image'] ?? '';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Tên bộ sưu tập không được để trống']); exit;
        }

        $thumbnail = $old_image; 
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
            $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                $thumbnail = $file_name;
            }
        }

        $kq = $this->collec->collections_update($id, $name, $slug, $thumbnail);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Cập nhật thành công' : 'Lỗi cập nhật']);
        exit;
    }

    // API: Xóa Bộ sưu tập
    public function delete($id) {
        $this->setApiHeader();
        $kq = $this->collec->collections_delete($id);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã xóa thành công' : 'Lỗi khi xóa']);
        exit;
    }

    // API: Xuất Excel (Tải trực tiếp)
    public function export() {
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Bo_Suu_Tap');

        $rowCount = 1;
        $sheet->setCellValue('A' . $rowCount, 'ID');
        $sheet->setCellValue('B' . $rowCount, 'TÊN BỘ SƯU TẬP');
        $sheet->setCellValue('C' . $rowCount, 'SLUG');
        $sheet->setCellValue('D' . $rowCount, 'HÌNH ẢNH');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $search = $_GET['q'] ?? '';
        if ($search !== '') {
            $data = $this->collec->collections_select($search);
        } else {
            $data = $this->collec->collections_selectAll();
        }

        if ($data) {
            while ($row = mysqli_fetch_array($data)) {
                $rowCount++;
                $sheet->setCellValue('A' . $rowCount, $row['id']);
                $sheet->setCellValue('B' . $rowCount, $row['name']);
                $sheet->setCellValue('C' . $rowCount, $row['slug']);
                $sheet->setCellValue('D' . $rowCount, $row['thumbnail']);
            }
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Bo_Suu_Tap_" . time() . ".xlsx";
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    // API: Nhập Excel
    public function importExcelCollections() {
        $this->setApiHeader();

        if (!isset($_FILES['import_file_collection']) || empty($_FILES['import_file_collection']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn file!']); exit;
        }

        $file = $_FILES['import_file_collection']['tmp_name'];

        try {
            require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objExcel  = $objReader->load($file);
            $sheet     = $objExcel->getSheet(0);
            $sheetData = $sheet->toArray(null, true, true, true);

            $absPath = $_SERVER['DOCUMENT_ROOT'] . '/web_qlsp/Public/Picture/collections/';
            if (!is_dir($absPath)) mkdir($absPath, 0777, true);

            $countSuccess = 0;

            for ($i = 2; $i <= count($sheetData); $i++) {
                $name = trim($sheetData[$i]["A"] ?? '');
                if (empty($name)) continue;

                $slug = $this->create_slug($name);
                $rawImg = trim($sheetData[$i]["B"] ?? '');
                $thumbnail = 'no-image.jpg';

                if (!empty($rawImg)) {
                    if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                        $savedPath = $this->download_image_from_url($rawImg, $absPath);
                        if ($savedPath) $thumbnail = basename($savedPath); 
                    } else {
                        if (file_exists($absPath . $rawImg)) {
                            $thumbnail = $rawImg; 
                        }
                    }
                }

                $insertResult = $this->collec->collections_insert($name, $slug, $thumbnail);
                if ($insertResult) $countSuccess++;
            }

            echo json_encode(['success' => true, 'message' => "Đã nhập thành công $countSuccess bộ sưu tập."]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            exit;
        }
    }

    // Các hàm tiện ích
    private function create_slug($string) {
        $search = ['#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#', '#(ì|í|ị|ỉ|ĩ)#', '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#', '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#', '#(ỳ|ý|ỵ|ỷ|ỹ)#', '#(đ)#', '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#', '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#', '#(Ì|Í|Ị|Ỉ|Ĩ)#', '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#', '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#', '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#', '#(Đ)#', "/[^a-zA-Z0-9\-\_]/"];
        $replace = ['a', 'e', 'i', 'o', 'u', 'y', 'd', 'A', 'E', 'I', 'O', 'U', 'Y', 'D', '-'];
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        return strtolower($string);
    }

    private function download_image_from_url($url, $saveFolder) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) return '';
        try {
            $imageContent = @file_get_contents($url); 
            if ($imageContent === false) return '';

            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'jpg';
            if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $ext = 'jpg';

            $newFileName = 'col_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $savePath = $saveFolder . $newFileName;
            file_put_contents($savePath, $imageContent);
            return $savePath; 
        } catch (Exception $e) {
            return '';
        }
    }
}
?>