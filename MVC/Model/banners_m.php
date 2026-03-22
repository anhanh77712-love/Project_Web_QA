<?php
class banners_m extends connectDB {
    function __construct() {
        parent::__construct();
    }

    function banners_selectAll() {
        $sql = "SELECT * FROM banners ORDER BY id DESC";
        return mysqli_query($this->con, $sql);
    }
    // Add banner
    function banners_insert($title, $image_url, $link_url, $display_order) {
        $sql = "INSERT INTO banners (title, image_url, link_url, display_order, status) VALUES ('$title', '$image_url', '$link_url', $display_order, 1)";
        return mysqli_query($this->con, $sql);
    }
    
    // Update banner
    function banners_update($id, $title, $image_url, $link_url, $display_order) {
        $sql = "UPDATE banners SET title='$title', image_url='$image_url', link_url='$link_url', display_order=$display_order WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Delete banner
    function banners_delete($id) {
        $sql = "DELETE FROM banners WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Toggle status (ẩn/hiện)
    function banners_toggleStatus($id) {
        $sql = "UPDATE banners SET status = NOT status WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    
    // Get banner by ID
    function banners_selectById($id) {
        $sql = "SELECT * FROM banners WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
}