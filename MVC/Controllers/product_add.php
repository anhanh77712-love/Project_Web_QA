<?php
class product_add extends controllers
{
    private $prd_add;
    function __construct()
    {
        $this->prd_add = $this->model("product_m");
    }
    function Get_data()
    {
        $this->view('Master', [
            'Page' => 'product_add_v',
            'categories_list' => $this->prd_add->categories_selectAll(),
            'collections_list' => $this->prd_add->collections_selectAll(),
            'products_list' => $this->prd_add->products_select('', '')
        ]);
    }
    function thongBao($kq){
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }
        header("Location: /web_qlsp/product_list");
        exit();
    }
    function Add()
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

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên sản phẩm không được để trống');</script>";
                return;
            } else if (empty($base_price)) {
                echo "<script>alert('Giá bán không được để trống');</script>";
                return;
            } else if (empty($cost_price)) {
                echo "<script>alert('Giá nhập không được để trống');</script>";
                return;
            }

            // Upload ảnh đại diện
            $thumbnail = '';
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
                $file_name = time() . '_' . basename($_FILES["thumbnail"]["name"]);
                if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $file_name)) {
                    $thumbnail = $file_name;
                }
            }

            // Insert sản phẩm và variant, truyền thêm gender
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
                                // Insert ảnh chi tiết liên kết với variant_id
                                $this->prd_add->product_images_insert($variant_id, $file_name, 0);
                            }
                        }
                    }
                }
                
                $this->thongBao(true);
            } else {
                $this->thongBao(false);
            }
        }
    }

    function AddVariant()
    {
        if (isset($_POST['btnLuu'])) {
            $product_id = $_POST['product_id'] ?? 0;
            $color = $_POST['color'] ?? '';
            $size = $_POST['size'] ?? '';
            $cost_price = $_POST['cost_price'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;

            // Validate
            if (empty($product_id)) {
                echo "<script>alert('Vui lòng chọn sản phẩm');</script>";
                return;
            } else if (empty($cost_price)) {
                echo "<script>alert('Giá nhập không được để trống');</script>";
                return;
            }

            // Ngăn tạo trùng biến thể (màu + size) cho sản phẩm
            if ($this->prd_add->variant_exists($product_id, $color, $size)) {
                echo "<script>alert('Biến thể với màu và kích cỡ này đã tồn tại cho sản phẩm được chọn.'); history.back();</script>";
                return;
            }

            // Insert variant mới
            $variant_id = $this->prd_add->variant_insert(
                $product_id, $color, $size, $cost_price, $quantity
            );

            if ($variant_id) {
                // Upload ảnh chi tiết cho variant mới
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
                
                $this->thongBao(true);
            } else {
                $this->thongBao(false);
            }
        }
    }

    // JSON endpoint: return existing variants for a product
    function GetVariants()
    {
        header('Content-Type: application/json; charset=utf-8');
        $product_id = 0;
        if (isset($_GET['product_id'])) {
            $product_id = (int)$_GET['product_id'];
        } else if (isset($_POST['product_id'])) {
            $product_id = (int)$_POST['product_id'];
        }

        if ($product_id <= 0) {
            echo json_encode([ 'variants' => [], 'error' => 'invalid_product_id' ]);
            return;
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
        echo json_encode([ 'variants' => $variants ]);
    }
}
