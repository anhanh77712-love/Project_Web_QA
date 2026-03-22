<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
class categories_list extends controllers{
    private $ctlist;
    function __construct(){
        $this->ctlist=$this->model("categories_m");
    }
    function Get_data(){
        $this->view('Master',[
            'Page'=>'categories_list_v',
            'categories_list'=>$this->ctlist->categories_selectAll()
        ]);
    }

    function thongBao($kq){
        if (session_status() === PHP_SESSION_NONE) session_start();
                 if ($kq) {
                        $_SESSION['status_msg'] = "success";
                    } else {
                        $_SESSION['status_msg'] = "error";
                    }

                    // 3. Quay trở lại trang danh sách
                    header("Location: /web_qlsp/categories_list");
                    exit();
    }

    function add(){
        if(isset($_POST['add_category'])){
            $name = $_POST['name'];
            $slug = $_POST['slug'];

            // Validate
            if(empty($name)){
                echo "<script>alert('Tên danh mục không được để trống'); window.location.href='/web_qlsp/categories_list';</script>";
                return;
            } else {
                $thumbnail = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/categories/";
                    $file_name = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                    }
                }
                // Insert DB
                $kq = $this->ctlist->categories_insert($name, $slug, $thumbnail);

                $this->thongBao($kq);
            }
        }
    }
    function update(){
        if(isset($_POST['edit_category'])){
            $id = $_POST['id'];
            $name = $_POST['name'];
            $slug = $_POST['slug'];
            $old_image = $_POST['old_image'];

            // Validate
            if(empty($name)){
                echo "<script>alert('Tên danh mục không được để trống'); window.location.href='/web_qlsp/categories_list';</script>";
                return;
            } else {
                // Xử lý upload ảnh mới
                $thumbnail = $old_image; // Giữ ảnh cũ
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/categories/";
                    $file_name = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                        // Xóa ảnh cũ nếu có
                        if(!empty($old_image) && file_exists($target_dir . $old_image)){
                            unlink($target_dir . $old_image);
                        }
                    }
                }

                // Update DB
                $kq = $this->ctlist->categories_update($id, $name, $slug, $thumbnail);

                $this->thongBao($kq);
            }
        }
    }
    function delete($id){
        $kq = $this->ctlist->categories_delete($id);
        $this->thongBao($kq);
    }
    function search(){
        if(isset($_POST['btnTimkiem'])){
            $search=$_POST['txtSearch'];
            $this->view('Master',[
                'Page'=>'categories_list_v',
                'categories_list'=>$this->ctlist->categories_select($search),
                'search'=>$search
            ]);
        }
        else if(isset($_POST['btnXuat'])){
            // Khởi tạo PHPExcel (Đã được nạp từ bridge.php)
            $objExcel = new PHPExcel();
            $objExcel->setActiveSheetIndex(0);
            $sheet = $objExcel->getActiveSheet()->setTitle('Danh_Muc_San_Pham');

            // 1. Tạo tiêu đề cột dựa trên bảng categories
            $rowCount = 1;
            $sheet->setCellValue('A'.$rowCount, 'ID');
            $sheet->setCellValue('B'.$rowCount, 'TÊN DANH MỤC');
            $sheet->setCellValue('C'.$rowCount, 'SLUG');
            $sheet->setCellValue('D'.$rowCount, 'HÌNH ẢNH');
           
            
            // Định dạng in đậm tiêu đề
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);

            // 2. Lấy dữ liệu từ hàm categories_select($name) trong Model của bạn
            $name = $_POST['txtSearch'] ?? ''; 
            $data = $this->ctlist->categories_select($name); 

            if($data){
                while($row = mysqli_fetch_array($data)){
                    $rowCount++;
                    $sheet->setCellValue('A'.$rowCount, $row['id']);
                    $sheet->setCellValue('B'.$rowCount, $row['name']);
                    $sheet->setCellValue('C'.$rowCount, $row['slug']);
                    $sheet->setCellValue('D'.$rowCount, $row['thumbnail']);
                }
            }

            // Tự động giãn chiều rộng cột
            foreach(range('A','D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 3. Xuất file
            $filename = "Danh_Muc_" . time() . ".xlsx";
            if (ob_get_length()) ob_end_clean();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }

    }
    // --- HÀM 1: TẠO SLUG (Giữ nguyên của bạn) ---
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

    // --- HÀM 2: TẢI ẢNH TỪ URL (Giữ nguyên logic của bạn) ---
    private function download_image_from_url($url, $saveFolder)
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        try {
            // Lưu ý: Cần bật allow_url_fopen trong php.ini
            $imageContent = @file_get_contents($url); // Thêm @ để tránh warning ra màn hình
            if ($imageContent === false) {
                return '';
            }

            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
            
            // Nếu không lấy được đuôi ảnh, mặc định là jpg
            if (!$ext || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $ext = 'jpg';
            }

            // Đặt tên file: cat_time_random.ext
            $newFileName = 'cat_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

            // Đường dẫn lưu file
            $savePath = $saveFolder . $newFileName;

            file_put_contents($savePath, $imageContent);
            return $savePath; // Trả về đường dẫn để lưu DB
        } catch (Exception $e) {
            return '';
        }
    }

    // --- HÀM 3: IMPORT EXCEL DANH MỤC ---
    public function importExcelCat()
    {
        // Kiểm tra nút bấm từ Modal (name="btn_import" ở phần View trước)
        if (isset($_POST['btn_import'])) {
            $file = $_FILES['import_file']['tmp_name'];

            if (empty($file)) {
                echo "<script>alert('Vui lòng chọn file!'); window.history.back();</script>";
                return;
            }

            try {
                // 1. Gọi thư viện PHPExcel (Theo đường dẫn của bạn)
                require_once __DIR__ . '/../../Public/Classes/PHPExcel/IOFactory.php';
                $objReader = PHPExcel_IOFactory::createReaderForFile($file);
                $objExcel  = $objReader->load($file);
                $sheet     = $objExcel->getSheet(0);
                $sheetData = $sheet->toArray(null, true, true, true);

                // 2. Cấu hình thư mục lưu ảnh danh mục
                // Tạo thư mục này trong dự án nếu chưa có: Public/Picture/categories/
                $uploadFolder = ''; 
                
                // Nếu thư mục chưa tồn tại thì tạo mới
                // if (!file_exists($uploadFolder)) {
                //     mkdir($uploadFolder, 0777, true);
                // }

                $countSuccess = 0;

                // 3. Duyệt từ dòng 2 (Bỏ tiêu đề)
                // Cấu trúc Excel: Cột A = Tên, Cột B = Link Ảnh/Tên Ảnh
                for ($i = 2; $i <= count($sheetData); $i++) {
                    
                    // Lấy Tên Danh Mục (Cột A)
                    $name = trim($sheetData[$i]["A"]);
                    if (empty($name)) {
                        continue; // Bỏ qua nếu tên rỗng
                    }

                    // Tự động tạo Slug
                    $slug = $this->create_slug($name);

                    // Lấy Ảnh (Cột B)
                    $rawImg = trim($sheetData[$i]["B"]);
                    $thumbnail = '';

                    if (!empty($rawImg)) {
                        if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                            // Nếu là Link Online -> Tải về
                            $savedPath = $this->download_image_from_url($rawImg, $uploadFolder);
                            if ($savedPath) {
                                $thumbnail = $savedPath;
                            }
                        } else {
                            // Nếu là tên file có sẵn -> Ghép đường dẫn
                            $thumbnail = $uploadFolder . $rawImg;
                        }
                    } else {
                        // Nếu không có ảnh, gán ảnh mặc định (nếu muốn)
                        $thumbnail = 'Public/Picture/no-image.jpg';
                    }

                    // 4. Gọi Model để Insert
                    // Gọi hàm insert bạn đã cung cấp ở prompt đầu tiên
                    // Giả sử $this->model là đối tượng Model của bạn
                    $insertResult = $this->ctlist->categories_insert($name, $slug, $thumbnail);

                    if ($insertResult) {
                        $countSuccess++;
                    }
                }

                // 5. Thông báo SweetAlert2
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    window.onload = function() {
                        Swal.fire({
                            title: 'Thành công!',
                            html: 'Đã nhập được <b>$countSuccess</b> danh mục mới.',
                            icon: 'success'
                        }).then(() => { 
                            // Chuyển hướng về trang danh sách danh mục
                            window.location.href = 'index.php?act=list_categories'; 
                        });
                    };
                </script>";

            } catch (Exception $e) {
                echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "'); window.history.back();</script>";
            }
        }
    }


}