<?php
class reviews_m extends connectDB {
    function __construct() {
        parent::__construct();
    }

    // =========================================================
    // DÀNH CHO KHÁCH HÀNG (FRONTEND)
    // =========================================================

    // Lấy danh sách đánh giá của 1 sản phẩm
    function reviews_selectByProduct($product_id) {
        $product_id = intval($product_id);
        $sql = "SELECT r.*, u.full_name, u.avatar 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = $product_id AND r.status = 1 
        ORDER BY r.review_date DESC";
        return mysqli_query($this->con, $sql);
    }

    // Kiểm tra khách hàng đủ điều kiện đánh giá không
    function reviews_checkEligible($user_id, $product_id) {
        $user_id = intval($user_id);
        $product_id = intval($product_id);
        
        $sql = "SELECT o.id 
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = $user_id 
                  AND oi.product_id = $product_id 
                  AND o.status = 'completed'
                  AND o.id NOT IN (
                      SELECT order_id FROM reviews 
                      WHERE user_id = $user_id AND product_id = $product_id
                  )
                LIMIT 1";
        
        $result = mysqli_query($this->con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['id']; // Trả về order_id
        }
        return false;
    }

    // Thêm đánh giá mới
    function reviews_insert($user_id, $product_id, $order_id, $rating, $comment) {
        $user_id = intval($user_id);
        $product_id = intval($product_id);
        $order_id = intval($order_id);
        $rating = intval($rating);
        // Chống lỗi ngoặc kép/ngoặc đơn phá SQL
        $comment_safe = mysqli_real_escape_string($this->con, $comment);
        
        $sql = "INSERT INTO reviews (user_id, product_id, order_id, rating, comment, status) 
                VALUES ($user_id, $product_id, $order_id, $rating, '$comment_safe', 1)";
        return mysqli_query($this->con, $sql);
    }

    // =========================================================
    // DÀNH CHO ADMIN (BACKEND)
    // =========================================================

    function reviews_selectAllAdmin() {
        // Bổ sung thêm p.slug và p.thumbnail
        $sql = "SELECT r.*, u.full_name, p.name as product_name, p.slug, p.thumbnail 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                JOIN products p ON r.product_id = p.id
                ORDER BY r.review_date DESC";
        return mysqli_query($this->con, $sql);
    }

    function reviews_toggleStatus($id) {
        $id = intval($id);
        $sql = "UPDATE reviews SET status = NOT status WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }

    function reviews_delete($id) {
        $id = intval($id);
        $sql = "DELETE FROM reviews WHERE id=$id";
        return mysqli_query($this->con, $sql);
    }
}
?>