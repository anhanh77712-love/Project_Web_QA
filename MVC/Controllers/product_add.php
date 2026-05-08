<?php
class product_add extends controllers
{
    private $prd_add;
    
    function __construct()
    {
        parent::__construct();
        $this->prd_add = $this->model("product_m");
    }

    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data()
    {
        $this->view('Master', [
            'Page' => 'product_add_v'
        ]);
    }

    // 2. API: Lấy dữ liệu khởi tạo cho các thẻ <select> (Danh mục, Bộ sưu tập, Sản phẩm)
    function api_get_form_data() {
        $this->setApiHeader();
        
        // Lấy Danh mục
        $cats_result = $this->prd_add->categories_selectAll();
        $categories = [];
        if ($cats_result && mysqli_num_rows($cats_result) > 0) {
            while ($row = mysqli_fetch_assoc($cats_result)) { $categories[] = $row; }
        }

        // Lấy Bộ sưu tập
        $cols_result = $this->prd_add->collections_selectAll();
        $collections = [];
        if ($cols_result && mysqli_num_rows($cols_result) > 0) {
            while ($row = mysqli_fetch_assoc($cols_result)) { $collections[] = $row; }
        }

        // Lấy Sản phẩm (Cho tab thêm Variant)
        $prods_result = $this->prd_add->products_select('', '');
        $products = [];
        if ($prods_result && mysqli_num_rows($prods_result) > 0) {
            while ($row = mysqli_fetch_assoc($prods_result)) { 
                $products[] = ['id' => $row['id'], 'name' => $row['name']]; 
            }
        }

        echo json_encode([
            'success' => true, 
            'categories' => $categories, 
            'collections' => $collections, 
            'products' => $products
        ]);
        exit;
    }

    // API: Thêm sản phẩm mới + Variant đầu tiên
    function api_add()
    {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $collection_id = $_POST['collection_id'] ?? '';
        $description = $_POST['description'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $color = $_POST['color'] ?? '';
        $size = $_POST['size'] ?? '';
        $cost_price = $_POST['cost_price'] ?? 0;
        $base_price = $_POST['base_price'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        $is_sale = isset($_POST['is_sale']) ? 1 : 0;

        // Validate cơ bản
        if (empty($name) || empty($base_price) || empty($cost_price)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đủ Tên sản phẩm, Giá nhập và Giá bán']); exit;
        }

        // Upload ảnh đại diện
        $thumbnail = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES["thumbnail"]["name"]);
            if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $file_name)) {
                $thumbnail = $file_name;
            }
        }

        // Insert sản phẩm và variant
        $result = $this->prd_add->products_insert(
            $name, $slug, $category_id, $collection_id, $description, 
            $thumbnail, $cost_price, $base_price, $color, $size, $quantity, $is_sale, $gender
        );

        if ($result && is_array($result)) {
            $product_id = $result['product_id'];
            $variant_id = $result['variant_id'];

            // Upload ảnh chi tiết và liên kết với variant
            if (isset($_FILES['detail_images']) && count($_FILES['detail_images']['name']) > 0 && $_FILES['detail_images']['name'][0] != '') {
                $files = $_FILES['detail_images'];
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (!empty($files['name'][$i]) && $files['error'][$i] == 0) {
                        $file_name = time() . '_' . $i . '_' . basename($files["name"][$i]);
                        if (move_uploaded_file($files["tmp_name"][$i], $target_dir . $file_name)) {
                            $this->prd_add->product_images_insert($variant_id, $file_name, 0);
                        }
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Tạo sản phẩm mới thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu dữ liệu vào Database']);
        }
        exit;
    }

    // API: Thêm Variant cho sản phẩm có sẵn
    function api_add_variant()
    {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Lỗi phương thức']); exit;
        }

        $product_id = $_POST['product_id'] ?? 0;
        $color = $_POST['color'] ?? '';
        $size = $_POST['size'] ?? '';
        $cost_price = $_POST['cost_price'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;

        if (empty($product_id) || empty($cost_price)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn sản phẩm và nhập giá']); exit;
        }

        // Ngăn tạo trùng biến thể
        if ($this->prd_add->variant_exists($product_id, $color, $size)) {
            echo json_encode(['success' => false, 'message' => 'Biến thể (Màu + Size) này đã tồn tại cho sản phẩm này!']); exit;
        }

        // Insert variant mới
        $variant_id = $this->prd_add->variant_insert($product_id, $color, $size, $cost_price, $quantity);

        if ($variant_id) {
            // Upload ảnh chi tiết
            if (isset($_FILES['detail_images']) && count($_FILES['detail_images']['name']) > 0 && $_FILES['detail_images']['name'][0] != '') {
                $files = $_FILES['detail_images'];
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (!empty($files['name'][$i]) && $files['error'][$i] == 0) {
                        $file_name = time() . '_' . $i . '_' . basename($files["name"][$i]);
                        if (move_uploaded_file($files["tmp_name"][$i], $target_dir . $file_name)) {
                            $this->prd_add->product_images_insert($variant_id, $file_name, 0);
                        }
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã thêm Variant mới cho sản phẩm!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu Variant vào Database']);
        }
        exit;
    }

    // API: Lấy danh sách biến thể của 1 sản phẩm (Đã có sẵn của bạn, mình chuẩn hóa lại tí)
    function api_get_variants()
    {
        $this->setApiHeader();
        $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'variants' => []]); return;
        }

        $result = $this->prd_add->get_variants_by_product($product_id);
        $variants = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $variants[] = [
                    'id' => (int)$row['id'],
                    'color' => $row['color'],
                    'size' => $row['size']
                ];
            }
        }
        echo json_encode(['success' => true, 'variants' => $variants]);
        exit;
    }
}
?>