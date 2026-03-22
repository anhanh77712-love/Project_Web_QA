<?php
class home_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    
    function categories_selectAll() {
        $sql = "SELECT * FROM categories ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function banners_getActive() {
        $sql = "SELECT * FROM banners WHERE status=1 ORDER BY display_order ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function sections_getActive() {
        $sql = "SELECT * FROM home_sections WHERE status=1 ORDER BY display_order ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function products_getByCollection($collection_id, $limit = 4) {
        $sql = "SELECT *, base_price as base_price, (base_price * 0.9) as sale_price FROM products WHERE collection_id=$collection_id LIMIT $limit";
        return mysqli_query($this->con, $sql);
    }
    
    function collection_getSlug($collection_id) {
        $sql = "SELECT slug FROM collections WHERE id=$collection_id";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['slug'] : '';
    }
    
    function get_variants_by_product($product_id) {
        $product_id = (int)$product_id;
        $sql = "SELECT DISTINCT color, size, id FROM product_variants WHERE product_id = $product_id ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function get_images_by_variant($variant_id) {
        $variant_id = (int)$variant_id;
        $sql = "SELECT image_url FROM product_images WHERE variant_id = $variant_id ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    function get_color_hex($color) {
        $colors = [
            'Đen' => '#000000',
            'Trắng' => '#FFFFFF',
            'Đỏ' => '#DC3545',
            'Xanh' => '#0D6EFD',
            'Vàng' => '#FFC107',
            'Hồng' => '#E83E8C',
            'Xám' => '#6C757D',
            'Nâu' => '#8B4513',
            'Tím' => '#6F42C1',
            'Cam' => '#FD7E14'
        ];
        return isset($colors[$color]) ? $colors[$color] : '#6C757D';
    }
}