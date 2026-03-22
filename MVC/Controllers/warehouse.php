<?php
class warehouse extends controllers {
    private $m;
    function __construct() {
        $this->m = $this->model('warehouse_m');
    }

    function Get_data() {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $items = $this->m->warehouse_selectItems($q, $category, $status);
        $categories = $this->m->categories_selectAll();

        // Summaries
        $sum_stock = 0; $low_stock_count = 0; $out_of_stock_count = 0; $stock_value = 0;
        foreach ($items as $it) {
            $stock = intval($it['stock_quantity'] ?? 0);
            $threshold = intval($it['threshold'] ?? 5);
            $sum_stock += $stock;
            if ($stock == 0) { $out_of_stock_count++; }
            elseif ($stock <= $threshold) { $low_stock_count++; }
            // Inventory value = import/cost price * quantity
            $stock_value += (float)($it['cost_price'] ?? 0) * $stock;
        }

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

    // Import Excel: show upload form (GET) and process file (POST)
    function import() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('Master', [
                'Page' => 'warehouse_import_v'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file']['tmp_name'])) {
            $this->view('Master', [
                'Page' => 'warehouse_import_v',
                'error' => 'Vui lòng chọn tệp Excel để nhập'
            ]);
            return;
        }

        $tmp = $_FILES['file']['tmp_name'];
        try {
            $objPHPExcel = PHPExcel_IOFactory::load($tmp);
        } catch (Exception $e) {
            $this->view('Master', [
                'Page' => 'warehouse_import_v',
                'error' => 'Không thể đọc tệp Excel: ' . $e->getMessage()
            ]);
            return;
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

        // Accept "sku" and either "newstock" or "stock"/"tồn kho mới"
        $colSku = null; $colNewStock = null;
        foreach ($headers as $col => $name) {
            if ($name === 'sku') $colSku = $col;
            if ($name === 'newstock' || $name === 'new stock' || $name === 'stock' || $name === 'tồn kho mới') $colNewStock = $col;
        }

        if (!$colSku || !$colNewStock) {
            $this->view('Master', [
                'Page' => 'warehouse_import_v',
                'error' => 'Không tìm thấy cột bắt buộc: SKU và NewStock/Stock/Tồn kho mới',
            ]);
            return;
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
                $results['details'][] = ['sku' => $sku, 'status' => 'Lỗi khi cập nhật'];
            }
        }

        $this->view('Master', [
            'Page' => 'warehouse_import_v',
            'results' => $results
        ]);
    }
    // Adjust stock via modal
    function adjust_stock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Phương thức không hỗ trợ']);
            return;
        }
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? intval($_POST['variant_id']) : null;
        $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

        if ($product_id <= 0 || $delta == 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
            return;
        }

        // Require variant_id because base products table has no stock column
        if ($variant_id === null) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn biến thể để điều chỉnh tồn kho']);
            return;
        }

        $ok = $this->m->warehouse_adjustStock($product_id, $variant_id, $delta);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật tồn kho']);
        }
    }

    // Export Excel of current filtered items
    function export_excel() {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $items = $this->m->warehouse_selectItems($q, $category, $status);

        if (!$items || count($items) === 0) {
            echo "<script>alert('Không có dữ liệu để xuất!'); window.history.back();</script>";
            return;
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setCellValue('A1', 'Sản phẩm');
        $sheet->setCellValue('B1', 'Biến thể');
        $sheet->setCellValue('C1', 'SKU');
        $sheet->setCellValue('D1', 'Danh mục');
        $sheet->setCellValue('E1', 'Tồn kho');
        $sheet->setCellValue('F1', 'Đang giữ');
        $sheet->setCellValue('G1', 'Sẵn có');
        $sheet->setCellValue('H1', 'Ngưỡng cảnh báo');
        $sheet->setCellValue('I1', 'Giá cơ bản');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $it) {
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
            $sheet->setCellValue('I'.$row, floatval($it['base_price'] ?? 0));
            $row++;
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
