<?php
class import extends controllers {
    private $sp;
    private $cate;

    function __construct() {
        // Khởi tạo model và gán vào thuộc tính private
        $this->sp = $this->model('product_m');
        $this->cate = $this->model('categories_m');
    }

    function Get_data() {
        $this->view('Master', [
            'Page' => 'import_v'
        ]);
    }

    function create_slug($string) {
        $search = array(
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
            '/[^a-zA-Z0-9\-\_]/',
        );
        $replace = array('a', 'e', 'i', 'o', 'u', 'y', 'd', 'A', 'E', 'I', 'O', 'U', 'Y', 'D', '-',);
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower(trim($string, '-'));
        return $string;
    }

    function upload_products() {
        if (isset($_POST['btnUpload'])) {
            $file = $_FILES['txtfile']['tmp_name'];

            if (empty($file)) {
                echo "<script>alert('Vui lòng chọn file!'); window.history.back();</script>";
                return;
            }

            try {
                $objReader = PHPExcel_IOFactory::createReaderForFile($file);
                $objExcel = $objReader->load($file);
                $sheet = $objExcel->getSheet(0);
                $sheetData = $sheet->toArray(null, true, true, true);

                $countSuccess = 0;

                for ($i = 2; $i <= count($sheetData); $i++) {
                    $name = $sheetData[$i]["A"];
                    $price = $sheetData[$i]["B"];
                    $cat_id = $sheetData[$i]["C"];
                    $description = $sheetData[$i]["D"];

                    if (empty($name) || empty($price)) continue;

                    $slug = $this->create_slug($name);
                    
                    // SỬA TẠI ĐÂY: Sử dụng đối tượng $this->sp đã khai báo
                    $kq = $this->sp->products_insert($name, $slug, $price, $cat_id, '', $description, '');
                    
                    if ($kq) {
                        $countSuccess++;
                    } else {
                        // SỬA LỖI DÒNG 72: Thay $this->userModel bằng $this->sp
                        // Vì model 'product_m' kế thừa từ DB nên nó có thuộc tính 'con'
                        die("Lỗi SQL tại dòng $i: " . mysqli_error($this->sp->con));
                    }
                }

                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            title: 'Hoàn tất!',
                            text: 'Đã nhập thành công ' + $countSuccess + ' sản phẩm.',
                            icon: 'success',
                            confirmButtonText: 'Tuyệt vời',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '/web_qlsp/import';
                        });
                    };
                </script>";

            } catch (Exception $e) {
                echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
            }
        }
    }

    
   function upload_categories() {
        if(isset($_POST['btnUpload'])){
            if(isset($_FILES['txtfile']) && $_FILES['txtfile']['error'] == 0) {
                $file = $_FILES['txtfile']['tmp_name'];
                $objReader = PHPExcel_IOFactory::createReaderForFile($file);
                $objExcel = $objReader->load($file);
                $sheetData = $objExcel->getActiveSheet()->toArray(null, true, true, true);

                $successCount = 0;
                for($i = 2; $i <= count($sheetData); $i++){
                    $name = trim($sheetData[$i]["A"]); 
                    if(empty($name)) continue; // Bỏ qua dòng trống

                    $slug = !empty($sheetData[$i]["B"]) ? $sheetData[$i]["B"] : $this->create_slug($name);
                    $thumbnail = isset($sheetData[$i]["C"]) ? $sheetData[$i]["C"] : "";

                    // BƯỚC QUAN TRỌNG: Kiểm tra trùng Slug trước khi gọi Insert
                    $check = $this->cate->checkDuplicate($slug); 
                    if($check == 0) {
                        $this->cate->categories_insert($name, $slug, $thumbnail);
                        $successCount++;
                    }
                }
                // Dùng SweetAlert2 cho chuyên nghiệp
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    window.onload = function() {
                        Swal.fire('Thành công', 'Đã thêm ' + $successCount + ' danh mục mới', 'success')
                        .then(() => { window.location.href='/web_qlsp/import'; });
                    };
                </script>";
            }
        }
    }

}