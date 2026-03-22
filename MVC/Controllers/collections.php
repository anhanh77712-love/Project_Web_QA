<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
class collections extends controllers
{
    private $collec;

public function __construct()
{
    $this->collec = $this->model("collections_m");
}

public function Get_data()
{
    $this->view('Master', [
        'Page'             => 'collections_v',
        'collections_list' => $this->collec->collections_selectAll(),
        'collec_model'     => $this->collec // <--- THÊM DÒNG NÀY (Truyền model sang View)
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
        header("Location: /web_qlsp/collections");
        exit();
    }
    public function add()
    {
        if (isset($_POST['add_collection'])) {
            $name = $_POST['name'];
            $slug = $_POST['slug'];

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên bộ sưu tập không được để trống'); window.location.href='/web_qlsp/collections';</script>";
                return;
            } else {
                $thumbnail = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
                    $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                    }
                }
                // Insert DB
                $kq = $this->collec->collections_insert($name, $slug, $thumbnail);

                $this->thongBao($kq);
            }
        }
    }
    public function update()
    {
        if (isset($_POST['edit_collection'])) {
            $id        = $_POST['id'];
            $name      = $_POST['name'];
            $slug      = $_POST['slug'];
            $old_image = $_POST['old_image'];

            // Validate
            if (empty($name)) {
                echo "<script>alert('Tên bộ sưu tập không được để trống'); window.location.href='/web_qlsp/collections';</script>";
                return;
            } else {
                                         // Xử lý upload ảnh mới
                $thumbnail = $old_image; // Giữ ảnh cũ
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/collections/";
                    $file_name  = time() . '_' . basename($_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
                        $thumbnail = $file_name;
                    }
                }
                // Update DB
                $kq = $this->collec->collections_update($id, $name, $slug, $thumbnail);

                $this->thongBao($kq);
            }
        }
    }
    public function delete($id)
    {
        $kq = $this->collec->collections_delete($id);
        $this->thongBao($kq);
    }
    public function search()
    {
        if (isset($_POST['btnTimkiem'])) {
            $search = $_POST['txtSearch'];
            $this->view('Master', [
                'Page'             => 'collections_v',
                'collections_list' => $this->collec->collections_select($search),
                'search'           => $search,
            ]);
        } else if (isset($_POST['btnXuat'])) {
            $objExcel = new PHPExcel();
            $objExcel->setActiveSheetIndex(0);
            $sheet = $objExcel->getActiveSheet()->setTitle('Bo_Suu_Tap');

            // 1. Tạo tiêu đề cột dựa trên bảng collections
            $rowCount = 1;
            $sheet->setCellValue('A' . $rowCount, 'ID');
            $sheet->setCellValue('B' . $rowCount, 'TÊN BỘ SƯU TẬP');
            $sheet->setCellValue('C' . $rowCount, 'SLUG');
            $sheet->setCellValue('D' . $rowCount, 'HÌNH ẢNH');

            $sheet->getStyle('A1:D1')->getFont()->setBold(true);

            // 2. Lấy dữ liệu từ hàm collections_select($name) trong Model
            $name = $_POST['txtSearch'] ?? '';
            $data = $this->collec->collections_select($name);

            if ($data) {
                while ($row = mysqli_fetch_array($data)) {
                    $rowCount++;
                    $sheet->setCellValue('A' . $rowCount, $row['id']);
                    $sheet->setCellValue('B' . $rowCount, $row['name']);
                    $sheet->setCellValue('C' . $rowCount, $row['slug']);
                    $sheet->setCellValue('D' . $rowCount, $row['thumbnail']);
                }
            }

            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 3. Xuất file
            $filename = "Bo_Suu_Tap_" . time() . ".xlsx";
            if (ob_get_length()) {
                ob_end_clean();
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
    }
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
            $ext      = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

            // Nếu không lấy được đuôi ảnh, mặc định là jpg
            if (! $ext || ! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
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
   public function importExcelCollections()
{
    if (isset($_POST['btn_import_collection'])) {
        $file = $_FILES['import_file_collection']['tmp_name'];

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

            // --- CẤU HÌNH ĐƯỜNG DẪN ---
            // 1. Đường dẫn vật lý để kiểm tra file (Sử dụng DOCUMENT_ROOT để chính xác nhất)
            // Lưu ý: Thay 'web_qlsp' bằng đúng tên thư mục dự án của bạn nếu khác
            $projectFolder = '/web_qlsp'; 
            $subFolder     = '/Public/Picture/collections/';
            
            // Đường dẫn tuyệt đối trên ổ cứng: C:/xampp/htdocs/web_qlsp/Public/Picture/collections/
            $absPath = $_SERVER['DOCUMENT_ROOT'] . $projectFolder . $subFolder;

            // Tạo thư mục nếu chưa có
            if (!file_exists($absPath)) {
                mkdir($absPath, 0777, true);
            }

            $countSuccess = 0;

            for ($i = 2; $i <= count($sheetData); $i++) {
                // Cột A: Tên Collection
                $name = trim($sheetData[$i]["A"]);
                if (empty($name)) continue;

                $slug = $this->create_slug($name);

                // Cột B: Ảnh
                $rawImg = trim($sheetData[$i]["B"]);
                $thumbnail = 'no-image.jpg'; // Mặc định nếu không tìm thấy

                if (!empty($rawImg)) {
                    // --- TRƯỜNG HỢP 1: LINK ONLINE ---
                    if (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                        // Tải về thư mục $absPath
                        // Hàm download này cần trả về TÊN FILE mới (ví dụ: img_123.jpg)
                        // Bạn cần sửa nhẹ hàm download để trả về basename hoặc xử lý ở đây
                        $savedPath = $this->download_image_from_url($rawImg, $absPath);
                        
                        // $savedPath trả về đường dẫn full, ta chỉ lấy tên file
                        if ($savedPath) {
                            $thumbnail = basename($savedPath); 
                        }
                    } 
                    // --- TRƯỜNG HỢP 2: FILE CÓ SẴN (LOCAL) ---
                    else {
                        // Kiểm tra xem file có nằm trong thư mục collections chưa
                        if (file_exists($absPath . $rawImg)) {
                            // QUAN TRỌNG: Vì View đã có sẵn đường dẫn, ta chỉ lưu TÊN FILE
                            $thumbnail = $rawImg; 
                        } else {
                            // Không tìm thấy file -> Dùng no-image
                            $thumbnail = 'no-image.jpg';
                        }
                    }
                }

                // Gọi Model insert
                $insertResult = $this->collec->collections_insert($name, $slug, $thumbnail);

                if ($insertResult) {
                    $countSuccess++;
                }
            }

            // Thông báo kết quả
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Hoàn tất!',
                        html: 'Đã nhập thành công <b>$countSuccess</b> bộ sưu tập.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'index.php?act=list_collections';
                    });
                };
            </script>";

        } catch (Exception $e) {
            echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }
}
}
