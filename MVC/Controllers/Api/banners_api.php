<?php
// Lưu ý: Đảm bảo session_start() đã được gọi ở file gốc (index.php hoặc core controller)
class banners_api extends controllers {
    private $banners_model;

    function __construct() {
        $this->banners_model = $this->model('banners_m');
    }

    // Hàm hỗ trợ lấy dữ liệu JSON cho các API không có upload file
    private function getRequestData() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return $data ? $data : $_POST;
    }

    // ==========================================
    // CÁC HÀM TRẢ VỀ JSON (Dùng để lấy dữ liệu)
    // ==========================================

    // 1. Lấy danh sách tất cả banner
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->banners_model->banners_selectAll(); 
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không có banner nào']);
        }
    }

    // 2. Lấy chi tiết 1 banner theo ID
    function get_by_id() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID banner']);
            return;
        }

        $result = $this->banners_model->banners_selectById($id);
        if ($result && mysqli_num_rows($result) > 0) {
            $banner = mysqli_fetch_assoc($result);
            echo json_encode(['success' => true, 'data' => $banner]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy banner']);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ FORM & CHUYỂN TRANG (REDIRECT)
    // ==========================================

    // 3. Thêm banner mới
    function insert() {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $link_url = isset($_POST['link_url']) ? $_POST['link_url'] : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

        if (empty($title)) {
            echo "<script>alert('Tiêu đề banner không được để trống'); window.location.href='/web_qlsp/banners';</script>";
            return;
        } else {
            $image_url = '';
            if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/";
                $file_name  = time() . '_' . basename($_FILES["image_url"]["name"]);
                if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_dir . $file_name)) {
                    $image_url = $file_name;
                }
            }
            
            // Insert DB
            $kq = $this->banners_model->banners_insert($title, $image_url, $link_url, $display_order);

            if ($kq) {
                $_SESSION['status'] = 'success';
            } else {
                $_SESSION['status'] = 'error';
            }
            header("Location: /web_qlsp/banners");
            exit;
        }
    }

    // 4. Cập nhật banner
    function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $link_url = isset($_POST['link_url']) ? $_POST['link_url'] : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $old_image = isset($_POST['old_image']) ? $_POST['old_image'] : '';
        
        $image_url = $old_image; // Mặc định giữ ảnh cũ

        // Kiểm tra có upload ảnh mới không
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/";
            $file_name  = time() . '_' . basename($_FILES["image_url"]["name"]);
            if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_dir . $file_name)) {
                $image_url = $file_name;

                // Xóa ảnh cũ
                if (file_exists($target_dir . $old_image) && $old_image != '') {
                    unlink($target_dir . $old_image);
                }
            }
        }

        $kq = $this->banners_model->banners_update($id, $title, $image_url, $link_url, $display_order);

        if ($kq) {
            $_SESSION['status'] = 'success';
        } else {
            $_SESSION['status'] = 'error';
        }
        header("Location: /web_qlsp/banners");
        exit;
    }

    // 5. Xóa banner (nhận ID qua URL hoặc POST đều được, tôi giữ logic lấy từ GET/POST như cũ)
    function delete($id = 0) {
        // Nếu không truyền tham số trên URL, thử lấy từ POST/GET
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id > 0) {
            // Lấy thông tin ảnh trước khi xóa
            $result = $this->banners_model->banners_selectById($id);
            if ($result && mysqli_num_rows($result) > 0) {
                $banner = mysqli_fetch_assoc($result);

                // Xóa file ảnh
                $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/" . $banner['image_url'];
                if (file_exists($file_path) && $banner['image_url'] != '') {
                    unlink($file_path);
                }

                // Xóa record trong DB
                $kq = $this->banners_model->banners_delete($id);

                if ($kq) {
                    $_SESSION['status'] = 'success';
                } else {
                    $_SESSION['status'] = 'error';
                }
            }
        }
        
        header("Location: /web_qlsp/banners");
        exit;
    }

    // 6. Ẩn / Hiện banner
    function toggle_status($id = 0) {
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id > 0) {
            $kq = $this->banners_model->banners_toggleStatus($id);
        }
        
        header("Location: /web_qlsp/banners");
        exit;
    }
}
?>