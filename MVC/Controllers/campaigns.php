<?php
class campaigns extends controllers {
    private $campaign;
    private $collection;
    
    function __construct() {
                parent::__construct();

        $this->campaign = $this->model('campaigns_m');
        $this->collection = $this->model('collections_m');
    }
    
    // 1. LUỒNG DÀNH CHO TRÌNH DUYỆT WEB (Chỉ tải giao diện rỗng)
    function Get_data() {
        $this->view('Master', [
            'Page' => 'campaigns_v'
        ]);
    }

    // Thiết lập Header cho API
    private function setApiHeader() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    }

    // 2. LUỒNG API DÀNH CHO JAVASCRIPT (Trả về cả Campaigns và Collections)
    function api_get_data() {
        $this->setApiHeader();

        // Lấy danh sách Campaigns
        $campaigns_result = $this->campaign->campaigns_selectAll();
        $campaigns = [];
        if ($campaigns_result && mysqli_num_rows($campaigns_result) > 0) {
            while ($row = mysqli_fetch_assoc($campaigns_result)) {
                $campaigns[] = $row;
            }
        }

        // Lấy danh sách Collections (để đổ vào dropdown)
        $collections_result = $this->collection->collections_selectAll();
        $collections = [];
        if ($collections_result && mysqli_num_rows($collections_result) > 0) {
            while ($row = mysqli_fetch_assoc($collections_result)) {
                $collections[] = $row;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'campaigns' => $campaigns,
                'collections' => $collections
            ]
        ]);
        exit;
    }
    
    // API: Thêm campaign mới
    function add() {
        $this->setApiHeader();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']); exit;
        }

        $title = $_POST['title'] ?? '';
        $section_type = $_POST['type'] ?? '';
        $bg_color = $_POST['bg_color'] ?? '#ffffff';
        $text_color = $_POST['text_color'] ?? '#000000';
        $display_order = $_POST['display_order'] ?? 0;
        $status = isset($_POST['is_active']) ? 1 : 0; // Nếu JS gửi 'is_active' dạng on/true
        
        $collection_id = null;
        $image_url = null;
        $link_url = null;
        $end_time = null;
        $button_text = null;
        $text_position = null;
        
        // Xử lý upload ảnh banner
        if($section_type == 'overlay_banner') {
            if(isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/";
                if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
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
        
        $kq = $this->campaign->campaigns_insert($title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
        
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Thêm thành công' : 'Lỗi thêm dữ liệu']);
        exit;
    }
    
    // API: Cập nhật campaign
    function update() {
        $this->setApiHeader();
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST']); exit;
        }

        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $section_type = $_POST['type'] ?? '';
        $bg_color = $_POST['bg_color'] ?? '#ffffff';
        $text_color = $_POST['text_color'] ?? '#000000';
        $display_order = $_POST['display_order'] ?? 0;
        $status = isset($_POST['is_active']) ? 1 : 0;
        
        $collection_id = null;
        $image_url = $_POST['old_banner_image'] ?? null;
        $link_url = null;
        $end_time = null;
        $button_text = null;
        $text_position = null;
        
        if($section_type == 'overlay_banner') {
            if(isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/";
                $file_name = time() . '_' . basename($_FILES["banner_image"]["name"]);
                if(move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_dir . $file_name)) {
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
        
        $kq = $this->campaign->campaigns_update($id, $title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
        
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Cập nhật thành công' : 'Lỗi cập nhật dữ liệu']);
        exit;
    }
    
    // API: Xóa campaign
    function delete($id) {
        $this->setApiHeader();
        $result = $this->campaign->campaigns_selectById($id);
        $campaign = mysqli_fetch_assoc($result);
        
        if($campaign && $campaign['image_url']) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/" . $campaign['image_url'];
            if(file_exists($file_path)) unlink($file_path);
        }
        
        $kq = $this->campaign->campaigns_delete($id);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã xóa thành công' : 'Lỗi khi xóa']);
        exit;
    }
    
    // API: Toggle trạng thái
    function toggle($id) {
        $this->setApiHeader();
        $kq = $this->campaign->campaigns_toggleStatus($id);
        echo json_encode(['success' => $kq, 'message' => $kq ? 'Đã thay đổi trạng thái' : 'Lỗi thay đổi trạng thái']);
        exit;
    }
}
?>