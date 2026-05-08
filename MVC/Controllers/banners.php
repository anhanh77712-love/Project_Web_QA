<?php
class banners extends controllers
{
    private $banner;
    
    public function __construct()
    {
                parent::__construct();

        $this->banner = $this->model('banners_m');
    }

    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Trả về giao diện rỗng)
    public function Get_data()
    {
        // Gọi file Master và nhúng file banners_v vào, KHÔNG truyền data gì cả
        $this->view('Master', [
            'Page' => 'banners_v'
        ]);
    }

    // Thiết lập Header dùng chung cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 2. LUỒNG DÀNH CHO JAVASCRIPT / APP MOBILE (Trả về API JSON)
    // Mình đổi tên hàm này thành api_get_data cho rõ ràng
    public function api_get_data()
    {
        $this->setApiHeader();
        $result = $this->banner->banners_selectAll();
        $data = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // API: Thêm Banner mới (Nhận FormData từ JS)
    public function add()
    {
        $this->setApiHeader();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận phương thức POST']);
            exit;
        }

        $title         = $_POST['title'] ?? '';
        $link_url      = $_POST['link_url'] ?? '';
        $display_order = $_POST['display_order'] ?? 0;

        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
            exit;
        }

        $image_url = '';
        if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/";
            // Tạo thư mục nếu chưa có
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); 
            
            $file_name  = time() . '_' . basename($_FILES["image_url"]["name"]);
            if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_dir . $file_name)) {
                $image_url = $file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi tải ảnh lên server']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hình ảnh']);
            exit;
        }

        $kq = $this->banner->banners_insert($title, $image_url, $link_url, $display_order);

        if ($kq) {
            echo json_encode(['success' => true, 'message' => 'Thêm banner thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu']);
        }
        exit;
    }

    // API: Ẩn/Hiện banner
    public function toggle($id)
    {
        $this->setApiHeader();
        $kq = $this->banner->banners_toggleStatus($id);
        
        if ($kq) {
            echo json_encode(['success' => true, 'message' => 'Đã thay đổi trạng thái']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi đổi trạng thái']);
        }
        exit;
    }

    // API: Cập nhật banner
    public function update()
    {
        $this->setApiHeader();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận phương thức POST']);
            exit;
        }

        $id            = $_POST['id'] ?? '';
        $title         = $_POST['title'] ?? '';
        $link_url      = $_POST['link_url'] ?? '';
        $display_order = $_POST['display_order'] ?? 0;
        $old_image     = $_POST['old_image'] ?? '';
        
        $image_url = $old_image; // Mặc định giữ ảnh cũ

        // Nếu có upload ảnh mới
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

        $kq = $this->banner->banners_update($id, $title, $image_url, $link_url, $display_order);

        if ($kq) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
        exit;
    }

    // API: Xóa banner
    public function delete($id)
    {
        $this->setApiHeader();
        
        $result = $this->banner->banners_selectById($id);
        $banner = mysqli_fetch_assoc($result);

        if ($banner) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/banners/" . $banner['image_url'];
            if (file_exists($file_path) && $banner['image_url'] != '') {
                unlink($file_path);
            }

            $kq = $this->banner->banners_delete($id);

            if ($kq) {
                echo json_encode(['success' => true, 'message' => 'Đã xóa banner']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy banner']);
        }
        exit;
    }
}
?>