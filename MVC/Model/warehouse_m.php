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
        $thresholdExpr = $this->hasColumn('products','low_stock_threshold') ? 'COALESCE(p.low_stock_threshold, 5)' : '5';
        // Giá nhập lấy theo biến thể: product_variants.input_price nếu có; fallback giá tại products
        if ($this->hasColumn('product_variants','input_price')) {
            $costExpr = 'COALESCE(pv.input_price, 0)';
        } elseif ($this->hasColumn('products','input_prices')) {
            $costExpr = 'COALESCE(p.input_prices, 0)';
        } elseif ($this->hasColumn('products','import_price')) {
            $costExpr = 'COALESCE(p.import_price, 0)';
        } elseif ($this->hasColumn('products','cost_price')) {
            $costExpr = 'COALESCE(p.cost_price, 0)';
        } else {
            $costExpr = 'COALESCE(p.base_price, 0)';
        }

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
                    $thresholdExpr AS threshold,
                    CASE WHEN pv.id IS NULL THEN CONCAT('P-', p.id) ELSE CONCAT('PV-', pv.id) END AS sku
                FROM products p
                LEFT JOIN product_variants pv ON pv.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";

        // Filters
        if (!empty($category_id)) {
            $category_id = intval($category_id);
            $sql .= " AND p.category_id = $category_id";
        }
        if (!empty($q)) {
            $q = mysqli_real_escape_string($this->con, $q);
            $like = "%$q%";
            $sql .= " AND (p.name LIKE '$like' OR pv.color LIKE '$like' OR pv.size LIKE '$like' OR p.slug LIKE '$like')";
        }
        if (!empty($status)) {
            // Apply status filter based on stock vs threshold from variant stock only
            if ($status === 'out') {
                $sql .= " AND COALESCE(pv.stock, 0) = 0";
            } elseif ($status === 'low') {
                $sql .= " AND COALESCE(pv.stock, 0) > 0 AND COALESCE(pv.stock, 0) <= $thresholdExpr";
            } elseif ($status === 'ok') {
                $sql .= " AND COALESCE(pv.stock, 0) > $thresholdExpr";
            }
        }

        $sql .= " ORDER BY p.name ASC, pv.id ASC";
        $res = mysqli_query($this->con, $sql);
        $items = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) { $items[] = $row; }
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

    // Resolve variant by SKU string (expects format 'PV-<id>') or raw numeric id
    function variant_by_sku($sku) {
        $sku = trim($sku);
        $variant_id = null;
        if ($sku === '') return null;
        if (preg_match('/^PV-(\d+)$/i', $sku, $m)) {
            $variant_id = intval($m[1]);
        } elseif (ctype_digit($sku)) {
            $variant_id = intval($sku);
        } else {
            return null;
        }

        $sql = "SELECT pv.id, pv.product_id, pv.stock FROM product_variants pv WHERE pv.id = $variant_id";
        $res = mysqli_query($this->con, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_assoc($res);
        }
        return null;
    }
    
}
?>