<?php
class warehouse_m extends connectDB {
    function __construct() { parent::__construct(); }

    private function hasColumn($table, $column) {
        $table = mysqli_real_escape_string($this->con, $table);
        $column = mysqli_real_escape_string($this->con, $column);
        $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
        $res = mysqli_query($this->con, $sql);
        return $res && mysqli_num_rows($res) > 0;
    }

    // Categories for filter
    function categories_selectAll() {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $res = mysqli_query($this->con, $sql);
        $out = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) { $out[] = $row; }
        }
        return $out;
    }

    // Core list query for warehouse items (products with optional variants)
    function warehouse_selectItems($q = '', $category_id = '', $status = '') {

    // Ngưỡng cảnh báo tồn kho
    $thresholdExpr = 5;

    // Luôn dùng base_price làm giá
    $costExpr = 'COALESCE(p.base_price, 0)';

    // Câu SQL chính
    $sql = "SELECT 
                p.id AS product_id,
                p.name AS product_name,
                p.base_price,
                $costExpr AS cost_price,
                p.thumbnail,
                c.name AS category_name,
                pv.id AS variant_id,
                pv.color,
                pv.size,
                COALESCE(pv.stock, 0) AS stock_quantity,
                0 AS reserved_quantity,
                $thresholdExpr AS threshold

            FROM products p

            LEFT JOIN product_variants pv 
                ON pv.product_id = p.id

            LEFT JOIN categories c 
                ON p.category_id = c.id

            WHERE 1=1";

    // =========================
    // FILTER CATEGORY
    // =========================
    if (!empty($category_id)) {

        $category_id = intval($category_id);

        $sql .= " AND p.category_id = $category_id";
    }

    // =========================
    // FILTER SEARCH
    // =========================
    if (!empty($q)) {

        $q = mysqli_real_escape_string($this->con, $q);

        $like = "%$q%";

        $sql .= " AND p.name LIKE '$like'";
    }

    // =========================
    // FILTER STATUS
    // =========================
    if (!empty($status)) {

        // Hết hàng
        if ($status === 'out') {

            $sql .= " AND COALESCE(pv.stock, 0) = 0";
        }

        // Sắp hết
        elseif ($status === 'low') {

            $sql .= "
                AND COALESCE(pv.stock, 0) > 0
                AND COALESCE(pv.stock, 0) <= $thresholdExpr
            ";
        }

        // Còn nhiều hàng
        elseif ($status === 'ok') {

            $sql .= "
                AND COALESCE(pv.stock, 0) > $thresholdExpr
            ";
        }
    }

    $sql .= " ORDER BY p.name ASC, pv.id ASC";

   
    $res = mysqli_query($this->con, $sql);

    $items = [];

    if ($res) {

        while ($row = mysqli_fetch_assoc($res)) {

            $items[] = $row;
        }
    }

    return $items;
}

    // Adjust stock by delta (variant required as products table has no stock column)
    function warehouse_adjustStock($product_id, $variant_id, $delta) {
        $product_id = intval($product_id);
        $delta = intval($delta);
        if (!empty($variant_id)) {
            $variant_id = intval($variant_id);
            $sql = "UPDATE product_variants SET stock = GREATEST(stock + $delta, 0) WHERE id = $variant_id AND product_id = $product_id";
            return mysqli_query($this->con, $sql);
        } else {
            // No product-level stock; require variant
            return false;
        }
    }


    
}
?>