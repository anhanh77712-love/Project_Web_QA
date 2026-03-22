<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
class product_list extends controllers
{
    private $pdlist;
    public function __construct()
    {
        $this->pdlist = $this->model("product_m");
    }
    public function Get_data()
    {
        $products_result = $this->pdlist->products_select('', '');

        // Convert result to array and get variants + images
        $products_list  = [];
        $variants       = [];
        $variant_images = [];

        if ($products_result && mysqli_num_rows($products_result) > 0) {
            while ($product = mysqli_fetch_assoc($products_result)) {
                $product_id      = $product['id'];
                $products_list[] = $product;

                // Get variants for this product
                $variants_result       = $this->pdlist->get_variants_by_product($product_id);
                $variants[$product_id] = [];

                if ($variants_result && mysqli_num_rows($variants_result) > 0) {
                    while ($variant = mysqli_fetch_assoc($variants_result)) {
                        $variant_id              = $variant['id'];
                        $variants[$product_id][] = $variant;

                        // Get images for this variant
                        $images_result               = $this->pdlist->get_images_by_variant($variant_id);
                        $variant_images[$variant_id] = [];

                        if ($images_result && mysqli_num_rows($images_result) > 0) {
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
        ]);
    }
    public function thongBao($kq)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }

        // 3. Quay trở lại trang danh sách
        header("Location: /web_qlsp/product_list");
        exit();
    }
    public function reset()
    {
        header("Location: /web_qlsp/product_list");
    }
    public function search()
    {
        if (isset($_POST['btnTimkiem'])) {
            $search = $_POST['txtSearch'];

            $products_result = $this->pdlist->products_select('', $search);

            // Convert result to array and get variants + images
            $products_list  = [];
            $variants       = [];
            $variant_images = [];

            if ($products_result && mysqli_num_rows($products_result) > 0) {
                while ($product = mysqli_fetch_assoc($products_result)) {
                    $product_id      = $product['id'];
                    $products_list[] = $product;

                    // Get variants for this product
                    $variants_result       = $this->pdlist->get_variants_by_product($product_id);
                    $variants[$product_id] = [];

                    if ($variants_result && mysqli_num_rows($variants_result) > 0) {
                        while ($variant = mysqli_fetch_assoc($variants_result)) {
                            $variant_id              = $variant['id'];
                            $variants[$product_id][] = $variant;

                            // Get images for this variant
                            $images_result               = $this->pdlist->get_images_by_variant($variant_id);
                            $variant_images[$variant_id] = [];

                            if ($images_result && mysqli_num_rows($images_result) > 0) {
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
                'search'         => $search,
            ]);
        }
        // PHẦN XUẤT EXCEL
        else if (isset($_POST['btnXuatExcel'])) {
            // 1. Kiểm tra thư viện
            if (! class_exists('PHPExcel')) {
                require_once "./MVC/Bridge.php";
                if (! class_exists('PHPExcel')) {
                    die("Lỗi: Không tìm thấy thư viện PHPExcel.");
                }
            }

            $objExcel = new PHPExcel();
            $objExcel->setActiveSheetIndex(0);
            $sheet = $objExcel->getActiveSheet()->setTitle('DanhSachSanPham');

            // 2. Cấu hình Header
            $rowCount = 1;
            $columns  = [
                'A' => 'ID Sản phẩm',
                'B' => 'Tên Sản Phẩm',
                'C' => 'ID Danh Mục',
                'D' => 'ID Bộ Sưu Tập',
                'E' => 'Giá Bán',
                'F' => 'Giá Nhập', // Đã có data
                'G' => 'Màu',        // Đã có data
                'H' => 'Size',        // Đã có data
                'I' => 'Kho',         // Đã có data
                'J' => 'Giới Tính',
                'K' => 'Ngày Tạo',
            ];

            // Vẽ tiêu đề
            foreach ($columns as $col => $title) {
                $sheet->setCellValue($col . $rowCount, $title);
                $sheet->getStyle($col . $rowCount)->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'E0E0E0']],
                ]);
            }

            // 3. Đổ dữ liệu
            $keyword = $_POST['txtSearch'] ?? '';

            // GỌI HÀM MỚI (Đảm bảo bạn đã thêm hàm này vào ProductModel như bước trước)
            $data = $this->pdlist->get_products_export_full($keyword);

            if ($data) {
                while ($row = mysqli_fetch_array($data)) {
                    $rowCount++;

                    $sheet->setCellValue('A' . $rowCount, $row['id']);
                    $sheet->setCellValue('B' . $rowCount, $row['name']);
                    $sheet->setCellValue('C' . $rowCount, $row['category_id']);
                    $sheet->setCellValue('D' . $rowCount, $row['collection_id']);
                    $sheet->setCellValue('E' . $rowCount, $row['base_price']);

                    // --- SỬA LẠI KHÚC NÀY ---
                    // Thay vì để '', ta lấy đúng tên cột trong Database ra
                    // Dùng ?? '' để nếu sản phẩm không có biến thể thì để trống thay vì báo lỗi
                    $sheet->setCellValue('F' . $rowCount, $row['input_price'] ?? '');
                    $sheet->setCellValue('G' . $rowCount, $row['color'] ?? '');
                    $sheet->setCellValue('H' . $rowCount, $row['size'] ?? '');
                    $sheet->setCellValue('I' . $rowCount, $row['stock'] ?? '');
                    // ------------------------

                    $sheet->setCellValue('J' . $rowCount, $row['gender']);
                    $sheet->setCellValue('K' . $rowCount, $row['created_at']);
                }
            }

            // 4. Auto-size cột
            foreach (array_keys($columns) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 5. Xuất file
            $filename = "Export_Products_" . date('Y_m_d_H_i_s') . ".xlsx";
            if (ob_get_contents()) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }

    }

    public function sua($id)
    {
        // Get product info
        $product_result = $this->pdlist->products_select($id, '');

        // Get variants and images
        $variants       = [];
        $variant_images = [];

        if ($product_result && mysqli_num_rows($product_result) > 0) {
            // Get variants for this product
            $product_id      = (int) $id;
            $variants_result = $this->pdlist->get_variants_by_product($product_id);
            if ($variants_result && mysqli_num_rows($variants_result) > 0) {
                while ($variant = mysqli_fetch_assoc($variants_result)) {
                    $variant_id = $variant['id'];
                    $variants[] = $variant;

                    // Get images for this variant
                    $images_result               = $this->pdlist->get_images_by_variant($variant_id);
                    $variant_images[$variant_id] = [];

                    if ($images_result && mysqli_num_rows($images_result) > 0) {
                        while ($image = mysqli_fetch_assoc($images_result)) {
                            $variant_images[$variant_id][] = $image;
                        }
                    }
                }
            }
        }

        // Convert results to arrays để tránh vấn đề con trỏ
        $categories        = [];
        $categories_result = $this->pdlist->categories_selectAll();
        if ($categories_result && mysqli_num_rows($categories_result) > 0) {
            while ($cat = mysqli_fetch_assoc($categories_result)) {
                $categories[] = $cat;
            }
        }

        $collections        = [];
        $collections_result = $this->pdlist->collections_selectAll();
        if ($collections_result && mysqli_num_rows($collections_result) > 0) {
            while ($col = mysqli_fetch_assoc($collections_result)) {
                $collections[] = $col;
            }
        }

        $this->view('Master', [
            'Page'             => 'product_edit_v',
            'item'             => $product_result,
            'id'               => $id,
            'variants'         => $variants,
            'variant_images'   => $variant_images,
            'categories_list'  => $categories,
            'collections_list' => $collections,
        ]);
    }
    public function update()
    {
        if (isset($_POST['btnLuu'])) {
            // Lấy dữ liệu từ form
            $id            = $_POST['id'];
            $name          = $_POST['name'];
            $slug          = $_POST['slug'];
            $base_price    = $_POST['base_price'];
            $category_id   = $_POST['category_id'];
            $collection_id = $_POST['collection_id'];
            $description   = $_POST['description'];
            $gender        = $_POST['gender'];
            $is_sale       = isset($_POST['is_sale']) ? 1 : 0;

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên sản phẩm không được để trống'); window.location.href='/web_qlsp/product_list/sua/$id';</script>";
                return;
            } else if (empty($base_price)) {
                echo "<script>alert('Giá sản phẩm không được để trống'); window.location.href='/web_qlsp/product_list/sua/$id';</script>";
                return;
            } else {
                // Upload ảnh nếu có
                $thumbnail = '';
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
                    $file_name  = time() . '_' . basename($_FILES["thumbnail"]["name"]);
                    if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                    }
                } else {
                    // Nếu không upload ảnh mới, lấy ảnh cũ từ database
                    $product_data = $this->pdlist->products_select($id, '');
                    if ($product_data && mysqli_num_rows($product_data) > 0) {
                        $old_product = mysqli_fetch_assoc($product_data);
                        $thumbnail   = $old_product['thumbnail'];
                    }
                }

                // Cập nhật DB
                $kq = $this->pdlist->products_update($id, $name, $slug, $base_price, $category_id, $collection_id, $description, $thumbnail, $gender, $is_sale);

                $this->thongBao($kq);
            }
        }
    }
    public function delete($id)
    {
        $kq = $this->pdlist->products_delete($id);

        $this->thongBao($kq);
    }

    public function suaVariant($variant_id)
    {
        // Get variant data
        $variant_result = $this->pdlist->get_variant_by_id($variant_id);
        $variant        = null;
        $images         = [];

        if ($variant_result && mysqli_num_rows($variant_result) > 0) {
            $variant       = mysqli_fetch_assoc($variant_result);
            $images_result = $this->pdlist->get_images_by_variant($variant_id);

            if ($images_result && mysqli_num_rows($images_result) > 0) {
                while ($image = mysqli_fetch_assoc($images_result)) {
                    $images[] = $image;
                }
            }
        }

        $this->view('Master', [
            'Page'       => 'variant_edit_v',
            'variant'    => $variant,
            'variant_id' => $variant_id,
            'images'     => $images,
        ]);
    }

    public function updateVariant()
    {
        if (isset($_POST['btnLuu'])) {
            $variant_id  = $_POST['variant_id'];
            $product_id  = $_POST['product_id'];
            $color       = $_POST['color'];
            $size        = $_POST['size'];
            $input_price = $_POST['input_price'];
            $stock       = $_POST['stock'];

            $result = $this->pdlist->variant_update($variant_id, $color, $size, $input_price, $stock);

            if ($result) {
                $_SESSION['status_msg'] = 'success';
                header("Location: /web_qlsp/product_list/sua/$product_id");
            } else {
                $_SESSION['status_msg'] = 'error';
                header("Location: /web_qlsp/product_list/sua/$product_id");
            }
            exit();
        }
    }

    public function uploadVariantImages()
    {
        if (isset($_POST) && isset($_FILES['detail_images'])) {
            $variant_id   = $_POST['variant_id'];
            $product_id   = $this->pdlist->get_product_id_by_variant($variant_id);
            $files        = $_FILES['detail_images'];
            $target_dir   = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
            $upload_count = 0;

            for ($i = 0; $i < count($files['name']); $i++) {
                if (! empty($files['name'][$i]) && $files['error'][$i] == 0) {
                    $original_name = $files["name"][$i];
                    $original_name = iconv('UTF-8', 'UTF-8//IGNORE', $original_name);
                    $file_name     = time() . '_' . $i . '_' . $original_name;
                    if (move_uploaded_file($files["tmp_name"][$i], $target_dir . $file_name)) {
                        $this->pdlist->product_images_insert($variant_id, $file_name, 0);
                        $upload_count++;
                    }
                }
            }

            if ($upload_count > 0) {
                $_SESSION['status_msg'] = 'success';
            } else {
                $_SESSION['status_msg'] = 'error';
            }
            header("Location: /web_qlsp/product_list/sua/$product_id");
            exit();
        }
    }

    public function deleteVariantImage($image_id)
    {
        $result = $this->pdlist->get_images_by_id($image_id);
        if ($result && mysqli_num_rows($result) > 0) {
            $image      = mysqli_fetch_assoc($result);
            $variant_id = $image['variant_id'];
            $product_id = $this->pdlist->get_product_id_by_variant($variant_id);

            // Delete file
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/" . $image['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete from database
            $this->pdlist->product_image_delete($image_id);

            header("Location: /web_qlsp/product_list/sua/$product_id");
            exit();
        }
    }

    public function deleteVariant($variant_id)
    {
        $kq = $this->pdlist->variant_delete($variant_id);

        $this->thongBao($kq);
    }
    // --- HÀM 1: TẠO SLUG (Biến tên sản phẩm thành link không dấu) ---
    private function create_slug($string)
    {
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

    // --- HÀM 2: TẢI ẢNH TỪ URL (Hỗ trợ nhập Excel bằng Link Online) ---
    private function download_image_from_url($url)
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        try {
            // Lưu ý: Cần bật allow_url_fopen trong php.ini
            $imageContent = file_get_contents($url);
            if ($imageContent === false) {
                return '';
            }

            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (! $ext) {
                $ext = 'jpg';
            }

            // Đặt tên file ngẫu nhiên
            $newFileName = time() . '_' . rand(1000, 9999) . '.' . $ext;

            // Lưu vào thư mục Picture giống như khi upload thủ công
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/";
            $fullPath = $target_dir . $newFileName;

            file_put_contents($fullPath, $imageContent);
            
            // Chỉ trả về tên file, không có đường dẫn
            return $newFileName;
        } catch (Exception $e) {
            return '';
        }
    }

    public function importExcel()
    {
        if (isset($_POST['btnUpload'])) {
            $file = $_FILES['txtfile']['tmp_name'];
            $importType = isset($_POST['importType']) ? $_POST['importType'] : 'product';

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

                $countNewProduct = 0;
                $countNewVariant = 0;

                if ($importType === 'product') {
                    // IMPORT SẢN PHẨM
                    // Cột: A=Name, B=CategoryID, C=BasePrice, D=Description, E=Gender, F=IsSale, G=CollectionID, H=ThumbnailImages
                    
                    for ($i = 2; $i <= count($sheetData); $i++) {
                        $name = trim($sheetData[$i]["A"] ?? '');
                        if (empty($name)) continue;

                        $cat_id = (int) ($sheetData[$i]["B"] ?? 0);
                        $base_price = (float) ($sheetData[$i]["C"] ?? 0);
                        $description = $sheetData[$i]["D"] ?? '';
                        $gender = trim($sheetData[$i]["E"] ?? 'Nam');
                        $is_sale = (int) ($sheetData[$i]["F"] ?? 0);
                        $collection_id = (int) ($sheetData[$i]["G"] ?? 0);
                        $imgInput = trim($sheetData[$i]["H"] ?? '');

                        // Xử lý ảnh
                        $listImages = [];
                        $thumbnail = '';
                        if (!empty($imgInput)) {
                            $rawArr = explode(',', $imgInput);
                            foreach ($rawArr as $imgName) {
                                $imgName = trim($imgName);
                                if (!empty($imgName)) {
                                    if (filter_var($imgName, FILTER_VALIDATE_URL)) {
                                        // Download từ URL và lưu vào Picture
                                        $savedFileName = $this->download_image_from_url($imgName);
                                        if ($savedFileName) $listImages[] = $savedFileName;
                                    } else {
                                        // Tên file local, giả định đã có trong Picture folder
                                        // Có thể là "ao1.jpg" hoặc "DataInput/ao1.jpg"
                                        $listImages[] = $imgName;
                                    }
                                }
                            }
                            if (count($listImages) > 0) $thumbnail = $listImages[0];
                        }

                        // Kiểm tra sản phẩm đã tồn tại chưa
                        $product_id = $this->pdlist->get_id_by_name($name);
                        
                        if ($product_id == 0) {
                            // Tạo sản phẩm mới (không tạo variant mặc định)
                            $slug = $this->create_slug($name);
                            $cost_price = $base_price * 0.7; // Giá nhập mặc định 70%
                            
                            $result_array = $this->pdlist->products_insert(
                                $name, $slug, $cat_id, $collection_id, $description, $thumbnail,
                                $cost_price, $base_price, '', '', 0, $is_sale, $gender
                            );

                            if ($result_array) {
                                $product_id = $result_array['product_id'];
                                $variant_id = $result_array['variant_id'];
                                $countNewProduct++;
                                
                                // Thêm ảnh cho variant mặc định
                                if (count($listImages) > 0 && $variant_id) {
                                    foreach ($listImages as $key => $imgUrl) {
                                        $is_main = ($key == 0) ? 1 : 0;
                                        $this->pdlist->product_images_insert($variant_id, $imgUrl, $is_main);
                                    }
                                }
                            }
                        }
                    }

                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        window.onload = function() {
                            Swal.fire({
                                title: 'Hoàn tất!',
                                html: 'Đã thêm <b>$countNewProduct</b> sản phẩm mới.',
                                icon: 'success'
                            }).then(() => { window.location.href = '/web_qlsp/product_list'; });
                        };
                    </script>";

                } else if ($importType === 'variant') {
                    // IMPORT BIẾN THỂ
                    // Cột: A=ProductName, B=Color, C=Size, D=Stock, E=InputPrice, F=Images
                    
                    for ($i = 2; $i <= count($sheetData); $i++) {
                        $product_name = trim($sheetData[$i]["A"] ?? '');
                        if (empty($product_name)) continue;

                        $color = trim($sheetData[$i]["B"] ?? '');
                        $size = trim($sheetData[$i]["C"] ?? '');
                        $stock = (int) ($sheetData[$i]["D"] ?? 0);
                        $input_price = (float) ($sheetData[$i]["E"] ?? 0);
                        $imgInput = trim($sheetData[$i]["F"] ?? '');

                        // Tìm product_id
                        $product_id = $this->pdlist->get_id_by_name($product_name);
                        if ($product_id == 0) continue; // Bỏ qua nếu sản phẩm không tồn tại

                        // Xử lý ảnh
                        // $listImages = [];
                        // if (!empty($imgInput)) {
                        //     $rawArr = explode(',', $imgInput);
                        //     foreach ($rawArr as $imgName) {
                        //         $imgName = trim($imgName);
                        //         if (!empty($imgName)) {
                        //             if (filter_var($imgName, FILTER_VALIDATE_URL)) {
                        //                 $savedPath = $this->download_image_from_url($imgName, 'Public/Picture/uploads/');
                        //                 if ($savedPath) $listImages[] = $savedPath;
                        //             } else {
                        //                 $listImages[] = $uploadFolder . $imgName;
                        //             }
                        //         }
                        //     }
                        // }
                        $listImages = [];
                        if (!empty($imgInput)) {
                            $rawArr = explode(',', $imgInput);
                            foreach ($rawArr as $imgName) {
                                $imgName = trim($imgName);
                                if (!empty($imgName)) {
                                    if (filter_var($imgName, FILTER_VALIDATE_URL)) {
                                        // Download từ URL và lưu vào Picture
                                        $savedFileName = $this->download_image_from_url($imgName);
                                        if ($savedFileName) $listImages[] = $savedFileName;
                                    } else {
                                        // Tên file local, giả định đã có trong Picture folder
                                        // Có thể là "ao1.jpg" hoặc "DataInput/ao1.jpg"
                                        $listImages[] = $imgName;
                                    }
                                }
                            }
                        }
                        

                        // Kiểm tra variant đã tồn tại chưa
                        if (!$this->pdlist->variant_exists($product_id, $color, $size)) {
                            $variant_id = $this->pdlist->variant_insert($product_id, $color, $size, $input_price, $stock);
                            
                            if ($variant_id) {
                                $countNewVariant++;
                                // Thêm ảnh cho variant
                                if (count($listImages) > 0) {
                                    foreach ($listImages as $key => $imgUrl) {
                                        $is_main = ($key == 0) ? 1 : 0;
                                        $this->pdlist->product_images_insert($variant_id, $imgUrl, $is_main);
                                    }
                                }
                            }
                        }
                    }

                    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        window.onload = function() {
                            Swal.fire({
                                title: 'Hoàn tất!',
                                html: 'Đã thêm <b>$countNewVariant</b> biến thể mới.',
                                icon: 'success'
                            }).then(() => { window.location.href = '/web_qlsp/product_list'; });
                        };
                    </script>";
                }

            } catch (Exception $e) {
                echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
            }
        }
    }

    // Tải file mẫu Excel cho SẢN PHẨM
    public function downloadProductTemplate()
    {
        if (!class_exists('PHPExcel')) {
            require_once "./MVC/Bridge.php";
        }
        
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Products');
        
        // Style cho header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        
        // Style cho dữ liệu mẫu
        $dataStyle = [
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]]
        ];
        
        $headers = [
            'A' => 'Tên Sản Phẩm',
            'B' => 'Mã Danh Mục',
            'C' => 'Giá Bán',
            'D' => 'Mô Tả',
            'E' => 'Giới Tính',
            'F' => 'Sale (0/1)',
            'G' => 'Mã BST',
            'H' => 'Link/Tên Ảnh'
        ];
        
        // Set header
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col.'1', $title);
            $sheet->getStyle($col.'1')->applyFromArray($headerStyle);
            $sheet->getRowDimension('1')->setRowHeight(25);
        }
        
        // Ví dụ mẫu 1
        $sheet->setCellValue('A2', 'Áo thun cổ tròn');
        $sheet->setCellValue('B2', '1');
        $sheet->setCellValue('C2', '199000');
        $sheet->setCellValue('D2', 'Chất liệu cotton cao cấp, thoáng mát');
        $sheet->setCellValue('E2', 'Nam');
        $sheet->setCellValue('F2', '0');
        $sheet->setCellValue('G2', '1');
        $sheet->setCellValue('H2', 'DataInput/ao1.jpg, DataInput/ao2.jpg');
        
        // Ví dụ mẫu 2
        $sheet->setCellValue('A3', 'Quần jean slim fit');
        $sheet->setCellValue('B3', '2');
        $sheet->setCellValue('C3', '450000');
        $sheet->setCellValue('D3', 'Quần jean co giãn 4 chiều');
        $sheet->setCellValue('E3', 'Nam');
        $sheet->setCellValue('F3', '1');
        $sheet->setCellValue('G3', '2');
        $sheet->setCellValue('H3', 'DataInput/quan1.jpg');
        
        // Apply style cho data
        $sheet->getStyle('A2:H3')->applyFromArray($dataStyle);
        
        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set minimum width cho cột Description và Images
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(35);
        
        // Freeze header row
        $sheet->freezePane('A2');

        $filename = 'Template_SanPham_'.date('Ymd').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    // Tải file mẫu Excel cho BIẾN THỂ
    public function downloadVariantTemplate()
    {
        if (!class_exists('PHPExcel')) {
            require_once "./MVC/Bridge.php";
        }
        
        $objExcel = new PHPExcel();
        $objExcel->setActiveSheetIndex(0);
        $sheet = $objExcel->getActiveSheet()->setTitle('Variants');
        
        // Style cho header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '70AD47']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        
        // Style cho dữ liệu mẫu
        $dataStyle = [
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]]
        ];
        
        $headers = [
            'A' => 'Tên Sản Phẩm',
            'B' => 'Màu Sắc',
            'C' => 'Kích Thước',
            'D' => 'Số Lượng',
            'E' => 'Giá Nhập',
            'F' => 'Link/Tên Ảnh'
        ];
        
        // Set header
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col.'1', $title);
            $sheet->getStyle($col.'1')->applyFromArray($headerStyle);
            $sheet->getRowDimension('1')->setRowHeight(25);
        }
        
        // Ví dụ mẫu 1
        $sheet->setCellValue('A2', 'Áo thun cổ tròn');
        $sheet->setCellValue('B2', 'Đen');
        $sheet->setCellValue('C2', 'M');
        $sheet->setCellValue('D2', '50');
        $sheet->setCellValue('E2', '120000');
        $sheet->setCellValue('F2', 'DataInput/den-m.jpg, DataInput/den-m-2.jpg');
        
        // Ví dụ mẫu 2
        $sheet->setCellValue('A3', 'Áo thun cổ tròn');
        $sheet->setCellValue('B3', 'Trắng');
        $sheet->setCellValue('C3', 'L');
        $sheet->setCellValue('D3', '30');
        $sheet->setCellValue('E3', '120000');
        $sheet->setCellValue('F3', 'DataInput/trang-l.jpg');
        
        // Ví dụ mẫu 3
        $sheet->setCellValue('A4', 'Áo thun cổ tròn');
        $sheet->setCellValue('B4', 'Xanh');
        $sheet->setCellValue('C4', 'XL');
        $sheet->setCellValue('D4', '40');
        $sheet->setCellValue('E4', '120000');
        $sheet->setCellValue('F4', 'DataInput/xanh-xl.jpg');
        
        // Apply style cho data
        $sheet->getStyle('A2:F4')->applyFromArray($dataStyle);
        
        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set minimum width cho cột ProductName và Images
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(40);
        
        // Freeze header row
        $sheet->freezePane('A2');

        $filename = 'Template_BienThe_'.date('Ymd').'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

}
