<?php
class campaigns_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    
    function campaigns_selectAll() {
        $sql = "SELECT * FROM home_sections ORDER BY display_order ASC";
        return mysqli_query($this->con, $sql);
    }
    
    // Thêm campaign mới
    function campaigns_insert($title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position) {
        // Xử lý NULL cho các trường nullable
        $collection_id_val = $collection_id ? $collection_id : "NULL";
        $image_url_val = $image_url ? "'$image_url'" : "NULL";
        $link_url_val = $link_url ? "'$link_url'" : "NULL";
        $end_time_val = $end_time ? "'$end_time'" : "NULL";
        $button_text_val = $button_text ? "'$button_text'" : "NULL";
        $text_position_val = $text_position ? "'$text_position'" : "NULL";
        
        $sql = "INSERT INTO home_sections (title, section_type, collection_id, image_url, link_url, bg_color, text_color, end_time, display_order, status, button_text, text_position) 
                VALUES ('$title', '$section_type', $collection_id_val, $image_url_val, $link_url_val, '$bg_color', '$text_color', $end_time_val, $display_order, $status, $button_text_val, $text_position_val)";
        return mysqli_query($this->con, $sql);
    }
    
    // Cập nhật campaign
    function campaigns_update($id, $title, $section_type, $collection_id, $image_url, $link_url, $bg_color, $text_color, $end_time, $display_order, $status, $button_text, $text_position) {
        // Xử lý NULL cho các trường nullable
        $collection_id_val = $collection_id ? $collection_id : "NULL";
        $image_url_val = $image_url ? "'$image_url'" : "NULL";
        $link_url_val = $link_url ? "'$link_url'" : "NULL";
        $end_time_val = $end_time ? "'$end_time'" : "NULL";
        $button_text_val = $button_text ? "'$button_text'" : "NULL";
        $text_position_val = $text_position ? "'$text_position'" : "NULL";
        
        $sql = "UPDATE home_sections 
                SET title='$title', section_type='$section_type', collection_id=$collection_id_val, 
                    image_url=$image_url_val, link_url=$link_url_val, bg_color='$bg_color', 
                    text_color='$text_color', end_time=$end_time_val, display_order=$display_order, 
                    status=$status, button_text=$button_text_val, text_position=$text_position_val 
                WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Xóa campaign
    function campaigns_delete($id) {
        $sql = "DELETE FROM home_sections WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy campaign theo ID
    function campaigns_selectById($id) {
        $sql = "SELECT * FROM home_sections WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Toggle trạng thái
    function campaigns_toggleStatus($id) {
        $sql = "UPDATE home_sections SET status = NOT status WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
}