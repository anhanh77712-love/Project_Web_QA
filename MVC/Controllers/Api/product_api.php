<?php

class product_api extends controllers
{
    private $pdlist;

    public function __construct()
    {
        // Khởi tạo model dùng chung từ file của bạn
        $this->pdlist = $this->model("product_m");
    }

    // ============================================================
    // 1. QUẢN LÝ DANH SÁCH & HIỂN THỊ
    // ============================================================

    public function Get_data()
    {
        $this->list();
    }

    public function list()
    {
        $search = $_POST['txtSearch'] ?? '';
        $products_result = $this->pdlist->products_select('', $search);

        $products_list = [];
        $variants = [];
        $variant_images = [];

        if ($products_result && mysqli_num_rows($products_result) > 0) {
            while ($product = mysqli_fetch_assoc($products_result)) {
                $product_id = $product['id'];
                $products_list[] = $product;

                // Lấy biến thể cho từng sản phẩm
                $variants_result = $this->pdlist->get_variants_by_product($product_id);
                $variants[$product_id] = [];

                if ($variants_result && mysqli_num_rows($variants_result) > 0) {
                    while ($variant = mysqli_fetch_assoc($variants_result)) {
                        $variant_id = $variant['id'];
                        $variants[$product_id][] = $variant;

                        // Lấy ảnh của biến thể
                        $images_result = $this->pdlist->get_images_by_variant($variant_id);
                        $variant_images[$variant_id] = [];
                        if ($images_result) {
                            while ($image = mysqli_fetch_assoc($images_result)) {
                                $variant_images[$variant_id][] = $image;
                            }
                        }
                    }
                }
            }
        }

        $this->view('Master', [
            'Page'           => 'product_list_v',
            'products_list'  => $products_list,
            'variants'       => $variants,
            'variant_images' => $variant_images,
            'search'         => $search
        ]);
    }

    // ============================================================
    // 2. THÊM MỚI SẢN PHẨM & BIẾN THỂ
    // ============================================================

    public function show_add()
    {
        $this->view('Master', [
            'Page' => 'product_add_v',
            'categories_list' => $this->pdlist->categories_selectAll(),
            'collections_list' => $this->pdlist->collections_selectAll(),
            'products_list' => $this->pdlist->products_select('', '')
        ]);
    }

    public function Add()
    {
        if (isset($_POST['btnLuu'])) {
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

            if (empty($name) || empty($base_price)) {
                echo "<script>alert('Vui lòng nhập tên và giá bán'); history.back();</script>";
                return;
            }

            $thumbnail = $this->handle_upload('thumbnail');

            $result = $this->pdlist->products_insert(
                $name, $slug, $category_id, $collection_id, $description, 
                $thumbnail, $cost_price, $base_price, $color, $size, $quantity, $is_sale, $gender
            );

            if ($result && is_array($result)) {
                $this->handle_multiple_uploads($result['variant_id'], 'detail_images');
                $this->thongBao(true);
            } else {
                $this->thongBao(false);
            }
        }
    }

    public function AddVariant()
    {
        if (isset($_POST['btnLuu'])) {
            $product_id = $_POST['product_id'] ?? 0;
            $color = $_POST['color'] ?? '';
            $size = $_POST['size'] ?? '';
            $cost_price = $_POST['cost_price'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;

            if ($this->pdlist->variant_exists($product_id, $color, $size)) {
                echo "<script>alert('Biến thể đã tồn tại'); history.back();</script>";
                return;
            }

            $variant_id = $this->pdlist->variant_insert($product_id, $color, $size, $cost_price, $quantity);
            if ($variant_id) {
                $this->handle_multiple_uploads($variant_id, 'detail_images');
                $this->thongBao(true);
            } else {
                $this->thongBao(false);
            }
        }
    }

    // ============================================================
    // 3. SỬA & XÓA
    // ============================================================

    public function sua($id)
    {
        $product_data = $this->pdlist->products_select($id, '');
        $this->view('Master', [
            'Page'             => 'product_edit_v',
            'item'             => $product_data,
            'id'               => $id,
            'categories_list'  => $this->fetch_to_array($this->pdlist->categories_selectAll()),
            'collections_list' => $this->fetch_to_array($this->pdlist->collections_selectAll()),
            'variants'         => $this->fetch_variants_full($id)
        ]);
    }

    public function delete($id)
    {
        $this->thongBao($this->pdlist->products_delete($id));
    }

    // ============================================================
    // 4. IMPORT EXCEL & LOGIC XỬ LÝ
    // ============================================================

    public function importExcel()
    {
        if (isset($_POST['btnUpload'])) {
            $file = $_FILES['txtfile']['tmp_name'];
            $importType = $_POST['importType'] ?? 'product';

            if (empty($file)) {
                echo "<script>alert('Vui lòng chọn file!'); window.history.back();</script>";
                return;
            }

            try {
                require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
                $objExcel = PHPExcel_IOFactory::load($file);
                $sheetData = $objExcel->getActiveSheet()->toArray(null, true, true, true);

                if ($importType === 'product') {
                    $this->process_product_import($sheetData);
                } else {
                    $this->process_variant_import($sheetData);
                }
            } catch (Exception $e) {
                echo "<script>alert('Lỗi: " . $e->getMessage() . "'); history.back();</script>";
            }
        }
    }

    // Phương thức xử lý Import Sản Phẩm
    private function process_product_import($sheetData)
    {
        $count = 0;
        for ($i = 2; $i <= count($sheetData); $i++) {
            $name = trim($sheetData[$i]["A"] ?? '');
            if (empty($name)) continue;

            $cat_id = (int)($sheetData[$i]["B"] ?? 0);
            $base_price = (float)($sheetData[$i]["C"] ?? 0);
            $imgInput = trim($sheetData[$i]["H"] ?? '');

            // Xử lý ảnh (URL hoặc tên file)
            $listImages = $this->handle_excel_images($imgInput);
            $thumbnail = $listImages[0] ?? '';

            if ($this->pdlist->get_id_by_name($name) == 0) {
                $res = $this->pdlist->products_insert(
                    $name, $this->create_slug($name), $cat_id, (int)$sheetData[$i]["G"], 
                    $sheetData[$i]["D"], $thumbnail, $base_price * 0.7, $base_price, 
                    '', '', 0, (int)$sheetData[$i]["F"], $sheetData[$i]["E"]
                );
                if ($res) {
                    $count++;
                    foreach ($listImages as $k => $img) {
                        $this->pdlist->product_images_insert($res['variant_id'], $img, ($k == 0 ? 1 : 0));
                    }
                }
            }
        }
        $this->alertSuccess("Đã thêm $count sản phẩm mới.");
    }

    // Phương thức xử lý Import Biến Thể
    private function process_variant_import($sheetData)
    {
        $count = 0;
        for ($i = 2; $i <= count($sheetData); $i++) {
            $p_name = trim($sheetData[$i]["A"] ?? '');
            $p_id = $this->pdlist->get_id_by_name($p_name);
            if ($p_id == 0) continue;

            $color = trim($sheetData[$i]["B"] ?? '');
            $size = trim($sheetData[$i]["C"] ?? '');

            if (!$this->pdlist->variant_exists($p_id, $color, $size)) {
                $vid = $this->pdlist->variant_insert($p_id, $color, $size, (float)$sheetData[$i]["E"], (int)$sheetData[$i]["D"]);
                if ($vid) {
                    $count++;
                    $imgs = $this->handle_excel_images($sheetData[$i]["F"] ?? '');
                    foreach ($imgs as $k => $img) {
                        $this->pdlist->product_images_insert($vid, $img, ($k == 0 ? 1 : 0));
                    }
                }
            }
        }
        $this->alertSuccess("Đã thêm $count biến thể mới.");
    }

    // ============================================================
    // 5. CÁC HÀM BỔ TRỢ (HELPERS)
    // ============================================================

    private function handle_upload($field)
    {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $target = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
            $name = time() . '_' . basename($_FILES[$field]["name"]);
            if (move_uploaded_file($_FILES[$field]["tmp_name"], $target . $name)) return $name;
        }
        return '';
    }

    private function handle_multiple_uploads($variant_id, $field)
    {
        if (isset($_FILES[$field]) && $_FILES[$field]['name'][0] != '') {
            $files = $_FILES[$field];
            $target = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] == 0) {
                    $name = time() . '_' . $i . '_' . basename($files["name"][$i]);
                    if (move_uploaded_file($files["tmp_name"][$i], $target . $name)) {
                        $this->pdlist->product_images_insert($variant_id, $name, 0);
                    }
                }
            }
        }
    }

    private function handle_excel_images($input)
    {
        $result = [];
        if (empty($input)) return $result;
        $arr = explode(',', $input);
        foreach ($arr as $img) {
            $img = trim($img);
            if (filter_var($img, FILTER_VALIDATE_URL)) {
                $saved = $this->download_image_from_url($img);
                if ($saved) $result[] = $saved;
            } else {
                $result[] = $img;
            }
        }
        return $result;
    }

    private function download_image_from_url($url)
    {
        try {
            $content = file_get_contents($url);
            if ($content === false) return '';
            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $name = time() . '_' . rand(1000, 9999) . '.' . $ext;
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/" . $name, $content);
            return $name;
        } catch (Exception $e) { return ''; }
    }

    private function create_slug($string)
    {
        $search = ['#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#', '#(đ)#', "/[^a-zA-Z0-9\-\_]/"];
        $replace = ['a', 'd', '-'];
        return strtolower(preg_replace('/(-)+/', '-', preg_replace($search, $replace, $string)));
    }

    private function thongBao($kq)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['status_msg'] = $kq ? "success" : "error";
        header("Location: /web_qlsp/product_list");
        exit();
    }

    private function alertSuccess($msg)
    {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
              <script>window.onload = function(){ Swal.fire('Xong!', '$msg', 'success').then(()=>{ window.location.href='/web_qlsp/product_list'; }); };</script>";
    }

    private function fetch_to_array($res)
    {
        $data = [];
        if ($res) while ($r = mysqli_fetch_assoc($res)) $data[] = $r;
        return $data;
    }

    private function fetch_variants_full($pid)
    {
        $res = $this->pdlist->get_variants_by_product($pid);
        $data = [];
        while ($v = mysqli_fetch_assoc($res)) {
            $img_res = $this->pdlist->get_images_by_variant($v['id']);
            $v['images'] = $this->fetch_to_array($img_res);
            $data[] = $v;
        }
        return $data;
    }
}
?>