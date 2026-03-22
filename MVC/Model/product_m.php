<?php
class product_m extends connectDB
{
    public function __construct()
    {
        parent::__construct();
    }
    public function categories_selectAll()
    {
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        return mysqli_query($this->con, $sql);
    }
    public function collections_selectAll()
    {
        $sql = "SELECT * FROM collections ORDER BY id DESC";
        return mysqli_query($this->con, $sql);
    }

    public function products_insert($name, $slug, $category_id, $collection_id, $description, $thumbnail, $cost_price, $output_price, $color, $size, $stock, $is_sale = 0, $gender = '')
    {
        // Làm sạch chuỗi
        $name        = mysqli_real_escape_string($this->con, $name);
        $slug        = mysqli_real_escape_string($this->con, $slug);
        $description = mysqli_real_escape_string($this->con, $description);
        $thumbnail   = mysqli_real_escape_string($this->con, $thumbnail);
        $color       = mysqli_real_escape_string($this->con, $color);
        $size        = mysqli_real_escape_string($this->con, $size);
        $gender      = mysqli_real_escape_string($this->con, $gender);

        // Xử lý giá trị số
        $category_id   = ! empty($category_id) ? (int) $category_id : "NULL";
        $collection_id = ! empty($collection_id) ? (int) $collection_id : "NULL";
        $cost_price    = ! empty($cost_price) ? (float) $cost_price : 0;
        $output_price  = ! empty($output_price) ? (float) $output_price : 0;
        $stock         = ! empty($stock) ? (int) $stock : 0;
        $is_sale       = (int) $is_sale;

        // Insert vào bảng products
        $sql = "INSERT INTO products (name, slug, base_price, category_id, collection_id, description, thumbnail, is_sale, gender, created_at)
                VALUES ('$name', '$slug', $output_price, $category_id, $collection_id, '$description', '$thumbnail', $is_sale, '$gender', NOW())";

        $result = mysqli_query($this->con, $sql);

        if ($result) {
            $product_id = mysqli_insert_id($this->con);

            // Insert vào bảng product_variants
            $variant_sql = "INSERT INTO product_variants (product_id, size, color, input_price, stock)
                           VALUES ($product_id, '$size', '$color', $cost_price, $stock)";

            $variant_result = mysqli_query($this->con, $variant_sql);

            if ($variant_result) {
                $variant_id = mysqli_insert_id($this->con);
                return ['product_id' => $product_id, 'variant_id' => $variant_id];
            } else {
                // Nếu insert variant thất bại, xóa product vừa thêm
                mysqli_query($this->con, "DELETE FROM products WHERE id=$product_id");
                return false;
            }
        } else {
            // Debug: in lỗi SQL
            error_log("SQL Error: " . mysqli_error($this->con));
            return false;
        }
    }

    public function product_images_insert($variant_id, $image_url, $is_thumbnail = 0)
    {
        $image_url    = mysqli_real_escape_string($this->con, $image_url);
        $is_thumbnail = (int) $is_thumbnail;
        $variant_id   = (int) $variant_id;

        $sql = "INSERT INTO product_images (variant_id, image_url, is_thumbnail)
                VALUES ($variant_id, '$image_url', $is_thumbnail)";

        $result = mysqli_query($this->con, $sql);
        if (! $result) {
            error_log("SQL Error in product_images_insert: " . mysqli_error($this->con));
        }
        return $result;
    }

    public function variant_insert($product_id, $color, $size, $cost_price, $stock)
    {
        // Làm sạch chuỗi
        $color = mysqli_real_escape_string($this->con, $color);
        $size  = mysqli_real_escape_string($this->con, $size);

        // Xử lý giá trị số
        $product_id = (int) $product_id;
        $cost_price = ! empty($cost_price) ? (float) $cost_price : 0;
        $stock      = ! empty($stock) ? (int) $stock : 0;

        // Insert variant mới
        $sql = "INSERT INTO product_variants (product_id, size, color, input_price, stock)
                VALUES ($product_id, '$size', '$color', $cost_price, $stock)";

        if (mysqli_query($this->con, $sql)) {
            return mysqli_insert_id($this->con);
        }
        return false;
    }

    public function variant_exists($product_id, $color, $size)
    {
        $product_id = (int) $product_id;
        $color      = mysqli_real_escape_string($this->con, $color);
        $size       = mysqli_real_escape_string($this->con, $size);
        $sql        = "SELECT COUNT(*) AS cnt FROM product_variants WHERE product_id=$product_id AND color='$color' AND size='$size'";
        $res        = mysqli_query($this->con, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            return (int) $row['cnt'] > 0;
        }
        return false;
    }
    public function products_select($id, $name)
    {
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id LIKE '%$id%' AND p.name LIKE '%$name%'
                ORDER BY p.id DESC";
        return mysqli_query($this->con, $sql);
    }

    public function get_variants_by_product($product_id)
    {
        $product_id = (int) $product_id;
        $sql        = "SELECT * FROM product_variants WHERE product_id = $product_id ORDER BY id ASC";
        $result     = mysqli_query($this->con, $sql);
        if (! $result) {
            error_log("SQL Error in get_variants_by_product: " . mysqli_error($this->con));
        }
        return $result;
    }

    public function get_images_by_variant($variant_id)
    {
        $variant_id = (int) $variant_id;
        $sql        = "SELECT * FROM product_images WHERE variant_id = $variant_id ORDER BY id ASC";
        return mysqli_query($this->con, $sql);
    }

    public function variant_delete($variant_id)
    {
        $variant_id = (int) $variant_id;
        // Delete images first (due to foreign key)
        mysqli_query($this->con, "DELETE FROM product_images WHERE variant_id=$variant_id");
        // Delete variant
        $sql = "DELETE FROM product_variants WHERE id=$variant_id";
        return mysqli_query($this->con, $sql);
    }

    public function get_variant_by_id($variant_id)
    {
        $variant_id = (int) $variant_id;
        $sql        = "SELECT * FROM product_variants WHERE id = $variant_id";
        return mysqli_query($this->con, $sql);
    }

    public function get_product_id_by_variant($variant_id)
    {
        $variant_id = (int) $variant_id;
        $sql        = "SELECT product_id FROM product_variants WHERE id = $variant_id";
        $result     = mysqli_query($this->con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['product_id'];
        }
        return 0;
    }

    public function variant_update($variant_id, $color, $size, $input_price, $stock)
    {
        $variant_id  = (int) $variant_id;
        $color       = mysqli_real_escape_string($this->con, $color);
        $size        = mysqli_real_escape_string($this->con, $size);
        $input_price = (float) $input_price;
        $stock       = (int) $stock;

        $sql = "UPDATE product_variants
                SET color = '$color', size = '$size', input_price = $input_price, stock = $stock
                WHERE id = $variant_id";
        return mysqli_query($this->con, $sql);
    }

    public function get_images_by_id($image_id)
    {
        $image_id = (int) $image_id;
        $sql      = "SELECT * FROM product_images WHERE id = $image_id";
        return mysqli_query($this->con, $sql);
    }

    public function product_image_delete($image_id)
    {
        $image_id = (int) $image_id;
        $sql      = "DELETE FROM product_images WHERE id = $image_id";
        return mysqli_query($this->con, $sql);
    }

    public function products_update($id, $name, $slug, $price, $category_id, $collection_id, $description, $thumbnail, $gender = 'Nam', $is_sale = 0)
    {
        $sql = "UPDATE products
                SET name='$name', slug='$slug', base_price=$price, category_id=$category_id,
                    collection_id=$collection_id, description='$description', thumbnail='$thumbnail',
                    gender='$gender', is_sale=$is_sale
                WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    public function products_delete($id)
    {
        $sql = "DELETE FROM products WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
    // Thêm vào file product_m.php
    public function get_id_by_name($name)
    {
        $name = mysqli_real_escape_string($this->con, $name);
        // Tìm chính xác tên sản phẩm
        $sql    = "SELECT id FROM products WHERE name = '$name' LIMIT 1";
        $result = mysqli_query($this->con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['id'];
        }
        return 0; // Trả về 0 nếu chưa có
    }
    public function get_products_export_full($keyword = '')
    {
        // 1. Xử lý từ khóa tìm kiếm
        $keyword = mysqli_real_escape_string($this->con, $keyword);

        // 2. Câu lệnh SQL nối 2 bảng
        // p.* : Lấy hết cột bảng products
        // v.color, v.size... : Lấy thêm các cột từ bảng variants
        $sql = "SELECT p.*,
                       v.input_price, v.color, v.size, v.stock
                FROM products p
                LEFT JOIN product_variants v ON p.id = v.product_id
                WHERE p.id LIKE '%$keyword%' OR p.name LIKE '%$keyword%'
                ORDER BY p.id DESC, v.id ASC";

        return mysqli_query($this->con, $sql);
    }
}
