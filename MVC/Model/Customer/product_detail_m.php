<?php
class product_detail_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    
    // Lấy các biến thể (màu, kích thước) theo sản phẩm
    function get_variants_by_product($product_id) {
        $product_id = (int)$product_id;
        // Include stock so UI can mark sold-out sizes
        $sql = "SELECT id, color, size, stock FROM product_variants WHERE product_id = $product_id ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy ảnh theo variant
    function get_images_by_variant($variant_id) {
        $variant_id = (int)$variant_id;
        $sql = "SELECT image_url FROM product_images WHERE variant_id = $variant_id ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }

    // Lấy biến thể theo ID
    function variant_getById($variant_id) {
        $variant_id = (int)$variant_id;
        $sql = "SELECT id, product_id, color, size, stock FROM product_variants WHERE id = $variant_id LIMIT 1";
        $result = mysqli_query($this->con, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    // Kiểm tra tồn kho theo biến thể
    function variant_checkStock($variant_id, $quantity) {
        $variant_id = (int)$variant_id;
        $quantity = (int)$quantity;
        $sql = "SELECT stock FROM product_variants WHERE id = $variant_id";
        $result = mysqli_query($this->con, $sql);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        if (!$row) return false;
        return (int)$row['stock'] >= $quantity;
    }

    // Trừ tồn kho theo biến thể
    function variant_updateStock($variant_id, $quantity) {
        $variant_id = (int)$variant_id;
        $quantity = (int)$quantity;
        $sql = "UPDATE product_variants SET stock = GREATEST(stock - $quantity, 0) WHERE id = $variant_id";
        return mysqli_query($this->con, $sql);
    }
    
    // Map tên màu -> mã hex
    function get_color_hex($color) {
        $map = [
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
        return isset($map[$color]) ? $map[$color] : '#6C757D';
    }
    
    // Lấy chi tiết sản phẩm theo slug
    function product_selectBySlug($slug) {
        $slug = mysqli_real_escape_string($this->con, $slug);
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       c.slug as category_slug,
                       col.name as collection_name, 
                       col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.slug = '$slug'
                LIMIT 1";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm liên quan (cùng category, trừ sản phẩm hiện tại)
    function products_selectRelated($category_id, $current_product_id, $limit = 8) {
        $category_id = intval($category_id);
        $current_product_id = intval($current_product_id);
        $limit = intval($limit);
        
        $sql = "SELECT p.id, p.name, p.slug, p.base_price, p.thumbnail,
                       c.name as category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = $category_id 
                  AND p.id != $current_product_id
                ORDER BY p.views DESC, p.created_at DESC
                LIMIT $limit";
        return mysqli_query($this->con, $sql);
    }
    
    // Tăng lượt xem sản phẩm
    function product_increaseViews($product_id) {
        $product_id = intval($product_id);
        $sql = "UPDATE products SET views = views + 1 WHERE id = $product_id";
        return mysqli_query($this->con, $sql);
    }
}
