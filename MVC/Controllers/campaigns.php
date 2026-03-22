<?php
class campaigns extends controllers {
    private $campaign;
    private $collection;
    
    function __construct() {
        $this->campaign = $this->model('campaigns_m');
        $this->collection = $this->model('collections_m');
    }
    
    function Get_data() {
        $this->view('Master', [
            'Page' => 'campaigns_v',
            'campaigns_list' => $this->campaign->campaigns_selectAll(),
            'collections_list' => $this->collection->collections_selectAll()
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
                    header("Location: /web_qlsp/campaigns");
                    exit();
    }
    // Thêm campaign mới
    function add() {
        if(isset($_POST['add_campaign'])) {
            $title = $_POST['title'];
            $section_type = $_POST['type'];
            $bg_color = $_POST['bg_color'];
            $text_color = $_POST['text_color'];
            $display_order = $_POST['display_order'];
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
            
            $kq = $this->campaign->campaigns_insert($title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
            
            $this->thongBao($kq);
        }
    }
    
    // Cập nhật campaign
    function update() {
        if(isset($_POST['update_campaign'])) {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $section_type = $_POST['type'];
            $bg_color = $_POST['bg_color'];
            $text_color = $_POST['text_color'];
            $display_order = $_POST['display_order'];
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
            
            $kq = $this->campaign->campaigns_update($id, $title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position);
            
            $this->thongBao($kq);
        }
    }
    
    // Xóa campaign
    function delete($id) {
        // Lấy thông tin ảnh trước khi xóa
        $result = $this->campaign->campaigns_selectById($id);
        $campaign = mysqli_fetch_assoc($result);
        
        if($campaign && $campaign['image_url']) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/web_qlsp/Public/Picture/campaigns/" . $campaign['image_url'];
            if(file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $kq = $this->campaign->campaigns_delete($id);
        
        $this->thongBao($kq);
    }
    
    // Toggle trạng thái
    function toggle($id) {
        $kq = $this->campaign->campaigns_toggleStatus($id);
        if($kq) {
            header("Location: /web_qlsp/campaigns");
            exit;
        }
    }
}
