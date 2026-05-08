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
            
            // Lấy danh mục để đổ vào Select (Model này trả về mảng Array)
            $categories = $this->m->categories_selectAll();
            if (is_array($categories) && count($categories) > 0) {
                foreach ($categories as &$c) {
                    $c['name'] = mb_convert_encoding($c['name'], 'UTF-8', 'UTF-8');
                }
            }

            // Tính toán thống kê
            $sum_stock = 0; $low_stock_count = 0; $out_of_stock_count = 0; $stock_value = 0;
            $processed_items = [];

            // SỬA Ở ĐÂY: Vì $items là mảng, ta dùng is_array và foreach
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $it) {
                    $stock = intval($it['stock_quantity'] ?? 0);
                    $threshold = 5;
                    $reserved = 0;
                    $available = max(0, $stock - $reserved); // Số lượng có thể bán thực tế (đã trừ đi số lượng đã đặt nhưng chưa xuất kho)

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


}
?>