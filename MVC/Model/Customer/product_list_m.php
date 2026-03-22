<?php
class product_list_m extends connectDB {
    function __construct() {
        parent::__construct();
    }
    
    // Lấy tất cả sản phẩm
    function products_selectAll($sort = 'default') {
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm theo category slug
    function products_selectByCategory($category_slug, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE c.slug = '$category_slug'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm theo collection slug
    function products_selectByCollection($collection_slug, $sort = 'default') {
        $collection_slug = mysqli_real_escape_string($this->con, $collection_slug);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                INNER JOIN collections col ON p.collection_id = col.id 
                WHERE col.slug = '$collection_slug'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }

    // Lấy sản phẩm theo collection + category
    function products_selectByCollectionAndCategory($collection_slug, $category_slug, $sort = 'default') {
        $collection_slug = mysqli_real_escape_string($this->con, $collection_slug);
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender,
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.id
                INNER JOIN collections col ON p.collection_id = col.id
                WHERE col.slug = '$collection_slug' AND c.slug = '$category_slug'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }

    // Lấy sản phẩm theo collection + khoảng giá
    function products_selectByCollectionAndPriceRange($collection_slug, $min_price, $max_price, $sort = 'default') {
        $collection_slug = mysqli_real_escape_string($this->con, $collection_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender,
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                INNER JOIN collections col ON p.collection_id = col.id
                WHERE col.slug = '$collection_slug'
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }

    // Lấy sản phẩm theo collection + category + khoảng giá
    function products_selectByCollectionCategoryAndPrice($collection_slug, $category_slug, $min_price, $max_price, $sort = 'default') {
        $collection_slug = mysqli_real_escape_string($this->con, $collection_slug);
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender,
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p
                INNER JOIN categories c ON p.category_id = c.id
                INNER JOIN collections col ON p.collection_id = col.id
                WHERE col.slug = '$collection_slug' AND c.slug = '$category_slug'
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm theo khoảng giá
    function products_selectByPriceRange($min_price, $max_price, $sort = 'default') {
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.base_price >= $min_price AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm theo cả category và giá
    function products_selectByCategoryAndPrice($category_slug, $min_price, $max_price, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE c.slug = '$category_slug' 
                  AND p.base_price >= $min_price 
                  AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Helper function để lấy ORDER BY clause
    private function getSortOrder($sort) {
        switch($sort) {
            case 'price_asc':
                return "ORDER BY p.base_price ASC";
            case 'price_desc':
                return "ORDER BY p.base_price DESC";
            default:
                return "ORDER BY p.created_at DESC, p.id DESC";
        }
    }
    
    // Tìm kiếm sản phẩm theo từ khóa
    function products_searchByKeyword($keyword, $sort = 'default') {
        $keyword = mysqli_real_escape_string($this->con, $keyword);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.name LIKE '%$keyword%' OR p.description LIKE '%$keyword%'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Tìm kiếm + lọc theo category
    function products_searchByCategoryAndKeyword($category_slug, $keyword, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $keyword = mysqli_real_escape_string($this->con, $keyword);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE c.slug = '$category_slug'
                  AND (p.name LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Tìm kiếm + lọc theo price
    function products_searchByPriceAndKeyword($min_price, $max_price, $keyword, $sort = 'default') {
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $keyword = mysqli_real_escape_string($this->con, $keyword);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.base_price >= $min_price AND p.base_price <= $max_price
                  AND (p.name LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Tìm kiếm + lọc theo category + price
    function products_searchByCategoryPriceAndKeyword($category_slug, $min_price, $max_price, $keyword, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $keyword = mysqli_real_escape_string($this->con, $keyword);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE c.slug = '$category_slug'
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                  AND (p.name LIKE '%$keyword%' OR p.description LIKE '%$keyword%')
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lọc sản phẩm mới (trong 7 ngày qua)
    function products_selectNew($sort = 'default') {
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       p.created_at,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lọc sản phẩm mới + category
    function products_selectNewByCategory($category_slug, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       p.created_at,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND c.slug = '$category_slug'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lọc sản phẩm mới + price
    function products_selectNewByPrice($min_price, $max_price, $sort = 'default') {
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       p.created_at,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lọc sản phẩm mới + category + price
    function products_selectNewByCategoryAndPrice($category_slug, $min_price, $max_price, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
                $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       p.created_at,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND c.slug = '$category_slug'
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm bán chạy (bestseller)
    function products_selectBestseller($sort = 'default') {
        $orderBy = $this->getSortOrder($sort);
        // Chỉ lấy từ order đã completed
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug,
                       COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('completed', 'shipping', 'delivered')
                GROUP BY p.id
                HAVING total_sold > 0
                ORDER BY total_sold DESC, p.id DESC
                LIMIT 50";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm bán chạy + category
    function products_selectBestsellerByCategory($category_slug, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug,
                       COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('completed', 'shipping', 'delivered')
                WHERE c.slug = '$category_slug'
                GROUP BY p.id
                HAVING total_sold > 0
                ORDER BY total_sold DESC, p.id DESC
                LIMIT 50";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm bán chạy + price
    function products_selectBestsellerByPrice($min_price, $max_price, $sort = 'default') {
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug,
                       COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('completed', 'shipping', 'delivered')
                WHERE p.base_price >= $min_price AND p.base_price <= $max_price
                GROUP BY p.id
                HAVING total_sold > 0
                ORDER BY total_sold DESC, p.id DESC
                LIMIT 50";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy sản phẩm bán chạy + category + price
    function products_selectBestsellerByCategoryAndPrice($category_slug, $min_price, $max_price, $sort = 'default') {
        $category_slug = mysqli_real_escape_string($this->con, $category_slug);
        $min_price = intval($min_price);
        $max_price = intval($max_price);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug,
                       COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('completed', 'shipping', 'delivered')
                WHERE c.slug = '$category_slug'
                  AND p.base_price >= $min_price AND p.base_price <= $max_price
                GROUP BY p.id
                HAVING total_sold > 0
                ORDER BY total_sold DESC, p.id DESC
                LIMIT 50";
        return mysqli_query($this->con, $sql);
    }
    
    // Lấy tất cả categories có status = 1
    function categories_selectAll() {
        $sql = "SELECT id, name, slug, thumbnail FROM categories WHERE status = 1 ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }
    function products_searchByKeywordPrefix($keyword, $sort = 'default') {
        $keyword = mysqli_real_escape_string($this->con, $keyword);
        $orderBy = $this->getSortOrder($sort);
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.base_price, p.is_sale, p.gender, 
                       p.thumbnail, p.views, p.category_id, p.collection_id,
                       c.name as category_name, c.slug as category_slug,
                       col.name as collection_name, col.slug as collection_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN collections col ON p.collection_id = col.id 
                WHERE p.name LIKE '$keyword%'
                $orderBy";
        return mysqli_query($this->con, $sql);
    }
}