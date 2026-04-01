<?php
class campaigns_api extends controllers {
    private $campaigns_model;

    function __construct() {
        $this->campaigns_model = $this->model('campaigns_m');
    }

    // API GET: /web_qlsp/api/campaigns_api/get_all
    function get_all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET');

        $result = $this->campaigns_model->campaigns_selectAll();
        $list = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $list[] = $row;
            }
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $list]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không có chiến dịch nào']);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ FORM & CHUYỂN TRANG (REDIRECT)
    // ==========================================

    // Hàm hỗ trợ gán session thông báo và chuyển trang
    private function thongBao($kq) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($kq) {
            $_SESSION['status_msg'] = "success";
        } else {
            $_SESSION['status_msg'] = "error";
        }

        // Quay trở lại trang danh sách
        header("Location: /web_qlsp/campaigns");
        exit();
    }

    // Thêm campaign mới
    function add() {
        // Lấy dữ liệu thay vì bọc trong if(isset($_POST['add_campaign'])) để API linh hoạt hơn
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $section_type = isset($_POST['type']) ? $_POST['type'] : '';
        $bg_color = isset($_POST['bg_color']) ? $_POST['bg_color'] : '';
        $text_color = isset($_POST['text_color']) ? $_POST['text_color'] : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $status = isset($_POST['is_active']) ? 1 : 0;
        
        // Khởi tạo các biến
        $collection_id = null;
        $image_url = null;
        $link_url = null;
        $end_time = null;
        $button_text = null;
        $text_position = null;
        
        // Xử lý theo loại section
        if($section_type == 'overlay_banner') {
            // Xử lý upload ảnh banner
            if(isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/";
                if(!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_name = time() . '_' . basename($_FILES["banner_image"]["name"]);
                if(move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_dir . $file_name)) {
                    $image_url = $file_name;
                }
            }
            $button_text = $_POST['button_text'] ?? 'XEM NGAY';
            $text_position = $_POST['text_position'] ?? 'left';
            $link_url = $_POST['link_url'] ?? '#';
        } elseif($section_type == 'collection' || $section_type == 'flash_sale') {
            $collection_id = !empty($_POST['collection_id']) ? $_POST['collection_id'] : null;
            if($section_type == 'flash_sale') {
                $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
            }
        }
        
        $kq = $this->campaigns_model->campaigns_insert($title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
        
        $this->thongBao($kq);
    }
    
    // Cập nhật campaign
    function update() {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        $section_type = isset($_POST['type']) ? $_POST['type'] : '';
        $bg_color = isset($_POST['bg_color']) ? $_POST['bg_color'] : '';
        $text_color = isset($_POST['text_color']) ? $_POST['text_color'] : '';
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        $status = isset($_POST['is_active']) ? 1 : 0;
        
        // Khởi tạo các biến
        $collection_id = null;
        $image_url = $_POST['old_banner_image'] ?? null;
        $link_url = null;
        $end_time = null;
        $button_text = null;
        $text_position = null;
        
        // Xử lý theo loại section
        if($section_type == 'overlay_banner') {
            // Xử lý upload ảnh mới nếu có
            if(isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/";
                $file_name = time() . '_' . basename($_FILES["banner_image"]["name"]);
                if(move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_dir . $file_name)) {
                    // Xóa ảnh cũ
                    if($image_url && file_exists($target_dir . $image_url)) {
                        unlink($target_dir . $image_url);
                    }
                    $image_url = $file_name;
                }
            }
            $button_text = $_POST['button_text'] ?? 'XEM NGAY';
            $text_position = $_POST['text_position'] ?? 'left';
            $link_url = $_POST['link_url'] ?? '#';
        } elseif($section_type == 'collection' || $section_type == 'flash_sale') {
            $collection_id = !empty($_POST['collection_id']) ? $_POST['collection_id'] : null;
            if($section_type == 'flash_sale') {
                $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
            }
        }
        
        $kq = $this->campaigns_model->campaigns_update($id, $title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
        
        $this->thongBao($kq);
    }
    
    // Xóa campaign
    function delete($id = 0) {
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id > 0) {
            // Lấy thông tin ảnh trước khi xóa
            $result = $this->campaigns_model->campaigns_selectById($id);
            if ($result && mysqli_num_rows($result) > 0) {
                $campaign = mysqli_fetch_assoc($result);
                
                if($campaign && !empty($campaign['image_url'])) {
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/" . $campaign['image_url'];
                    if(file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                $kq = $this->campaigns_model->campaigns_delete($id);
                $this->thongBao($kq);
                return;
            }
        }
        $this->thongBao(false);
    }
    
    // Toggle trạng thái
    function toggle($id = 0) {
        if ($id == 0) {
            $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        }

        if ($id > 0) {
            $kq = $this->campaigns_model->campaigns_toggleStatus($id);
            if($kq) {
                header("Location: /web_qlsp/campaigns");
                exit;
            }
        }
        // Fallback về trang danh sách nếu lỗi
        header("Location: /web_qlsp/campaigns");
        exit;
    }
}
?>