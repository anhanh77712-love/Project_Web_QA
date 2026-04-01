<?php
class warehouse_api extends controllers
{
    private $warehouse_model;

    public function __construct()
    {
        // Khởi tạo model warehouse_m dùng chung cho tất cả chức năng
        $this->warehouse_model = $this->model('warehouse_m');
    }

    // ============================================================
    // 1. QUẢN LÝ DANH SÁCH & TỔNG QUAN (VIEW)
    // ============================================================

    public function Get_data()
    {
        // Lấy các tham số lọc từ URL
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        // Truy vấn dữ liệu từ model
        $items = $this->warehouse_model->warehouse_selectItems($q, $category, $status);
        $categories = $this->warehouse_model->categories_selectAll();

        // Tính toán các thông số tổng quan kho hàng
        $sum_stock = 0; 
        $low_stock_count = 0; 
        $out_of_stock_count = 0; 
        $stock_value = 0;

        foreach ($items as $it) {
            $stock = intval($it['stock_quantity'] ?? 0);
            $threshold = intval($it['threshold'] ?? 5);
            $sum_stock += $stock;
            
            if ($stock == 0) { 
                $out_of_stock_count++; 
            } elseif ($stock <= $threshold) { 
                $low_stock_count++; 
            }
            
            // Tính giá trị kho hàng = giá nhập * số lượng tồn
            $stock_value += (float)($it['cost_price'] ?? 0) * $stock;
        }

        // Trả về view quản lý kho
        $this->view('Master', [
            'Page' => 'warehouse_v',
            'warehouse_items' => $items,
            'categories' => $categories,
            'q' => $q,
            'sum_stock' => $sum_stock,
            'low_stock_count' => $low_stock_count,
            'out_of_stock_count' => $out_of_stock_count,
            'stock_value' => $stock_value
        ]);
    }

    // ============================================================
    // 2. ĐIỀU CHỈNH KHO (AJAX / MODAL)
    // ============================================================

    public function adjust_stock()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
            return;
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? intval($_POST['variant_id']) : null;
        $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0; // Số lượng tăng (+) hoặc giảm (-)

        // Kiểm tra dữ liệu đầu vào
        if ($product_id <= 0 || $delta == 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu điều chỉnh']);
            return;
        }

        if ($variant_id === null) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn biến thể để điều chỉnh']);
            return;
        }

        // Gọi model cập nhật số lượng tồn
        $ok = $this->warehouse_model->warehouse_adjustStock($product_id, $variant_id, $delta);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Thành công' : 'Không thể cập nhật']);
    }

    // ============================================================
    // 3. XỬ LÝ EXCEL (IMPORT & EXPORT)
    // ============================================================

    public function import()
    {
        // Nếu là GET: Hiển thị trang upload, nếu là POST: Xử lý file
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('Master', ['Page' => 'warehouse_import_v']);
            return;
        }

        if (empty($_FILES['file']['tmp_name'])) {
            $this->view('Master', ['Page' => 'warehouse_import_v', 'error' => 'Vui lòng chọn tệp Excel']);
            return;
        }

        try {
            require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
            $objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
            $sheet = $objPHPExcel->getActiveSheet();
            
            // Logic mapping SKU và số lượng mới từ file Excel
            $highestRow = $sheet->getHighestRow();
            $colSku = 'A'; // Giả định cột A là SKU
            $colNewStock = 'B'; // Giả định cột B là tồn kho mới

            $results = ['updated' => 0, 'failed' => 0, 'details' => []];
            for ($row = 2; $row <= $highestRow; $row++) {
                $sku = trim((string)$sheet->getCell($colSku . $row)->getValue());
                $newStock = intval($sheet->getCell($colNewStock . $row)->getValue());

                if (empty($sku)) continue;

                // Tìm biến thể theo SKU và thực hiện điều chỉnh
                $variant = $this->warehouse_model->variant_by_sku($sku);
                if ($variant) {
                    $delta = $newStock - intval($variant['stock'] ?? 0);
                    if ($this->warehouse_model->warehouse_adjustStock($variant['product_id'], $variant['id'], $delta)) {
                        $results['updated']++;
                    } else { $results['failed']++; }
                } else { $results['failed']++; }
            }

            $this->view('Master', ['Page' => 'warehouse_import_v', 'results' => $results]);
        } catch (Exception $e) {
            $this->view('Master', ['Page' => 'warehouse_import_v', 'error' => $e->getMessage()]);
        }
    }

    public function export_excel()
    {
        // Xuất danh sách tồn kho hiện tại ra file Excel
        $items = $this->warehouse_model->warehouse_selectItems('', '', '');
        if (!$items) return;

        require_once __DIR__ . '/../../Public/Classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        
        // Thiết lập Header
        $headers = ['Sản phẩm', 'Biến thể', 'SKU', 'Danh mục', 'Tồn kho', 'Sẵn có'];
        $cols = range('A', 'F');
        foreach ($cols as $i => $col) {
            $sheet->setCellValue($col.'1', $headers[$i]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Đổ dữ liệu
        $rowIdx = 2;
        foreach ($items as $it) {
            $sheet->setCellValue('A'.$rowIdx, $it['product_name']);
            $sheet->setCellValue('C'.$rowIdx, $it['sku']);
            $sheet->setCellValue('E'.$rowIdx, $it['stock_quantity']);
            $rowIdx++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Kho_hang_'.date('Ymd').'.xlsx"');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }

    // ============================================================
    // 4. API ENDPOINT (JSON)
    // ============================================================

    // API GET: /web_qlsp/warehouse_api/get_all
    public function get_all()
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        
        $result = $this->warehouse_model->warehouse_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có dữ liệu kho']);
        }
    }
}