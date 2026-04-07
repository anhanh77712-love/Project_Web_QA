<?php
class warehouse extends controllers {
    private $m;

    function __construct() {
        $this->m = $this->model('warehouse_m');
    }

    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'warehouse_v'
        ]);
    }

    // 2. API LẤY DỮ LIỆU KHO HÀNG & THỐNG KÊ
    function api_get_data() {
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
        $this->setApiHeader();

        try {
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $category = isset($_GET['category']) ? trim($_GET['category']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';

            // Lấy danh sách sản phẩm trong kho (Model này trả về mảng Array)
            $items = $this->m->warehouse_selectItems($q, $category, $status);
            
            // Lấy danh mục để đổ vào Select (Model này trả về mysqli_result)
            $categories = [];
            $cat_res = $this->m->categories_selectAll();
            if ($cat_res && is_object($cat_res) && mysqli_num_rows($cat_res) > 0) {
                while ($c = mysqli_fetch_assoc($cat_res)) {
                    $c['name'] = mb_convert_encoding($c['name'], 'UTF-8', 'UTF-8');
                    $categories[] = $c;
                }
            }

            // Tính toán thống kê
            $sum_stock = 0; $low_stock_count = 0; $out_of_stock_count = 0; $stock_value = 0;
            $processed_items = [];

            // SỬA Ở ĐÂY: Vì $items là mảng, ta dùng is_array và foreach
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $it) {
                    $stock = intval($it['stock_quantity'] ?? 0);
                    $threshold = intval($it['threshold'] ?? 5);
                    $reserved = intval($it['reserved_quantity'] ?? 0);
                    $available = max(0, $stock - $reserved);

                    $sum_stock += $stock;
                    if ($available == 0) { 
                        $out_of_stock_count++; 
                    } elseif ($available <= $threshold) { 
                        $low_stock_count++; 
                    }
                    
                    $stock_value += (float)($it['cost_price'] ?? 0) * $stock;

                    $it['available'] = $available;
                    $it['is_low'] = ($available <= $threshold && $available > 0);
                    
                    // CHỐNG LỖI JSON
                    foreach ($it as $key => $val) {
                        if (is_string($val)) {
                            $it[$key] = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
                        }
                    }

                    $processed_items[] = $it;
                }
            }

            // Đóng gói JSON
            $response = [
                'success' => true,
                'data' => [
                    'items' => $processed_items,
                    'categories' => $categories,
                    'summaries' => [
                        'sum_stock' => $sum_stock,
                        'low_stock_count' => $low_stock_count,
                        'out_of_stock_count' => $out_of_stock_count,
                        'stock_value' => $stock_value
                    ]
                ]
            ];

            // Render JSON
            $json_string = json_encode($response);
            
            if ($json_string === false) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Lỗi mã hóa JSON: ' . json_last_error_msg()
                ]);
                exit;
            }

            echo $json_string;
            exit;

        } catch (Throwable $e) { 
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi Code PHP: ' . $e->getMessage() . ' tại dòng ' . $e->getLine()
            ]);
            exit;
        }
    }

    // 3. API ĐIỀU CHỈNH TỒN KHO THỦ CÔNG
    function api_adjust_stock() {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']); exit;
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? intval($_POST['variant_id']) : null;
        $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

        if ($product_id <= 0 || $delta == 0 || $variant_id === null) {
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu hoặc chưa chọn biến thể']); exit;
        }

        $ok = $this->m->warehouse_adjustStock($product_id, $variant_id, $delta);
        
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Điều chỉnh tồn kho thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật tồn kho']);
        }
        exit;
    }

    // 4. API IMPORT EXCEL KHO HÀNG
    function api_import_excel() {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file']['tmp_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn tệp Excel để nhập']); exit;
        }

        $tmp = $_FILES['file']['tmp_name'];
        
        try {
            if (!class_exists('PHPExcel_IOFactory')) require_once "./MVC/Bridge.php";
            $objPHPExcel = PHPExcel_IOFactory::load($tmp);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Không thể đọc tệp Excel: ' . $e->getMessage()]); exit;
        }

        $sheet = $objPHPExcel->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Map header columns
        $headers = [];
        $headerRow = 1;
        foreach (range('A', $highestColumn) as $col) {
            $val = trim((string)$sheet->getCell($col . $headerRow)->getValue());
            if ($val !== '') { $headers[$col] = mb_strtolower($val); }
        }

        // Tìm cột SKU và Tồn kho mới
        $colSku = null; $colNewStock = null;
        foreach ($headers as $col => $name) {
            if ($name === 'sku') $colSku = $col;
            if ($name === 'newstock' || $name === 'new stock' || $name === 'stock' || $name === 'tồn kho mới') $colNewStock = $col;
        }

        if (!$colSku || !$colNewStock) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy cột bắt buộc: SKU và NewStock/Stock/Tồn kho mới trong file Excel.']); exit;
        }

        $results = ['updated' => 0, 'failed' => 0, 'details' => []];

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $sku = trim((string)$sheet->getCell($colSku . $row)->getValue());
            $newStockRaw = $sheet->getCell($colNewStock . $row)->getValue();
            if ($sku === '' && ($newStockRaw === '' || $newStockRaw === null)) { continue; }
            $newStock = intval($newStockRaw);

            $variant = $this->m->variant_by_sku($sku);
            if (!$variant) {
                $results['failed']++;
                $results['details'][] = ['sku' => $sku, 'status' => 'Không tìm thấy biến thể'];
                continue;
            }

            $current = intval($variant['stock'] ?? 0);
            $delta = $newStock - $current;

            if ($delta === 0) {
                $results['details'][] = ['sku' => $sku, 'status' => 'Không thay đổi'];
                continue;
            }

            $ok = $this->m->warehouse_adjustStock(intval($variant['product_id']), intval($variant['id']), $delta);
            if ($ok) {
                $results['updated']++;
                $results['details'][] = ['sku' => $sku, 'status' => 'Cập nhật', 'from' => $current, 'to' => $newStock];
            } else {
                $results['failed']++;
                $results['details'][] = ['sku' => $sku, 'status' => 'Lỗi cập nhật DB'];
            }
        }

        echo json_encode(['success' => true, 'message' => 'Xử lý file hoàn tất', 'results' => $results]);
        exit;
    }

    // 5. XUẤT EXCEL (Giữ nguyên)
    function export_excel() {
        if (!class_exists('PHPExcel')) require_once "./MVC/Bridge.php";

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $items = $this->m->warehouse_selectItems($q, $category, $status);

        if (!$items || count($items) === 0) {
            echo "<script>alert('Không có dữ liệu để xuất!'); window.history.back();</script>"; return;
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        $headers = ['A'=>'Sản phẩm', 'B'=>'Biến thể', 'C'=>'SKU', 'D'=>'Danh mục', 'E'=>'Tồn kho', 'F'=>'Đang giữ', 'G'=>'Sẵn có', 'H'=>'Ngưỡng cảnh báo', 'I'=>'Giá nhập'];
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col.'1', $title);
        }
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $row = 2;
        if($items && mysqli_num_rows($items) > 0) {
            while ($it = mysqli_fetch_assoc($items)) {
                $available = max(0, intval($it['stock_quantity'] ?? 0) - intval($it['reserved_quantity'] ?? 0));
                $variantLabel = trim(($it['color'] ?? '') . ((isset($it['size']) && $it['size']!=='') ? (' / ' . $it['size']) : ''));
                $sheet->setCellValue('A'.$row, $it['product_name'] ?? '');
                $sheet->setCellValue('B'.$row, $variantLabel);
                $sheet->setCellValue('C'.$row, $it['sku'] ?? '');
                $sheet->setCellValue('D'.$row, $it['category_name'] ?? '');
                $sheet->setCellValue('E'.$row, intval($it['stock_quantity'] ?? 0));
                $sheet->setCellValue('F'.$row, intval($it['reserved_quantity'] ?? 0));
                $sheet->setCellValue('G'.$row, $available);
                $sheet->setCellValue('H'.$row, intval($it['threshold'] ?? 5));
                $sheet->setCellValue('I'.$row, floatval($it['cost_price'] ?? 0));
                $row++;
            }
        }
        foreach (range('A','I') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $filename = 'Kho_hang_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }
}
?>